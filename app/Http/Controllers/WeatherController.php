<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use App\Services\AirQualityService;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class WeatherController extends Controller
{
    protected $weatherService;
    protected $airQualityService;

    public function __construct(WeatherService $weatherService, AirQualityService $airQualityService)
    {
        $this->weatherService = $weatherService;
        $this->airQualityService = $airQualityService;
    }

    /**
     * Display weather for homepage or default location
     */
    public function index()
    {
        // Default to Hanoi
        $province = Province::where('code_name', 'ha_noi')->first();
        $weatherData = $this->weatherService->getWeatherData($province->code_name, 'province');

        if (!$weatherData) {
            abort(500, 'Weather data unavailable');
        }

        // Get featured locations for the widget
        $featuredLocations = $this->weatherService->getFeaturedLocationsWeather();

        // Format date for cards to match the design
        foreach ($weatherData['daily'] as $key => $day) {
            $date = $weatherData['daily'][$key]['date'];
            $weatherData['daily'][$key]['formatted_date'] = $weatherData['daily'][$key]['day_name'] . ' ' . $date;
        }

        // Get latest weather news
        $weatherNews = $this->getLatestArticles();

        // Get all provinces for navigation
        $provinces = Province::orderBy('name')->get();

        $SEOData = new SEOData(
            title: 'Dự báo thời tiết ' . $weatherData['location'] . ' - ' . setting('site_name'),
            description: 'Thông tin dự báo thời tiết ' . $weatherData['location'] . ' hôm nay và các ngày tới. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất.'
        );

        return view('weather.index', compact(
            'province',
            'weatherData',
            'featuredLocations',
            'weatherNews',
            'provinces',
            'SEOData'
        ));
    }

    /**
     * Display weather for a specific province
     */
    public function showProvince($provinceSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        return $this->showForecastForLocation($province);
    }

    /**
     * Display weather for a specific district
     */
    public function showDistrict($provinceSlug, $districtSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        return $this->showForecastForLocation($province, null, $district);
    }

    /**
     * Display weather for a specific ward
     */
    public function showWard($provinceSlug, $districtSlug, $wardSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        $ward = $this->findWard($district, $wardSlug);
        if (!$ward) {
            abort(404, 'Ward not found');
        }

        return $this->showForecastForLocation($province, null, $district, $ward);
    }

    /**
     * Show hourly forecast for a province
     */
    public function showProvinceHourly($provinceSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        return $this->showForecastForLocation($province, 'hourly');
    }

    /**
     * Show hourly forecast for a district
     */
    public function showDistrictHourly($provinceSlug, $districtSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        return $this->showForecastForLocation($province, 'hourly', $district);
    }

    /**
     * Show hourly forecast for a ward
     */
    public function showWardHourly($provinceSlug, $districtSlug, $wardSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        $ward = $this->findWard($district, $wardSlug);
        if (!$ward) {
            abort(404, 'Ward not found');
        }

        return $this->showForecastForLocation($province, 'hourly', $district, $ward);
    }

    /**
     * Show tomorrow's forecast for a province
     */
    public function showProvinceTomorrow($provinceSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        return $this->showForecastForLocation($province, 'tomorrow');
    }

    /**
     * Show tomorrow's forecast for a district
     */
    public function showDistrictTomorrow($provinceSlug, $districtSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        return $this->showForecastForLocation($province, 'tomorrow', $district);
    }

    /**
     * Show tomorrow's forecast for a ward
     */
    public function showWardTomorrow($provinceSlug, $districtSlug, $wardSlug)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        $ward = $this->findWard($district, $wardSlug);
        if (!$ward) {
            abort(404, 'Ward not found');
        }

        return $this->showForecastForLocation($province, 'tomorrow', $district, $ward);
    }

    /**
     * Show multi-day forecast for a province
     */
    public function showProvinceDaily($provinceSlug, $days)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        return $this->showForecastForLocation($province, 'daily', null, null, $days);
    }

    /**
     * Show multi-day forecast for a district
     */
    public function showDistrictDaily($provinceSlug, $districtSlug, $days)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        return $this->showForecastForLocation($province, 'daily', $district, null, $days);
    }

    /**
     * Show multi-day forecast for a ward
     */
    public function showWardDaily($provinceSlug, $districtSlug, $wardSlug, $days)
    {
        $province = $this->findProvince($provinceSlug);
        if (!$province) {
            abort(404, 'Province not found');
        }

        $district = $this->findDistrict($province, $districtSlug);
        if (!$district) {
            abort(404, 'District not found');
        }

        $ward = $this->findWard($district, $wardSlug);
        if (!$ward) {
            abort(404, 'Ward not found');
        }

        return $this->showForecastForLocation($province, 'daily', $district, $ward, $days);
    }

    /**
     * API endpoint to get weather data for a location
     */
    public function apiGetWeather(Request $request, $location = null)
    {
        if (!$location) {
            $location = $request->input('location', 'ha-noi');
        }

        $province = Province::where('code', $location)
            ->orWhere('code_name', str_replace('-', '_', $location))
            ->first();

        if ($province) {
            $weatherData = $this->weatherService->getWeatherData($province->code_name, 'province');
        } else {
            $weatherData = $this->weatherService->getWeatherData($location);
        }

        if (!$weatherData) {
            return response()->json(['error' => 'Location not found or weather data unavailable'], 404);
        }

        return response()->json($weatherData);
    }

    /**
     * API endpoint to get all provinces
     */
    public function apiGetProvinces()
    {
        $provinces = Province::orderBy('name')->get();
        return response()->json($provinces);
    }

    /**
     * API endpoint to get districts for a province
     */
    public function apiGetDistricts($provinceCode)
    {
        $provinceCodeName = str_replace('-', '_', $provinceCode);

        $province = Province::where('code', $provinceCode)
            ->orWhere('code_name', $provinceCodeName)
            ->first();

        if (!$province) {
            return response()->json(['error' => 'Province not found'], 404);
        }

        $districts = $province->districts()->orderBy('name')->get();
        return response()->json($districts);
    }

    /**
     * API endpoint to get wards for a district
     */
    public function apiGetWards($districtCode)
    {
        $districtCodeName = str_replace('-', '_', $districtCode);

        $district = District::where('code', $districtCode)
            ->orWhere('code_name', $districtCodeName)
            ->first();

        if (!$district) {
            return response()->json(['error' => 'District not found'], 404);
        }

        $wards = $district->wards()->orderBy('name')->get();
        return response()->json($wards);
    }

    /**
     * Common method to handle forecasts for all location types and periods
     */
    protected function showForecastForLocation($province, $period = null, $district = null, $ward = null, $days = null)
    {
        // Determine the location type and code name for API request
        if ($ward) {
            $locationType = 'ward';
            $locationCode = $ward->code_name;
            $locationName = $ward->name . ', ' . $district->name . ', ' . $province->name;
        } elseif ($district) {
            $locationType = 'district';
            $locationCode = $district->code_name;
            $locationName = $district->name . ', ' . $province->name;
        } else {
            $locationType = 'province';
            $locationCode = $province->code_name;
            $locationName = $province->name;
        }

        // Get weather data
        $weatherData = $this->weatherService->getWeatherData($locationCode, $locationType);

        if (!$weatherData) {
            abort(404, 'Weather data unavailable');
        }

        // Format date for cards
        foreach ($weatherData['daily'] as $key => $day) {
            $date = $weatherData['daily'][$key]['date'];
            $weatherData['daily'][$key]['formatted_date'] = $weatherData['daily'][$key]['day_name'] . ' ' . $date;
        }

        // Prepare chart data
        $hourlyChartData = [];
        $precipitationHourlyData = [];
        $dailyChartData = [];

        // Process hourly data for charts (next 12-24 hours)
        for ($i = 0; $i < min(24, count($weatherData['hourly'])); $i++) {
            $hour = $weatherData['hourly'][$i];
            $precipProb = isset($hour['precipitation_probability']) ?
                $hour['precipitation_probability'] :
                rand(35, 100); // Fallback to random if not available

            $hourlyChartData[] = [
                'time' => $hour['time'],
                'temperature' => $hour['temperature'],
                'precipitation_probability' => $precipProb
            ];

            $precipitationHourlyData[] = [
                'time' => $hour['time'],
                'precipitation_amount' => $hour['precipitation']
            ];
        }

        // Prepare daily chart data
        foreach ($weatherData['daily'] as $day) {
            $dailyChartData[] = [
                'date' => $day['date'],
                'day_name' => $day['day_name'],
                'temperature_max' => $day['max_temp'],
                'temperature_min' => $day['min_temp'],
                'precipitation_probability' => $day['precipitation_probability']
            ];
        }

        // Get air quality data
        $airQualityData = $this->getAirQualityData($province, $district, $ward);

        // Format sunrise/sunset times
        $sunriseSunsetData = [
            'sunrise' => isset($weatherData['sunrise']) ?
                date('h:i A', strtotime($weatherData['sunrise'])) : '05:25 AM',
            'sunset' => isset($weatherData['sunset']) ?
                date('h:i A', strtotime($weatherData['sunset'])) : '06:21 PM'
        ];

        // Get time of day temperatures
        $timeOfDayTemps = isset($weatherData['time_of_day']) ?
            $weatherData['time_of_day'] :
            [
                'day' => ['temperature' => 31, 'temperature_night' => 30],
                'night' => ['temperature' => 22, 'temperature_night' => 23],
                'morning' => ['temperature' => 19, 'temperature_night' => 20],
                'evening' => ['temperature' => 30, 'temperature_night' => 29]
            ];

        // Get latest weather news
        $weatherNews = $this->getLatestArticles();

        // Get featured locations
        $featuredLocations = $this->weatherService->getFeaturedLocationsWeather();

        // Prepare base view data
        $viewData = [
            'province' => $province,
            'district' => $district,
            'ward' => $ward,
            'weatherData' => $weatherData,
            'featuredLocations' => $featuredLocations,
            'weatherNews' => $weatherNews,
            'hourlyChartData' => $hourlyChartData,
            'precipitationHourlyData' => $precipitationHourlyData,
            'dailyChartData' => $dailyChartData,
            'airQualityData' => $airQualityData,
            'sunriseSunsetData' => $sunriseSunsetData,
            'timeOfDayTemps' => $timeOfDayTemps,
            'today' => date('d/m/Y')
        ];

        // Prepare districts list for provinces page
        if (!$district && $locationType === 'province') {
            $districts = $province->districts()->orderBy('name')->get();
            $districtsWithWeather = [];

            foreach ($districts as $district) {
                $districtWeatherData = $this->getBasicDistrictWeather($district, $province);
                $districtsWithWeather[] = [
                    'name' => $district->name,
                    'code' => $district->code,
                    'url' => route('weather.district', [$province->getSlug(), $district->getSlug()]),
                    'weather_icon' => asset($districtWeatherData['weather_image'] ?? '/assets/images/weather-1/01d.png')
                ];
            }

            $viewData['districtsWithWeather'] = $districtsWithWeather;
        }

        // For district page, add wards list
        if ($district && !$ward && $locationType === 'district') {
            $viewData['wards'] = $district->wards()->orderBy('name')->get();
        }

        // Choose template and set SEO data based on forecast period
        switch ($period) {
            case 'hourly':
                $viewData['SEOData'] = new SEOData(
                    title: 'Dự báo thời tiết theo giờ ' . $locationName . ' - ' . setting('site_name'),
                    description: 'Thông tin dự báo thời tiết theo giờ tại ' . $locationName . '. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất theo từng giờ.'
                );
                return view('weather.location-hourly', $viewData);

            case 'tomorrow':
                $viewData['SEOData'] = new SEOData(
                    title: 'Dự báo thời tiết ngày mai ' . $locationName . ' - ' . setting('site_name'),
                    description: 'Thông tin dự báo thời tiết ngày mai tại ' . $locationName . '. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất.'
                );
                return view('weather.location-tomorrow', $viewData);

            case 'daily':
                $daysCount = (int) $days;
                $viewData['daysCount'] = $daysCount;
                $viewData['SEOData'] = new SEOData(
                    title: "Dự báo thời tiết $daysCount ngày tới $locationName - " . setting('site_name'),
                    description: "Thông tin dự báo thời tiết $daysCount ngày tới tại $locationName. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất."
                );
                return view('weather.location-daily', $viewData);

            default:
                // Regular location view (current weather)
                $viewData['SEOData'] = new SEOData(
                    title: 'Dự báo thời tiết ' . $locationName . ' - ' . setting('site_name'),
                    description: 'Thông tin dự báo thời tiết ' . $locationName . ' hôm nay và các ngày tới. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất.'
                );
                return view('weather.location', $viewData);
        }
    }

    /**
     * Find province by slug
     */
    protected function findProvince($provinceSlug)
    {
        $provinceCodeName = str_replace('-', '_', $provinceSlug);
        return Province::where('code_name', $provinceCodeName)
            ->orWhere('code', $provinceSlug)
            ->first();
    }

    /**
     * Find district by slug
     */
    protected function findDistrict($province, $districtSlug)
    {
        $districtCodeName = str_replace('-', '_', $districtSlug);

        $district = District::where(function($query) use ($districtSlug, $districtCodeName) {
            $query->where('code', $districtSlug)
                ->orWhere('code_name', $districtCodeName);
        })
            ->where('province_code', $province->code)
            ->first();

        if (!$district) {
            // Try a more flexible search if the exact match fails
            $district = District::where(function($query) use ($districtSlug, $districtCodeName) {
                $query->where('code', 'LIKE', $districtSlug . '%')
                    ->orWhere('code_name', 'LIKE', $districtCodeName . '%')
                    ->orWhere('name', 'LIKE', str_replace('_', ' ', $districtCodeName) . '%');
            })
                ->where('province_code', $province->code)
                ->first();
        }

        return $district;
    }

    /**
     * Find ward by slug
     */
    protected function findWard($district, $wardSlug)
    {
        $wardCodeName = str_replace('-', '_', $wardSlug);

        $ward = Ward::where(function($query) use ($wardSlug, $wardCodeName) {
            $query->where('code', $wardSlug)
                ->orWhere('code_name', $wardCodeName);
        })
            ->where('district_code', $district->code)
            ->first();

        if (!$ward) {
            // Try a more flexible search if the exact match fails
            $ward = Ward::where(function($query) use ($wardSlug, $wardCodeName) {
                $query->where('code', 'LIKE', $wardSlug . '%')
                    ->orWhere('code_name', 'LIKE', $wardCodeName . '%')
                    ->orWhere('name', 'LIKE', str_replace('_', ' ', $wardCodeName) . '%');
            })
                ->where('district_code', $district->code)
                ->first();

            if (!$ward) {
                // As a fallback, get the first ward in this district
                $ward = Ward::where('district_code', $district->code)->first();

                if ($ward) {
                    \Log::warning("Using fallback ward: " . $ward->name . " for requested ward: " . $wardSlug);
                }
            }
        }

        return $ward;
    }

    /**
     * Get air quality data for a location
     */
    protected function getAirQualityData($province, $district = null, $ward = null)
    {
        // Try to get coordinates in order of specificity
        $coordinates = null;

        if ($ward && $ward->getCoordinates()) {
            $coordinates = $ward->getCoordinates();
        } elseif ($district && $district->getCoordinates()) {
            $coordinates = $district->getCoordinates();
        } elseif ($province->getCoordinates()) {
            $coordinates = $province->getCoordinates();
        }

        if ($coordinates) {
            return $this->airQualityService->getAirQualityData($coordinates['lat'], $coordinates['lng']);
        }

        // Fallback to Hanoi coordinates
        return $this->airQualityService->getAirQualityData(21.028511, 105.804817);
    }

    /**
     * Get latest weather news articles
     */
    protected function getLatestArticles()
    {
        $latestArticles = Article::latest()
            ->where('is_published', 1)
            ->limit(4)
            ->get();

        // If no articles, use static data
        if ($latestArticles->count() === 0) {
            return $this->weatherService->getWeatherNews();
        }

        $weatherNews = [];
        foreach ($latestArticles as $article) {
            $weatherNews[] = [
                'title' => $article->title,
                'image' => $article->getThumbnail(),
                'slug' => $article->slug
            ];
        }

        return $weatherNews;
    }

    /**
     * Get basic weather data for a district (optimized version)
     */
    protected function getBasicDistrictWeather($district, $province)
    {
        $cacheKey = 'district_basic_weather_' . $district->code . '_' . date('Y-m-d');

        if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
            return Cache::get($cacheKey);
        }

        try {
            // Use minimal data level for district listings
            $data = $this->weatherService->getWeatherData($district->code_name, 'district', 'minimal');

            if ($data && isset($data['current'])) {
                $result = [
                    'weather_image' => $data['current']['weather_image'],
                    'temperature' => $data['current']['temperature']
                ];

                if ((bool)setting('cache_enabled')) {
                    Cache::put($cacheKey, $result, now()->addHours(6));
                }

                return $result;
            }
        } catch (\Exception $e) {
            Log::error("Error getting district weather: " . $e->getMessage());
        }

        // Fallback data
        return [
            'weather_image' => '/assets/images/weather-1/01d.png',
            'temperature' => 30
        ];
    }
}
