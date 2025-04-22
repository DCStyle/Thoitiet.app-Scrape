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
use Illuminate\Support\Facades\Log;
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
        try {
            // Default to Hanoi
            $province = Province::where('code_name', 'ha_noi')->first();

            if (!$province) {
                Log::error("Default province 'ha_noi' not found in database");
                $province = Province::first(); // Fallback to any province

                if (!$province) {
                    abort(500, 'No provinces available in the database');
                }
            }

            $weatherData = $this->weatherService->getWeatherData($province->code_name, 'province');

            if (!$weatherData) {
                Log::error("Weather data unavailable for default location: " . $province->name);

                // Create fallback weather data
                $weatherData = $this->createFallbackWeatherData($province->name);
            }

            // Get featured locations for the widget
            $featuredLocations = $this->weatherService->getFeaturedLocationsWeather();

            // Format date for cards to match the design
            if (isset($weatherData['daily']) && is_array($weatherData['daily'])) {
                foreach ($weatherData['daily'] as $key => $day) {
                    if (isset($weatherData['daily'][$key]['date']) && isset($weatherData['daily'][$key]['day_name'])) {
                        $date = $weatherData['daily'][$key]['date'];
                        $weatherData['daily'][$key]['formatted_date'] = $weatherData['daily'][$key]['day_name'] . ' ' . $date;
                    } else {
                        $weatherData['daily'][$key]['formatted_date'] = 'Ngày ' . ($key + 1);
                    }
                }
            }

            // Get latest weather news
            $weatherNews = $this->getLatestArticles();

            // Get all provinces for navigation
            $provinces = Province::orderBy('name')->get();

            $customTitle = setting('site_name');
            $customDescription = setting('site_description');

            $SEOData = new SEOData(
                title: $customTitle ?: 'Dự báo thời tiết ' . ($weatherData['location'] ?? 'Việt Nam') . ' - ' . setting('site_name'),
                description: $customDescription ?: 'Thông tin dự báo thời tiết ' . ($weatherData['location'] ?? 'Việt Nam') . ' hôm nay và các ngày tới. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất.'
            );

            return view('weather.index', compact(
                'province',
                'weatherData',
                'featuredLocations',
                'weatherNews',
                'provinces',
                'SEOData'
            ));
        } catch (\Exception $e) {
            Log::error("Error in weather index: " . $e->getMessage());

            // Show user-friendly error page
            return response()->view('errors.custom', [
                'message' => 'Không thể tải dữ liệu thời tiết. Vui lòng thử lại sau.',
                'code' => 500
            ], 500);
        }
    }

    /**
     * Display weather for a specific province
     */
    public function showProvince($provinceSlug)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            return $this->showForecastForLocation($province);
        } catch (\Exception $e) {
            Log::error("Error in showProvince: " . $e->getMessage());
            abort(500, 'Error retrieving province weather data');
        }
    }

    /**
     * Display weather for a specific district
     */
    public function showDistrict($provinceSlug, $districtSlug)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            $district = $this->findDistrict($province, $districtSlug);
            if (!$district) {
                abort(404, 'District not found');
            }

            return $this->showForecastForLocation($province, null, $district);
        } catch (\Exception $e) {
            Log::error("Error in showDistrict: " . $e->getMessage());
            abort(500, 'Error retrieving district weather data');
        }
    }

    /**
     * Display weather for a specific ward
     */
    public function showWard($provinceSlug, $districtSlug, $wardSlug)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error("Error in showWard: " . $e->getMessage());
            abort(500, 'Error retrieving ward weather data');
        }
    }

    /**
     * Show hourly forecast for a province
     */
    public function showProvinceHourly($provinceSlug)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            return $this->showForecastForLocation($province, 'hourly');
        } catch (\Exception $e) {
            Log::error("Error in showProvinceHourly: " . $e->getMessage());
            abort(500, 'Error retrieving hourly province weather data');
        }
    }

    /**
     * Show hourly forecast for a district
     */
    public function showDistrictHourly($provinceSlug, $districtSlug)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            $district = $this->findDistrict($province, $districtSlug);
            if (!$district) {
                abort(404, 'District not found');
            }

            return $this->showForecastForLocation($province, 'hourly', $district);
        } catch (\Exception $e) {
            Log::error("Error in showDistrictHourly: " . $e->getMessage());
            abort(500, 'Error retrieving hourly district weather data');
        }
    }

    /**
     * Show hourly forecast for a ward
     */
    public function showWardHourly($provinceSlug, $districtSlug, $wardSlug)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error("Error in showWardHourly: " . $e->getMessage());
            abort(500, 'Error retrieving hourly ward weather data');
        }
    }

    /**
     * Show tomorrow's forecast for a province
     */
    public function showProvinceTomorrow($provinceSlug)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            return $this->showForecastForLocation($province, 'tomorrow');
        } catch (\Exception $e) {
            Log::error("Error in showProvinceTomorrow: " . $e->getMessage());
            abort(500, 'Error retrieving tomorrow\'s province weather data');
        }
    }

    /**
     * Show tomorrow's forecast for a district
     */
    public function showDistrictTomorrow($provinceSlug, $districtSlug)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            $district = $this->findDistrict($province, $districtSlug);
            if (!$district) {
                abort(404, 'District not found');
            }

            return $this->showForecastForLocation($province, 'tomorrow', $district);
        } catch (\Exception $e) {
            Log::error("Error in showDistrictTomorrow: " . $e->getMessage());
            abort(500, 'Error retrieving tomorrow\'s district weather data');
        }
    }

    /**
     * Show tomorrow's forecast for a ward
     */
    public function showWardTomorrow($provinceSlug, $districtSlug, $wardSlug)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error("Error in showWardTomorrow: " . $e->getMessage());
            abort(500, 'Error retrieving tomorrow\'s ward weather data');
        }
    }

    /**
     * Show multi-day forecast for a province
     */
    public function showProvinceDaily($provinceSlug, $days)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            return $this->showForecastForLocation($province, 'daily', null, null, $days);
        } catch (\Exception $e) {
            Log::error("Error in showProvinceDaily: " . $e->getMessage());
            abort(500, 'Error retrieving multi-day province weather data');
        }
    }

    /**
     * Show multi-day forecast for a district
     */
    public function showDistrictDaily($provinceSlug, $districtSlug, $days)
    {
        try {
            $province = $this->findProvince($provinceSlug);
            if (!$province) {
                abort(404, 'Province not found');
            }

            $district = $this->findDistrict($province, $districtSlug);
            if (!$district) {
                abort(404, 'District not found');
            }

            return $this->showForecastForLocation($province, 'daily', $district, null, $days);
        } catch (\Exception $e) {
            Log::error("Error in showDistrictDaily: " . $e->getMessage());
            abort(500, 'Error retrieving multi-day district weather data');
        }
    }

    /**
     * Show multi-day forecast for a ward
     */
    public function showWardDaily($provinceSlug, $districtSlug, $wardSlug, $days)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error("Error in showWardDaily: " . $e->getMessage());
            abort(500, 'Error retrieving multi-day ward weather data');
        }
    }

    /**
     * API endpoint to get weather data for a location
     */
    public function apiGetWeather(Request $request, $location = null)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error("API Error in apiGetWeather: " . $e->getMessage());
            return response()->json(['error' => 'Server error while retrieving weather data'], 500);
        }
    }

    /**
     * API endpoint to get all provinces
     */
    public function apiGetProvinces()
    {
        try {
            $provinces = Province::orderBy('name')->get();
            return response()->json($provinces);
        } catch (\Exception $e) {
            Log::error("API Error in apiGetProvinces: " . $e->getMessage());
            return response()->json(['error' => 'Server error while retrieving provinces'], 500);
        }
    }

    /**
     * API endpoint to get districts for a province
     */
    public function apiGetDistricts($provinceCode)
    {
        try {
            $provinceCodeName = str_replace('-', '_', $provinceCode);

            $province = Province::where('code', $provinceCode)
                ->orWhere('code_name', $provinceCodeName)
                ->first();

            if (!$province) {
                return response()->json(['error' => 'Province not found'], 404);
            }

            $districts = $province->districts()->orderBy('name')->get();
            return response()->json($districts);
        } catch (\Exception $e) {
            Log::error("API Error in apiGetDistricts: " . $e->getMessage());
            return response()->json(['error' => 'Server error while retrieving districts'], 500);
        }
    }

    /**
     * API endpoint to get wards for a district
     */
    public function apiGetWards($districtCode)
    {
        try {
            $districtCodeName = str_replace('-', '_', $districtCode);

            $district = District::where('code', $districtCode)
                ->orWhere('code_name', $districtCodeName)
                ->first();

            if (!$district) {
                return response()->json(['error' => 'District not found'], 404);
            }

            $wards = $district->wards()->orderBy('name')->get();
            return response()->json($wards);
        } catch (\Exception $e) {
            Log::error("API Error in apiGetWards: " . $e->getMessage());
            return response()->json(['error' => 'Server error while retrieving wards'], 500);
        }
    }

    /**
     * Common method to handle forecasts for all location types and periods
     */
    protected function showForecastForLocation($province, $period = null, $district = null, $ward = null, $days = null)
    {
        $locationModel = $ward ?? $district ?? $province;

        try {
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
                Log::error("Weather data unavailable for {$locationName}");

                // More graceful error handling - could redirect to a static page or show a friendly message
                return response()->view('errors.weather-unavailable', [
                    'locationName' => $locationName,
                    'province' => $province,
                ], 503);
            }

            // Check for required data
            if (empty($weatherData['daily'])) {
                Log::error("Missing daily forecast data for {$locationName}");

                // Create fallback data if missing
                $weatherData = $this->createFallbackWeatherData($locationName);
            }

            // Format date for cards with safety check
            foreach ($weatherData['daily'] as $key => $day) {
                if (isset($weatherData['daily'][$key]['date']) && isset($weatherData['daily'][$key]['day_name'])) {
                    $date = $weatherData['daily'][$key]['date'];
                    $weatherData['daily'][$key]['formatted_date'] = $weatherData['daily'][$key]['day_name'] . ' ' . $date;
                } else {
                    // Provide default format if data is missing
                    $weatherData['daily'][$key]['formatted_date'] = 'Ngày ' . ($key + 1);
                }
            }

            // Prepare chart data
            $hourlyChartData = [];
            $precipitationHourlyData = [];
            $dailyChartData = [];

            // Process hourly data for charts (next 12-24 hours)
            if (isset($weatherData['hourly']) && is_array($weatherData['hourly'])) {
                for ($i = 0; $i < min(24, count($weatherData['hourly'])); $i++) {
                    $hour = $weatherData['hourly'][$i];

                    // Check for required fields
                    if (!isset($hour['time']) || !isset($hour['temperature'])) {
                        continue;
                    }

                    $precipProb = $hour['precipitation_probability'] ?? rand(0, 100); // Fallback to random if not available

                    $hourlyChartData[] = [
                        'time' => $hour['time'],
                        'temperature' => $hour['temperature'],
                        'precipitation_probability' => $precipProb
                    ];

                    $precipitationHourlyData[] = [
                        'time' => $hour['time'],
                        'precipitation_amount' => $hour['precipitation'] ?? 0
                    ];
                }
            }

            // Prepare daily chart data
            if (isset($weatherData['daily']) && is_array($weatherData['daily'])) {
                foreach ($weatherData['daily'] as $day) {
                    // Check for required fields
                    if (!isset($day['date']) || !isset($day['max_temp']) || !isset($day['min_temp'])) {
                        continue;
                    }

                    $dailyChartData[] = [
                        'date' => $day['date'],
                        'day_name' => $day['day_name'] ?? '',
                        'temperature_max' => $day['max_temp'],
                        'temperature_min' => $day['min_temp'],
                        'precipitation_probability' => $day['precipitation_probability'] ?? 0
                    ];
                }
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
            $timeOfDayTemps = $weatherData['time_of_day'] ?? [
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

            // Get metadata
            $metadata = $this->getMetadata();
            $customTitle = $metadata['title'] ?? null;
            $customDescription = $metadata['description'] ?? null;

            // Choose template and set SEO data based on forecast period
            switch ($period) {
                case 'hourly':
                    $viewData['SEOData'] = new SEOData(
                        title: $customTitle ?: 'Dự báo thời tiết theo giờ ' . $locationName . ' - ' . setting('site_name'),
                        description: $customDescription ?: 'Thông tin dự báo thời tiết theo giờ tại ' . $locationName . '. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất theo từng giờ.'
                    );
                    return view('weather.location-hourly', $viewData);

                case 'tomorrow':
                    $viewData['SEOData'] = new SEOData(
                        title: $customTitle ?: 'Dự báo thời tiết ngày mai ' . $locationName . ' - ' . setting('site_name'),
                        description: $customDescription ?: 'Thông tin dự báo thời tiết ngày mai tại ' . $locationName . '. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất.'
                    );
                    return view('weather.location-tomorrow', $viewData);

                case 'daily':
                    $daysCount = (int) $days;
                    $viewData['daysCount'] = $daysCount;
                    $viewData['SEOData'] = new SEOData(
                        title: $customTitle ?: "Dự báo thời tiết $daysCount ngày tới $locationName - " . setting('site_name'),
                        description: $customDescription ?: "Thông tin dự báo thời tiết $daysCount ngày tới tại $locationName. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất."
                    );
                    return view('weather.location-daily', $viewData);

                default:
                    // Regular location view (current weather)
                    $viewData['SEOData'] = new SEOData(
                        title: $customTitle ?: 'Dự báo thời tiết ' . $locationName . ' - ' . setting('site_name'),
                        description: $customDescription ?: 'Thông tin dự báo thời tiết ' . $locationName . ' hôm nay và các ngày tới. Cập nhật nhiệt độ, mưa, nắng, gió mới nhất.'
                    );
                    return view('weather.location', $viewData);
            }
        } catch (\Exception $e) {
            Log::error("Error in showForecastForLocation for {$province->name}: " . $e->getMessage());

            // Show user-friendly error page
            return response()->view('errors.custom', [
                'message' => 'Không thể tải dữ liệu thời tiết. Vui lòng thử lại sau.',
                'code' => 500
            ], 500);
        }
    }

    /**
     * Create fallback weather data when API fails
     */
    protected function createFallbackWeatherData($locationName)
    {
        $currentWeather = [
            'temperature' => 25,
            'feels_like' => 27,
            'weather_code' => 1003,
            'weather_description' => 'Có mây',
            'weather_icon' => '03d',
            'weather_image' => '/assets/images/weather-1/03d.png',
            'humidity' => 70,
            'wind_speed' => 5.00,
            'precipitation' => 0,
            'precipitation_probability' => 20,
            'visibility' => 10
        ];

        $dailyForecasts = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('d/m', strtotime("+$i days"));
            $dailyForecasts[] = [
                'date' => $date,
                'full_date' => date('Y-m-d', strtotime("+$i days")),
                'day_name' => $this->getDayName(date('Y-m-d', strtotime("+$i days"))),
                'max_temp' => 28 + rand(0, 4),
                'min_temp' => 20 + rand(0, 3),
                'weather_code' => 1003,
                'weather_description' => 'Có mây',
                'weather_icon' => '03d',
                'weather_image' => '/assets/images/weather-1/03d.png',
                'precipitation_sum' => 0,
                'precipitation_probability' => 20,
                'sunrise' => '06:00',
                'sunset' => '18:00',
                'formatted_date' => $this->getDayName(date('Y-m-d', strtotime("+$i days"))) . ' ' . $date
            ];
        }

        $hourlyForecasts = [];
        $currentHour = (int)date('H');

        for ($i = 1; $i <= 24; $i++) {
            $hour = ($currentHour + $i) % 24;
            $isDay = ($hour >= 6 && $hour < 18) ? 1 : 0;
            $weatherIcon = $isDay ? '03d' : '03n';

            $hourlyForecasts[] = [
                'time' => sprintf('%02d:00', $hour),
                'full_time' => date('Y-m-d H:i:00', strtotime("+$i hours")),
                'temperature' => 22 + rand(0, 8),
                'weather_code' => 1003,
                'weather_description' => 'Có mây',
                'weather_icon' => $weatherIcon,
                'weather_image' => '/assets/images/weather-1/' . $weatherIcon . '.png',
                'precipitation' => 0,
                'precipitation_probability' => 20,
                'wind_speed' => 5,
                'humidity' => 70,
                'visibility' => 10
            ];
        }

        return [
            'location' => $locationName,
            'current' => $currentWeather,
            'daily' => $dailyForecasts,
            'hourly' => $hourlyForecasts,
            'time_of_day' => [
                'morning' => ['temperature' => 24, 'temperature_night' => 22],
                'day' => ['temperature' => 28, 'temperature_night' => 26],
                'evening' => ['temperature' => 25, 'temperature_night' => 23],
                'night' => ['temperature' => 22, 'temperature_night' => 20]
            ],
            'sunrise' => '06:00',
            'sunset' => '18:00'
        ];
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
                    Log::warning("Using fallback ward: " . $ward->name . " for requested ward: " . $wardSlug);
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
        try {
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
        } catch (\Exception $e) {
            Log::error("Error getting air quality data: " . $e->getMessage());

            // Return fallback data
            return [
                'aqi' => 50,
                'aqi_category' => 'Trung bình',
                'aqi_color' => '#ffff00',
                'description' => 'Chất lượng không khí chấp nhận được. Tuy nhiên, một số chất gây ô nhiễm có thể gây lo ngại cho một số người nhạy cảm.',
                'co' => 300.0,
                'no2' => 10.0,
                'o3' => 60.0,
                'pm10' => 30.0,
                'pm2_5' => 20.0,
                'so2' => 5.0
            ];
        }
    }

    /**
     * Get latest weather news articles
     */
    protected function getLatestArticles()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error("Error getting latest articles: " . $e->getMessage());
            return $this->weatherService->getWeatherNews();
        }
    }

    /**
     * Get basic weather data for a district (optimized version)
     */
    protected function getBasicDistrictWeather($district, $province)
    {
        try {
            $cacheKey = 'district_basic_weather_' . $district->code . '_' . date('Y-m-d');

            if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
                $cachedData = Cache::get($cacheKey);
                if ($cachedData && !empty($cachedData)) {
                    return $cachedData;
                }
            }

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
            Log::error("Error getting district weather for {$district->name}: " . $e->getMessage());
        }

        // Fallback data
        return [
            'weather_image' => '/assets/images/weather-1/01d.png',
            'temperature' => 30
        ];
    }

    /**
     * Get Vietnamese day name from a date
     */
    protected function getDayName($dateString)
    {
        try {
            if (empty($dateString)) {
                return 'T2'; // Default to Monday if date is missing
            }

            $timestamp = strtotime($dateString);
            if ($timestamp === false) {
                return 'T2'; // Default to Monday if date format is invalid
            }

            $dayOfWeek = date('w', $timestamp);

            $days = [
                0 => 'CN',
                1 => 'T2',
                2 => 'T3',
                3 => 'T4',
                4 => 'T5',
                5 => 'T6',
                6 => 'T7'
            ];

            return $days[$dayOfWeek] ?? 'T2';
        } catch (\Exception $e) {
            Log::error("Error getting day name: " . $e->getMessage());
            return 'T2'; // Default to Monday if any error occurs
        }
    }

    /**
     * Build path for a location
     *
     * @param District|Province|Ward $locationModel
     * @return string
     */
    protected function buildPathForLocation(Province|Ward|District $locationModel, string | null $period = null)
    {
        $path = $locationModel->getSlug();

        switch ($period)
        {
            case 'hourly':
                $path .= '/theo-gio';
                break;
            case 'tomorrow':
                $path .= '/ngay-mai';
                break;
            case 'daily-3':
                $path .= '/3-ngay-toi';
                break;
            case 'daily-5':
                $path .= '/5-ngay-toi';
                break;
            case 'daily-7':
                $path .= '/7-ngay-toi';
                break;
            case 'daily-10':
                $path .= '/10-ngay-toi';
                break;
            case 'daily-15':
                $path .= '/15-ngay-toi';
                break;
            case 'daily-20':
                $path .= '/20-ngay-toi';
                break;
            case 'daily-30':
                $path .= '/30-ngay-toi';
                break;
        }

        return $path;
    }

    /**
     * Checks and retrieves custom metadata for a given path.
     *
     * This method checks if the provided path matches any custom path settings defined
     * for the site. If a match is found, it returns an array containing the associated
     * title and description metadata. If no match is found, it returns null.
     *
     * @param string $path The path to check against custom metadata settings.
     * @return array|null An associative array with 'title' and 'description' keys if metadata is found, or null otherwise.
     */
    protected function checkCustomPathMetadata($path)
    {
        // Get custom path settings
        $pathTitles = setting('site_path_title') ? json_decode(setting('site_path_title'), true) : [];
        $pathDescriptions = setting('site_path_description') ? json_decode(setting('site_path_description'), true) : [];

        foreach ($pathTitles as $index => $titleData) {
            $settingPath = array_key_first($titleData);

            // Check if current path matches the setting path
            if ($path === $settingPath || rtrim($path, '/') === rtrim($settingPath, '/')) {
                return [
                    'title' => $titleData[$settingPath],
                    'description' => $pathDescriptions[$index][$settingPath] ?? ''
                ];
            }
        }

        return null;
    }
}
