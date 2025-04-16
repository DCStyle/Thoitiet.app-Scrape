<?php

namespace App\Services;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private $baseUrl = 'https://api.open-meteo.com/v1/forecast';
    private $geocodingUrl = 'https://rsapi.goong.io/geocode';

    // Weather condition mapping
    private $weatherCodes = [
        0 => ['icon' => 'fa-sun', 'description' => 'Trời quang đãng'],
        1 => ['icon' => 'fa-sun', 'description' => 'Bầu trời quang đãng'],
        2 => ['icon' => 'fa-cloud-sun', 'description' => 'Có mây rải rác'],
        3 => ['icon' => 'fa-cloud', 'description' => 'Nhiều mây'],
        45 => ['icon' => 'fa-smog', 'description' => 'Sương mù'],
        48 => ['icon' => 'fa-smog', 'description' => 'Sương mù đọng băng'],
        51 => ['icon' => 'fa-cloud-rain', 'description' => 'Mưa phùn nhẹ'],
        53 => ['icon' => 'fa-cloud-rain', 'description' => 'Mưa phùn vừa'],
        55 => ['icon' => 'fa-cloud-rain', 'description' => 'Mưa phùn dày đặc'],
        61 => ['icon' => 'fa-cloud-rain', 'description' => 'Mưa nhẹ'],
        63 => ['icon' => 'fa-cloud-rain', 'description' => 'Mưa vừa'],
        65 => ['icon' => 'fa-cloud-showers-heavy', 'description' => 'Mưa nặng hạt'],
        71 => ['icon' => 'fa-snowflake', 'description' => 'Tuyết rơi nhẹ'],
        73 => ['icon' => 'fa-snowflake', 'description' => 'Tuyết rơi vừa'],
        75 => ['icon' => 'fa-snowflake', 'description' => 'Tuyết rơi dày đặc'],
        80 => ['icon' => 'fa-cloud-rain', 'description' => 'Mưa rào nhẹ'],
        81 => ['icon' => 'fa-cloud-rain', 'description' => 'Mưa rào vừa'],
        82 => ['icon' => 'fa-cloud-showers-heavy', 'description' => 'Mưa rào nặng hạt'],
        95 => ['icon' => 'fa-bolt', 'description' => 'Mưa dông nhẹ'],
        96 => ['icon' => 'fa-bolt', 'description' => 'Mưa dông có mưa đá nhẹ'],
        99 => ['icon' => 'fa-bolt', 'description' => 'Mưa dông có mưa đá nặng']
    ];

    /**
     * Get weather data for a location with optimized data levels
     *
     * @param string $location Location code or slug
     * @param string $type Location type (province, district, ward)
     * @param string $dataLevel Level of data to request (minimal, basic, full)
     * @return array|null Weather data
     */
    public function getWeatherData($location, $type = 'province', $dataLevel = 'full')
    {
        // Get location data with model instance
        $locationData = $this->getLocationData($location, $type);

        if (!$locationData) {
            // If not found in database, search using open-meteo geocoding API
            $coordinates = $this->geocodeLocation($location);
            if (!$coordinates) {
                return null;
            }
            $locationName = $coordinates['name'];
            $lat = $coordinates['lat'];
            $lng = $coordinates['lng'];
        } else {
            $locationName = $locationData['name'];
            $locationModel = $locationData['model'];

            // Check if coordinates exist in the model
            $coordinates = $locationModel->getCoordinates();

            if ($coordinates) {
                $lat = $coordinates['lat'];
                $lng = $coordinates['lng'];
            } else {
                // If coordinates don't exist in model, try to geocode
                try {
                    // Create address based on location type
                    if ($type === 'province') {
                        $addressToGeocode = $locationModel->full_name;
                    } elseif ($type === 'district') {
                        $addressToGeocode = $locationModel->full_name;
                        if ($locationModel->province) {
                            $addressToGeocode .= ', ' . $locationModel->province->name;
                        }
                    } elseif ($type === 'ward') {
                        $addressToGeocode = $locationModel->full_name;
                        if ($locationModel->district) {
                            $addressToGeocode .= ', ' . $locationModel->district->name;
                            if ($locationModel->district->province) {
                                $addressToGeocode .= ', ' . $locationModel->district->province->name;
                            }
                        }
                    } else {
                        throw new \Exception("Invalid location type: {$type}");
                    }

                    $fallbackCoordinates = $this->geocodeLocation($addressToGeocode);
                    if ($fallbackCoordinates) {
                        $lat = $fallbackCoordinates['lat'];
                        $lng = $fallbackCoordinates['lng'];

                        // Save these coordinates to the model
                        $locationModel->lat = $lat;
                        $locationModel->lng = $lng;
                        $locationModel->save();

                        Log::info("Open-meteo geocoded and saved coordinates for {$addressToGeocode}: {$lat}, {$lng}");
                    } else {
                        // If all geocoding fails, return null
                        Log::warning("Geocoding failed for {$addressToGeocode}. No coordinates available.");
                        return null;
                    }
                } catch (\Exception $e) {
                    Log::error("Error geocoding {$locationName}: " . $e->getMessage());
                    return null;
                }
            }
        }

        // Generate cache key based on location, type, data level and current date
        $cacheKey = 'weather_' . $location . '_' . $type . '_' . $dataLevel . '_' . date('Y-m-d');

        // Check if we have cached data
        if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
            return Cache::get($cacheKey);
        }

        try {
            // Base parameters
            $apiParams = [
                'latitude' => $lat,
                'longitude' => $lng,
                'timezone' => 'Asia/Ho_Chi_Minh',
            ];

            // Set API parameters based on data level
            switch ($dataLevel) {
                case 'minimal':
                    // Just current weather - for widget displays and listing pages
                    $apiParams['current_weather'] = true;
                    break;

                case 'basic':
                    // Current + basic daily - for district listings and summaries
                    $apiParams['current_weather'] = true;
                    $apiParams['daily'] = 'weathercode,temperature_2m_max,temperature_2m_min';
                    $apiParams['forecast_days'] = 3;
                    break;

                case 'full':
                default:
                    // Complete data - for detailed location pages
                    $apiParams['hourly'] = 'temperature_2m,relativehumidity_2m,precipitation,precipitation_probability,weathercode,windspeed_10m,visibility';
                    $apiParams['daily'] = 'weathercode,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,sunrise,sunset';
                    $apiParams['forecast_days'] = 15;
                    $apiParams['current_weather'] = true;
                    break;
            }

            // Make API request
            $response = Http::get($this->baseUrl, $apiParams);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch weather data: {$response->status()}");
            }

            $data = $response->json();

            // Process data based on level
            switch ($dataLevel) {
                case 'minimal':
                    $formattedData = $this->formatMinimalWeatherData($data, $locationName);
                    break;

                case 'basic':
                    $formattedData = $this->formatBasicWeatherData($data, $locationName);
                    break;

                case 'full':
                default:
                    $formattedData = $this->formatWeatherData($data, $locationName);
                    break;
            }

            // Longer cache for lighter data
            $cacheDuration = ($dataLevel === 'full') ? 3 : 6;
            if ((bool)setting('cache_enabled')) {
                Cache::put($cacheKey, $formattedData, now()->addHours($cacheDuration));
            }

            return $formattedData;
        } catch (\Exception $e) {
            Log::error("Weather API request failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Format minimal weather data (current conditions only)
     */
    private function formatMinimalWeatherData($data, $locationName)
    {
        $current = $data['current_weather'] ?? null;

        if (!$current) {
            return null;
        }

        $weatherCode = $current['weathercode'];
        $weather = $this->getWeatherDescription($weatherCode);

        return [
            'location' => $locationName,
            'current' => [
                'temperature' => round($current['temperature']),
                'weather_code' => $weatherCode,
                'weather_description' => $weather['description'],
                'weather_icon' => $weather['icon'],
                'weather_image' => $this->getWeatherImagePath($weatherCode)
            ]
        ];
    }

    /**
     * Format basic weather data (current + simplified daily summary)
     */
    private function formatBasicWeatherData($data, $locationName)
    {
        $minimal = $this->formatMinimalWeatherData($data, $locationName);

        if (!$minimal || !isset($data['daily'])) {
            return $minimal;
        }

        $daily = $data['daily'];
        $dailyForecasts = [];
        $days = count($daily['time']);

        for ($i = 0; $i < min($days, 3); $i++) {
            $weatherCode = $daily['weathercode'][$i];
            $weatherInfo = $this->getWeatherDescription($weatherCode);

            $dailyForecasts[] = [
                'date' => date('d/m', strtotime($daily['time'][$i])),
                'day_name' => $this->getDayName($daily['time'][$i]),
                'max_temp' => round($daily['temperature_2m_max'][$i]),
                'min_temp' => round($daily['temperature_2m_min'][$i]),
                'weather_code' => $weatherCode,
                'weather_description' => $weatherInfo['description'],
                'weather_icon' => $weatherInfo['icon'],
                'weather_image' => $this->getWeatherImagePath($weatherCode)
            ];
        }

        $minimal['daily'] = $dailyForecasts;
        return $minimal;
    }

    /**
     * Get location data from the database
     *
     * @param string $location Location code or code_name
     * @param string $type Location type (province, district, ward)
     * @return array|null Location data
     */
    private function getLocationData($location, $type = 'province')
    {
        switch ($type) {
            case 'province':
                $locationModel = Province::where('code', $location)
                    ->orWhere('code_name', $location)
                    ->first();
                break;
            case 'district':
                $locationModel = District::where('code', $location)
                    ->orWhere('code_name', $location)
                    ->first();
                break;
            case 'ward':
                $locationModel = Ward::where('code', $location)
                    ->orWhere('code_name', $location)
                    ->first();
                break;
            default:
                return null;
        }

        if (!$locationModel) {
            return null;
        }

        return [
            'name' => $locationModel->name,
            'model' => $locationModel
        ];
    }

    /**
     * Get featured locations weather (optimized)
     */
    public function getFeaturedLocationsWeather()
    {
        $cacheKey = 'featured_locations_' . date('Y-m-d');

        if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
            return Cache::get($cacheKey);
        }

        $featuredProvinces = Province::whereIn('code_name', [
            'ha_noi', 'da_nang', 'quang_nam', 'ba_ria_vung_tau',
            'ho_chi_minh', 'ben_tre', 'thua_thien_hue', 'lao_cai',
            'hai_phong', 'ninh_binh', 'binh_dinh', 'khanh_hoa'
        ])->get();

        $results = [];
        foreach ($featuredProvinces as $province) {
            // Use minimal data level for featured locations
            $data = $this->getWeatherData($province->code_name, 'province', 'minimal');
            if ($data) {
                $results[] = [
                    'location' => $data['location'],
                    'current' => $data['current']
                ];
            }
        }

        if ((bool)setting('cache_enabled')) {
            Cache::put($cacheKey, $results, now()->addHours(6)); // Cache longer for lightweight data
        }

        return $results;
    }

    /**
     * Get weather for all provinces
     */
    public function getAllProvincesWeather()
    {
        $cacheKey = 'all_provinces_weather_' . date('Y-m-d');

        if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
            return Cache::get($cacheKey);
        }

        $provinces = Province::all();
        $results = [];

        foreach ($provinces as $province) {
            $data = $this->getWeatherData($province->code_name, 'province');
            if ($data) {
                $results[] = [
                    'province' => $province,
                    'weather' => $data
                ];
            }
        }

        if ((bool)setting('cache_enabled')) {
            Cache::put($cacheKey, $results, now()->addHours(3));
        }

        return $results;
    }

    /**
     * Get weather news
     */
    public function getWeatherNews()
    {
        // In a real scenario, this might come from your articles database
        return [
            [
                'title' => 'Hòa mình vào lá phổi xanh giữa nắng và gió cao nguyên ở biển hồ chè Gia Lai',
                'image' => 'https://example.com/images/news1.jpg',
                'slug' => 'hoa-minh-vao-la-phoi-xanh'
            ],
            [
                'title' => 'THÔNG CÁO BÁO CHÍ: CHUYỂN ĐỔI TÊN MIỀN TỪ THOITIET.APP SANG THOITIET247.VN',
                'image' => 'https://example.com/images/news2.jpg',
                'slug' => 'thong-cao-bao-chi-chuyen-doi-ten-mien'
            ],
            [
                'title' => 'Chiều 7/9 bão số 3 Yagi đã đổ sát bờ biển Quảng Ninh, Hải Phòng và tiếp tục đi qua các tỉnh này',
                'image' => 'https://example.com/images/news3.jpg',
                'slug' => 'chieu-7-9-bao-so-3-yagi'
            ],
            [
                'title' => 'DỰ BÁO THỜI TIẾT - ĐÊM 15 VÀ NGÀY 16/04/2023',
                'image' => 'https://example.com/images/news4.jpg',
                'slug' => 'du-bao-thoi-tiet-dem-15-va-ngay-16-04-2023'
            ]
        ];
    }

    /**
     * Geocode a location name to get coordinates using open-meteo API
     */
    public function geocodeLocation($query)
    {
        try {
            // Use the Goong.io API
            $response = Http::get($this->geocodingUrl, [
                'address' => $query,
                'api_key' => '4ZIRqmkfhJXwuLaYx5LxT6pMGeRcuTOup34iC0cn'
            ]);

            $data = $response->json();

            // Check if the response is successful and has results
            if (!$response->successful() || $data['status'] !== 'OK' || empty($data['results'])) {
                Log::warning("Geocoding failed for query: {$query}. Status: " . ($data['status'] ?? 'unknown'));
                return null;
            }

            // Get the first result
            $result = $data['results'][0];

            // Extract the needed data from the result
            return [
                'name' => $result['formatted_address'], // Using formatted_address as the name
                'lat' => $result['geometry']['location']['lat'],
                'lng' => $result['geometry']['location']['lng']
            ];

        } catch (\Exception $e) {
            Log::error("Geocoding failed: " . $e->getMessage());
            return null;
        }
    }

    // The remaining methods stay the same
    public function formatWeatherData($data, $locationName)
    {
        $current = $data['current_weather'] ?? null;
        $daily = $data['daily'] ?? null;
        $hourly = $data['hourly'] ?? null;

        if (!$current || !$daily || !$hourly) {
            return null;
        }

        // Get current weather code
        $weatherCode = $current['weathercode'];
        $weather = $this->getWeatherDescription($weatherCode);

        // Get current visibility (in meters, convert to km)
        $visibility = $this->getCurrentHourlyValue($hourly, 'visibility');
        $visibilityKm = is_numeric($visibility) ? round($visibility / 1000, 1) : 10; // Default to 10km

        // Format current weather
        $currentWeather = [
            'temperature' => round($current['temperature']),
            'feels_like' => $this->calculateFeelsLike($current['temperature'],
                $this->getCurrentHourlyValue($hourly, 'relativehumidity_2m'),
                $current['windspeed']),
            'weather_code' => $weatherCode,
            'weather_description' => $weather['description'],
            'weather_icon' => $weather['icon'],
            'weather_image' => $this->getWeatherImagePath($weatherCode),
            'humidity' => $this->getCurrentHourlyValue($hourly, 'relativehumidity_2m'),
            'wind_speed' => number_format($current['windspeed'], 2),
            'precipitation' => $this->getCurrentHourlyValue($hourly, 'precipitation'),
            'precipitation_probability' => $this->getCurrentHourlyValue($hourly, 'precipitation_probability'),
            'visibility' => $visibilityKm
        ];

        // Format daily forecast for next days
        $dailyForecasts = [];
        $days = count($daily['time']);

        for ($i = 0; $i < $days; $i++) {
            $weatherCode = $daily['weathercode'][$i];
            $weatherInfo = $this->getWeatherDescription($weatherCode);

            $sunrise = isset($daily['sunrise'][$i]) ? date('H:i', strtotime($daily['sunrise'][$i])) : '06:00';
            $sunset = isset($daily['sunset'][$i]) ? date('H:i', strtotime($daily['sunset'][$i])) : '18:00';

            $dailyForecasts[] = [
                'date' => date('d/m', strtotime($daily['time'][$i])),
                'full_date' => date('Y-m-d', strtotime($daily['time'][$i])),
                'day_name' => $this->getDayName($daily['time'][$i]),
                'max_temp' => round($daily['temperature_2m_max'][$i]),
                'min_temp' => round($daily['temperature_2m_min'][$i]),
                'weather_code' => $weatherCode,
                'weather_description' => $weatherInfo['description'],
                'weather_icon' => $weatherInfo['icon'],
                'weather_image' => $this->getWeatherImagePath($weatherCode),
                'precipitation_sum' => $daily['precipitation_sum'][$i],
                'precipitation_probability' => $daily['precipitation_probability_max'][$i],
                'sunrise' => $sunrise,
                'sunset' => $sunset
            ];
        }

        // Format hourly forecast for today and upcoming days (48 hours)
        $hourlyForecasts = [];
        $currentHour = (int)date('H');

        // Get hourly forecasts for the next 48 hours
        for ($i = $currentHour; $i < $currentHour + 48 && $i < count($hourly['time']); $i++) {
            $weatherCode = $hourly['weathercode'][$i];
            $weatherInfo = $this->getWeatherDescription($weatherCode);

            $hourlyForecasts[] = [
                'time' => date('H:i', strtotime($hourly['time'][$i])),
                'full_time' => $hourly['time'][$i],
                'temperature' => round($hourly['temperature_2m'][$i]),
                'weather_code' => $weatherCode,
                'weather_description' => $weatherInfo['description'],
                'weather_icon' => $weatherInfo['icon'],
                'weather_image' => $this->getWeatherImagePath($weatherCode, $i),
                'precipitation' => $hourly['precipitation'][$i],
                'precipitation_probability' => isset($hourly['precipitation_probability'][$i]) ?
                    $hourly['precipitation_probability'][$i] : 0,
                'wind_speed' => $hourly['windspeed_10m'][$i],
                'humidity' => $hourly['relativehumidity_2m'][$i],
                'visibility' => isset($hourly['visibility'][$i]) ? round($hourly['visibility'][$i] / 1000, 1) : 10
            ];
        }

        // Get time of day temperatures
        $timeOfDayTemps = $this->getTimeOfDayTemperatures($hourlyForecasts);

        return [
            'location' => $locationName,
            'current' => $currentWeather,
            'daily' => $dailyForecasts,
            'hourly' => $hourlyForecasts,
            'time_of_day' => $timeOfDayTemps,
            'sunrise' => $dailyForecasts[0]['sunrise'] ?? '06:00',
            'sunset' => $dailyForecasts[0]['sunset'] ?? '18:00'
        ];
    }

    /**
     * Calculate temperatures for different times of day
     *
     * @param array $hourlyForecasts
     * @return array
     */
    public function getTimeOfDayTemperatures($hourlyForecasts)
    {
        // Define time ranges
        $morningHours = [6, 7, 8, 9, 10, 11];
        $dayHours = [12, 13, 14, 15, 16, 17];
        $eveningHours = [18, 19, 20, 21];
        $nightHours = [22, 23, 0, 1, 2, 3, 4, 5];

        $timeOfDayTemps = [
            'morning' => ['temperatures' => []],
            'day' => ['temperatures' => []],
            'evening' => ['temperatures' => []],
            'night' => ['temperatures' => []],
        ];

        // Group hourly temperatures by time of day
        foreach ($hourlyForecasts as $hour) {
            if (empty($hour['full_time'])) continue;

            $hourOfDay = (int)date('G', strtotime($hour['full_time']));

            if (in_array($hourOfDay, $morningHours)) {
                $timeOfDayTemps['morning']['temperatures'][] = $hour['temperature'];
            } elseif (in_array($hourOfDay, $dayHours)) {
                $timeOfDayTemps['day']['temperatures'][] = $hour['temperature'];
            } elseif (in_array($hourOfDay, $eveningHours)) {
                $timeOfDayTemps['evening']['temperatures'][] = $hour['temperature'];
            } elseif (in_array($hourOfDay, $nightHours)) {
                $timeOfDayTemps['night']['temperatures'][] = $hour['temperature'];
            }
        }

        // Calculate min/max for each time period
        foreach ($timeOfDayTemps as $period => $data) {
            if (!empty($data['temperatures'])) {
                $timeOfDayTemps[$period]['temperature'] = max($data['temperatures']);
                $timeOfDayTemps[$period]['temperature_night'] = min($data['temperatures']);
            } else {
                $timeOfDayTemps[$period]['temperature'] = null;
                $timeOfDayTemps[$period]['temperature_night'] = null;
            }
            unset($timeOfDayTemps[$period]['temperatures']);
        }

        return $timeOfDayTemps;
    }

    /**
     * Get weather description based on weather code
     */
    public function getWeatherDescription($code)
    {
        if (isset($this->weatherCodes[$code])) {
            return $this->weatherCodes[$code];
        }

        // Default if code is not found
        return [
            'icon' => 'fa-cloud',
            'description' => 'Thay đổi'
        ];
    }

    /**
     * Get weather image path based on weather code
     */
    public function getWeatherImagePath($code, $hour = null)
    {
        // Determine if it's daytime or nighttime
        if ($hour === null) {
            $hour = (int)date('H');
        } elseif (is_numeric($hour) && $hour >= 24) {
            $hour = $hour % 24; // Normalize hour if it's beyond 24
        }

        $isDaytime = ($hour >= 6 && $hour < 18); // Consider 6 AM to 6 PM as daytime
        $timeCode = $isDaytime ? 'd' : 'n';

        // Map weather code to icon code
        $iconFile = $this->getWeatherIconCode($code, $timeCode);

        return '/assets/images/weather-1/' . $iconFile . '.png';
    }

    /**
     * Get the weather icon code based on weather code and time of day
     */
    public function getWeatherIconCode($code, $timeOfDay = 'd')
    {
        // Default to daytime if not specified
        $timeOfDay = ($timeOfDay === 'n') ? 'n' : 'd';

        // Map weather codes to icon filenames
        switch (true) {
            case $code === 0:
                // Clear sky
                return '01' . $timeOfDay;

            case $code === 1:
            case $code === 2:
                // Mainly clear, partly cloudy
                return '02' . $timeOfDay;

            case $code === 3:
            case $code === 45:
            case $code === 48:
                // Overcast, fog
                return '03' . $timeOfDay;

            case $code === 51:
            case $code === 53:
            case $code === 55:
            case $code === 61:
            case $code === 63:
            case $code === 80:
            case $code === 81:
                // Light to moderate rain
                return '09' . $timeOfDay;

            case $code === 65:
            case $code === 82:
                // Heavy rain
                return '10' . $timeOfDay;

            case $code === 71:
            case $code === 73:
            case $code === 75:
                // Snow - using rain icons for now
                return '10' . $timeOfDay;

            case $code === 95:
            case $code === 96:
            case $code === 99:
                // Thunderstorms
                return '04' . $timeOfDay;

            default:
                // Default to cloudy
                return '03' . $timeOfDay;
        }
    }

    /**
     * Get current value from hourly data
     */
    public function getCurrentHourlyValue($hourly, $key)
    {
        $currentHour = (int)date('H');
        if (isset($hourly[$key][$currentHour])) {
            return $hourly[$key][$currentHour];
        }

        return 0;
    }

    /**
     * Calculate feels like temperature
     */
    public function calculateFeelsLike($temperature, $humidity, $windSpeed)
    {
        // Simple approximation of "feels like" temperature
        if ($temperature > 27 && $humidity > 40) {
            // Hot and humid - feels warmer
            return round($temperature + ($humidity - 40) / 10);
        } else if ($temperature < 10 && $windSpeed > 5) {
            // Cold and windy - wind chill effect
            return round($temperature - ($windSpeed - 5) / 5);
        }

        return round($temperature);
    }

    /**
     * Get Vietnamese day name from a date
     */
    public function getDayName($dateString)
    {
        $timestamp = strtotime($dateString);
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

        return $days[$dayOfWeek];
    }
}
