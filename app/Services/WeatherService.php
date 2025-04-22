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
    private $baseUrl = 'http://api.weatherapi.com/v1';

    // Mapping WeatherAPI.com condition codes to local icon codes (partial list)
    private $conditionCodeToIcon = [
        1000 => ['day' => '01d', 'night' => '01n'], // Sunny/Clear
        1003 => ['day' => '02d', 'night' => '02n'], // Partly cloudy
        1006 => ['day' => '03d', 'night' => '03n'], // Cloudy
        1009 => ['day' => '04d', 'night' => '04n'], // Overcast
        1030 => ['day' => '50d', 'night' => '50n'], // Mist
        1063 => ['day' => '09d', 'night' => '09n'], // Patchy rain possible
        1066 => ['day' => '13d', 'night' => '13n'], // Patchy snow possible
        1069 => ['day' => '09d', 'night' => '09n'], // Patchy sleet possible
        1072 => ['day' => '09d', 'night' => '09n'], // Patchy freezing drizzle
        1087 => ['day' => '04d', 'night' => '04n'], // Thundery outbreaks possible
        1114 => ['day' => '13d', 'night' => '13n'], // Blowing snow
        1117 => ['day' => '13d', 'night' => '13n'], // Blizzard
        1135 => ['day' => '50d', 'night' => '50n'], // Fog
        1147 => ['day' => '50d', 'night' => '50n'], // Freezing fog
        1150 => ['day' => '09d', 'night' => '09n'], // Patchy light drizzle
        1153 => ['day' => '09d', 'night' => '09n'], // Light drizzle
        1168 => ['day' => '09d', 'night' => '09n'], // Freezing drizzle
        1171 => ['day' => '10d', 'night' => '10n'], // Heavy freezing drizzle
        1180 => ['day' => '09d', 'night' => '09n'], // Patchy light rain
        1183 => ['day' => '09d', 'night' => '09n'], // Light rain
        1186 => ['day' => '09d', 'night' => '09n'], // Moderate rain at times
        1189 => ['day' => '10d', 'night' => '10n'], // Moderate rain
        1192 => ['day' => '10d', 'night' => '10n'], // Heavy rain at times
        1195 => ['day' => '10d', 'night' => '10n'], // Heavy rain
        1198 => ['day' => '09d', 'night' => '09n'], // Light freezing rain
        1201 => ['day' => '10d', 'night' => '10n'], // Moderate/heavy freezing rain
        1204 => ['day' => '13d', 'night' => '13n'], // Light sleet
        1207 => ['day' => '13d', 'night' => '13n'], // Moderate/heavy sleet
        1210 => ['day' => '13d', 'night' => '13n'], // Patchy light snow
        1213 => ['day' => '13d', 'night' => '13n'], // Light snow
        1216 => ['day' => '13d', 'night' => '13n'], // Patchy moderate snow
        1219 => ['day' => '13d', 'night' => '13n'], // Moderate snow
        1222 => ['day' => '13d', 'night' => '13n'], // Patchy heavy snow
        1225 => ['day' => '13d', 'night' => '13n'], // Heavy snow
        1237 => ['day' => '13d', 'night' => '13n'], // Ice pellets
        1240 => ['day' => '09d', 'night' => '09n'], // Light rain shower
        1243 => ['day' => '10d', 'night' => '10n'], // Moderate/heavy rain shower
        1246 => ['day' => '10d', 'night' => '10n'], // Torrential rain shower
        1249 => ['day' => '13d', 'night' => '13n'], // Light sleet showers
        1252 => ['day' => '13d', 'night' => '13n'], // Moderate/heavy sleet showers
        1255 => ['day' => '13d', 'night' => '13n'], // Light snow showers
        1258 => ['day' => '13d', 'night' => '13n'], // Moderate/heavy snow showers
        1261 => ['day' => '13d', 'night' => '13n'], // Light showers of ice pellets
        1264 => ['day' => '13d', 'night' => '13n'], // Moderate/heavy ice pellet showers
        1273 => ['day' => '04d', 'night' => '04n'], // Patchy light rain with thunder
        1276 => ['day' => '04d', 'night' => '04n'], // Moderate/heavy rain with thunder
        1279 => ['day' => '04d', 'night' => '04n'], // Patchy light snow with thunder
        1282 => ['day' => '04d', 'night' => '04n'], // Moderate/heavy snow with thunder
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
        try {
            // Get location data with model instance
            $locationData = $this->getLocationData($location, $type);

            if ($locationData) {
                $locationModel = $locationData['model'];
                $coordinates = $locationModel->getCoordinates();
                if ($coordinates) {
                    $q = $coordinates['lat'] . ',' . $coordinates['lng'];
                } else {
                    // Construct full address
                    if ($type === 'province') {
                        $q = $locationModel->full_name ?? $locationModel->name ?? $location;
                    } elseif ($type === 'district') {
                        $province = $locationModel->province;
                        $q = ($locationModel->full_name ?? $locationModel->name) . ', ' .
                            ($province ? ($province->name ?? '') : '');
                    } elseif ($type === 'ward') {
                        $district = $locationModel->district;
                        $province = $district ? $district->province : null;

                        $q = ($locationModel->full_name ?? $locationModel->name);
                        if ($district) {
                            $q .= ', ' . ($district->name ?? '');
                        }
                        if ($province) {
                            $q .= ', ' . ($province->name ?? '');
                        }
                    }
                }
                $locationName = $locationData['name'];
            } else {
                $q = $location;
                $locationName = $location;
            }

            // Generate cache key
            $cacheKey = 'weather_' . md5($q) . '_' . $dataLevel . '_' . date('Y-m-d');

            if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
                $cachedData = Cache::get($cacheKey);
                if (!empty($cachedData)) {
                    return $cachedData;
                }
                // If cache exists but is empty/invalid, continue to retrieve fresh data
            }

            $apiKey = config('services.weatherapi.key');
            if (empty($apiKey)) {
                Log::error("Weather API key not configured");
                return null;
            }

            $endpoint = '/forecast.json';

            $params = [
                'key' => $apiKey,
                'q' => $q,
                'days' => ($dataLevel === 'minimal') ? 1 : 7, // Free plan: max 3 days
                'aqi' => 'no',
                'alerts' => 'no',
                'lang' => 'vi'
            ];

            $response = Http::timeout(10)
                ->retry(2, 1000)
                ->get($this->baseUrl . $endpoint, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch weather data: HTTP ' . $response->status());
            }

            $data = $response->json();

            // Check for API error response
            if (isset($data['error'])) {
                Log::warning("WeatherAPI error: " . ($data['error']['message'] ?? 'Unknown error'));
                return null;
            }

            // Verify required data is present
            if (!isset($data['current']) || !isset($data['forecast']['forecastday'])) {
                Log::warning("WeatherAPI returned incomplete data for location: {$q}");
                return null;
            }

            // Process data based on level
            try {
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
            } catch (\Exception $e) {
                Log::error("Error formatting weather data: " . $e->getMessage());
                return null;
            }

            // Cache the data if valid
            if ($formattedData) {
                $cacheDuration = ($dataLevel === 'full') ? 3 : 6;
                if ((bool)setting('cache_enabled')) {
                    Cache::put($cacheKey, $formattedData, now()->addHours($cacheDuration));
                }
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
        $current = $data['current'] ?? null;

        if (!$current) {
            return null;
        }

        $condition = $current['condition'] ?? ['code' => 1003, 'text' => 'Cloudy'];
        $isDay = $current['is_day'] ?? 1;
        $weatherIconCode = $this->getWeatherIconCode($condition['code'] ?? 1003, $isDay);

        return [
            'location' => $locationName,
            'current' => [
                'temperature' => round($current['temp_c'] ?? 25),
                'weather_code' => $condition['code'] ?? 1003,
                'weather_description' => $condition['text'] ?? 'Unknown',
                'weather_icon' => $weatherIconCode,
                'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png'
            ]
        ];
    }

    /**
     * Format basic weather data (current + simplified daily summary)
     */
    private function formatBasicWeatherData($data, $locationName)
    {
        $minimal = $this->formatMinimalWeatherData($data, $locationName);

        if (!$minimal || !isset($data['forecast']['forecastday'])) {
            return $minimal;
        }

        $forecastDays = $data['forecast']['forecastday'];
        $dailyForecasts = [];

        foreach (array_slice($forecastDays, 0, 3) as $day) {
            if (!isset($day['day']) || !isset($day['date'])) {
                continue;
            }

            $condition = $day['day']['condition'] ?? ['code' => 1003, 'text' => 'Cloudy'];
            $weatherIconCode = $this->getWeatherIconCode($condition['code'] ?? 1003, true); // Daytime for daily forecast

            $dailyForecasts[] = [
                'date' => date('d/m', strtotime($day['date'])),
                'day_name' => $this->getDayName($day['date']),
                'max_temp' => round($day['day']['maxtemp_c'] ?? 30),
                'min_temp' => round($day['day']['mintemp_c'] ?? 20),
                'weather_code' => $condition['code'] ?? 1003,
                'weather_description' => $condition['text'] ?? 'Unknown',
                'weather_icon' => $weatherIconCode,
                'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png'
            ];
        }

        $minimal['daily'] = $dailyForecasts;
        return $minimal;
    }

    /**
     * Format full weather data (current + daily + hourly)
     */
    public function formatWeatherData($data, $locationName)
    {
        $current = $data['current'] ?? null;
        $forecast = $data['forecast']['forecastday'] ?? null;

        if (!$current || !$forecast || empty($forecast)) {
            Log::warning("Missing required current or forecast data");
            return null;
        }

        $condition = $current['condition'] ?? ['code' => 1003, 'text' => 'Cloudy'];
        $isDay = $current['is_day'] ?? 1;
        $weatherIconCode = $this->getWeatherIconCode($condition['code'] ?? 1003, $isDay);

        // Get current hour safely
        $hourIndex = (int)date('H'); // Convert to integer to remove leading zeros
        $precipitationProbability = 0; // Default value

        // Check if the hour data exists before accessing it
        if (isset($forecast[0]['hour']) &&
            array_key_exists($hourIndex, $forecast[0]['hour']) &&
            isset($forecast[0]['hour'][$hourIndex]['chance_of_rain'])) {
            $precipitationProbability = $forecast[0]['hour'][$hourIndex]['chance_of_rain'];
        }

        // Format current weather
        $currentWeather = [
            'temperature' => round($current['temp_c'] ?? 25),
            'feels_like' => round($current['feelslike_c'] ?? 25),
            'weather_code' => $condition['code'] ?? 1003,
            'weather_description' => $condition['text'] ?? 'Unknown',
            'weather_icon' => $weatherIconCode,
            'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png',
            'humidity' => $current['humidity'] ?? 70,
            'wind_speed' => number_format($current['wind_kph'] ?? 5, 2),
            'precipitation' => $current['precip_mm'] ?? 0,
            'precipitation_probability' => $precipitationProbability,
            'visibility' => $current['vis_km'] ?? 10
        ];

        // Format daily forecast
        $dailyForecasts = [];
        foreach ($forecast as $day) {
            if (!isset($day['day']) || !isset($day['date'])) {
                continue;
            }

            $condition = $day['day']['condition'] ?? ['code' => 1003, 'text' => 'Cloudy'];
            $weatherIconCode = $this->getWeatherIconCode($condition['code'] ?? 1003, true); // Daytime for daily

            $dailyForecasts[] = [
                'date' => date('d/m', strtotime($day['date'])),
                'full_date' => date('Y-m-d', strtotime($day['date'])),
                'day_name' => $this->getDayName($day['date']),
                'max_temp' => round($day['day']['maxtemp_c'] ?? 30),
                'min_temp' => round($day['day']['mintemp_c'] ?? 20),
                'weather_code' => $condition['code'] ?? 1003,
                'weather_description' => $condition['text'] ?? 'Unknown',
                'weather_icon' => $weatherIconCode,
                'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png',
                'precipitation_sum' => $day['day']['totalprecip_mm'] ?? 0,
                'precipitation_probability' => $day['day']['daily_chance_of_rain'] ?? 0,
                'sunrise' => isset($day['astro']['sunrise']) ? date('H:i', strtotime($day['astro']['sunrise'])) : '06:00',
                'sunset' => isset($day['astro']['sunset']) ? date('H:i', strtotime($day['astro']['sunset'])) : '18:00'
            ];
        }

        // Format hourly forecast for next 48 hours
        $hourlyForecasts = [];
        $currentTime = time();
        $count = 0;

        foreach ($forecast as $day) {
            if (empty($day['hour'])) {
                continue;
            }

            foreach ($day['hour'] as $hour) {
                // Add more robust checks
                if (empty($hour['time'])) {
                    continue; // Skip this hour if time is missing
                }

                $hourTime = strtotime($hour['time']);
                if ($hourTime === false) {
                    continue; // Skip if time format is invalid
                }

                if ($hourTime > $currentTime && $count < 48) {
                    // Make sure all required properties exist
                    if (!isset($hour['condition']) ||
                        !isset($hour['condition']['code']) ||
                        !isset($hour['is_day'])) {
                        continue;
                    }

                    $condition = $hour['condition'];
                    $isDay = $hour['is_day'];
                    $weatherIconCode = $this->getWeatherIconCode($condition['code'], $isDay);

                    // Use null coalescing operator for optional values
                    $hourlyForecasts[] = [
                        'time' => date('H:i', $hourTime),
                        'full_time' => $hour['time'],
                        'temperature' => round($hour['temp_c'] ?? 25),
                        'weather_code' => $condition['code'] ?? 1003,
                        'weather_description' => $condition['text'] ?? 'Unknown',
                        'weather_icon' => $weatherIconCode,
                        'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png',
                        'precipitation' => $hour['precip_mm'] ?? 0,
                        'precipitation_probability' => $hour['chance_of_rain'] ?? 0,
                        'wind_speed' => $hour['wind_kph'] ?? 5,
                        'humidity' => $hour['humidity'] ?? 70,
                        'visibility' => $hour['vis_km'] ?? 10
                    ];
                    $count++;
                }
            }
        }

        // If no hourly forecasts were collected, provide at least the basics
        if (empty($hourlyForecasts)) {
            $hourlyForecasts = $this->generateFallbackHourlyForecast($currentWeather, $dailyForecasts);
        }

        // Get time of day temperatures
        $timeOfDayTemps = $this->getTimeOfDayTemperatures($hourlyForecasts);

        // Get sunrise/sunset
        $sunrise = $dailyForecasts[0]['sunrise'] ?? '06:00';
        $sunset = $dailyForecasts[0]['sunset'] ?? '18:00';

        return [
            'location' => $locationName,
            'current' => $currentWeather,
            'daily' => $dailyForecasts,
            'hourly' => $hourlyForecasts,
            'time_of_day' => $timeOfDayTemps,
            'sunrise' => $sunrise,
            'sunset' => $sunset
        ];
    }

    /**
     * Generate fallback hourly forecast when data is missing
     */
    private function generateFallbackHourlyForecast($currentWeather, $dailyForecasts)
    {
        $forecasts = [];
        $currentHour = (int)date('H');
        $currentTemp = $currentWeather['temperature'] ?? 25;

        // Use daily forecast min/max temps to establish temperature range
        $maxTemp = 30;
        $minTemp = 20;

        if (!empty($dailyForecasts)) {
            $maxTemp = $dailyForecasts[0]['max_temp'] ?? 30;
            $minTemp = $dailyForecasts[0]['min_temp'] ?? 20;
        }

        // Generate 24 hours of forecast data
        for ($i = 1; $i <= 48; $i++) {
            $hourToPredict = ($currentHour + $i) % 24;
            $dayOffset = floor(($currentHour + $i) / 24);
            $dateTime = strtotime("+$dayOffset days $hourToPredict:00:00");

            // Generate temperature based on time of day
            $temp = $this->estimateTemperatureForHour($hourToPredict, $minTemp, $maxTemp);

            // Weather changes are more common in early morning and afternoon
            $weatherCode = $currentWeather['weather_code'] ?? 1003;
            $weatherDesc = $currentWeather['weather_description'] ?? 'Partly cloudy';

            // Determine if it's day or night
            $isDay = ($hourToPredict >= 6 && $hourToPredict < 18) ? 1 : 0;
            $weatherIconCode = $this->getWeatherIconCode($weatherCode, $isDay);

            $forecasts[] = [
                'time' => date('H:i', $dateTime),
                'full_time' => date('Y-m-d H:i:00', $dateTime),
                'temperature' => $temp,
                'weather_code' => $weatherCode,
                'weather_description' => $weatherDesc,
                'weather_icon' => $weatherIconCode,
                'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png',
                'precipitation' => 0,
                'precipitation_probability' => 0,
                'wind_speed' => $currentWeather['wind_speed'] ?? 5,
                'humidity' => $currentWeather['humidity'] ?? 70,
                'visibility' => $currentWeather['visibility'] ?? 10
            ];
        }

        return $forecasts;
    }

    /**
     * Estimate temperature for a given hour
     */
    private function estimateTemperatureForHour($hour, $minTemp, $maxTemp)
    {
        // Coolest at around 5-6 AM, warmest at around 2-3 PM
        if ($hour >= 0 && $hour < 6) {
            // Early morning - lowest temperatures
            return round($minTemp + ($maxTemp - $minTemp) * 0.1);
        } elseif ($hour >= 6 && $hour < 10) {
            // Morning warming
            return round($minTemp + ($maxTemp - $minTemp) * (($hour - 6) / 4 * 0.4 + 0.1));
        } elseif ($hour >= 10 && $hour < 14) {
            // Approaching peak
            return round($minTemp + ($maxTemp - $minTemp) * (($hour - 10) / 4 * 0.3 + 0.5));
        } elseif ($hour >= 14 && $hour < 18) {
            // Afternoon cooling
            return round($minTemp + ($maxTemp - $minTemp) * (0.8 - ($hour - 14) / 4 * 0.3));
        } else {
            // Evening and night cooling
            return round($minTemp + ($maxTemp - $minTemp) * (0.5 - ($hour - 18) / 6 * 0.4));
        }
    }

    /**
     * Get location data from the database
     */
    private function getLocationData($location, $type = 'province')
    {
        try {
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
                'name' => $locationModel->name ?? $location,
                'model' => $locationModel
            ];
        } catch (\Exception $e) {
            Log::error("Error getting location data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get featured locations weather (optimized)
     */
    public function getFeaturedLocationsWeather()
    {
        try {
            $cacheKey = 'featured_locations_' . date('Y-m-d');

            if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
                $cachedData = Cache::get($cacheKey);
                if ($cachedData && !empty($cachedData)) {
                    return $cachedData;
                }
            }

            $featuredProvinces = Province::whereIn('code_name', [
                'ha_noi', 'da_nang', 'quang_nam', 'ba_ria_vung_tau',
                'ho_chi_minh', 'ben_tre', 'thua_thien_hue', 'lao_cai',
                'hai_phong', 'ninh_binh', 'binh_dinh', 'khanh_hoa'
            ])->get();

            $results = [];
            foreach ($featuredProvinces as $province) {
                try {
                    $data = $this->getWeatherData($province->code_name, 'province', 'minimal');
                    if ($data && isset($data['current'])) {
                        $results[] = [
                            'location' => $data['location'] ?? $province->name,
                            'current' => $data['current']
                        ];
                    } else {
                        // Fallback if API data is unavailable
                        $results[] = [
                            'location' => $province->name,
                            'current' => [
                                'temperature' => 25,
                                'weather_code' => 1003,
                                'weather_description' => 'Partly cloudy',
                                'weather_icon' => '02d',
                                'weather_image' => '/assets/images/weather-1/02d.png',
                                'precipitation' => 0
                            ]
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Error getting weather for {$province->name}: " . $e->getMessage());
                    // Continue with next province
                }
            }

            if ((bool)setting('cache_enabled')) {
                Cache::put($cacheKey, $results, now()->addHours(6));
            }

            return $results;
        } catch (\Exception $e) {
            Log::error("Error in getFeaturedLocationsWeather: " . $e->getMessage());

            // Return default data for common locations
            return $this->getFallbackFeaturedLocations();
        }
    }

    /**
     * Provide fallback featured locations when API fails
     */
    private function getFallbackFeaturedLocations()
    {
        $defaultLocations = [
            'Hà Nội', 'Đà Nẵng', 'Hồ Chí Minh', 'Hải Phòng',
            'Huế', 'Nha Trang', 'Đà Lạt', 'Hạ Long'
        ];

        $results = [];
        foreach ($defaultLocations as $location) {
            $results[] = [
                'location' => $location,
                'current' => [
                    'temperature' => rand(20, 33),
                    'weather_code' => 1003,
                    'weather_description' => 'Partly cloudy',
                    'weather_icon' => '02d',
                    'weather_image' => '/assets/images/weather-1/02d.png',
                    'precipitation' => 0
                ]
            ];
        }

        return $results;
    }

    /**
     * Get weather for all provinces
     */
    public function getAllProvincesWeather()
    {
        try {
            $cacheKey = 'all_provinces_weather_' . date('Y-m-d');

            if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
                $cachedData = Cache::get($cacheKey);
                if ($cachedData && !empty($cachedData)) {
                    return $cachedData;
                }
            }

            $provinces = Province::all();
            $results = [];

            foreach ($provinces as $province) {
                try {
                    $data = $this->getWeatherData($province->code_name, 'province', 'basic');
                    if ($data) {
                        $results[] = [
                            'province' => $province,
                            'weather' => $data
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Error getting weather for province {$province->name}: " . $e->getMessage());
                    // Continue with next province
                }
            }

            if ((bool)setting('cache_enabled')) {
                Cache::put($cacheKey, $results, now()->addHours(3));
            }

            return $results;
        } catch (\Exception $e) {
            Log::error("Error in getAllProvincesWeather: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get weather news (static for now)
     */
    public function getWeatherNews()
    {
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
     * Get weather icon code based on WeatherAPI condition code
     */
    public function getWeatherIconCode($conditionCode, $isDay)
    {
        $timeOfDay = $isDay ? 'day' : 'night';
        if (isset($this->conditionCodeToIcon[$conditionCode])) {
            return $this->conditionCodeToIcon[$conditionCode][$timeOfDay];
        }
        return $isDay ? '03d' : '03n'; // Default to cloudy
    }

    /**
     * Calculate temperatures for different times of day
     */
    public function getTimeOfDayTemperatures($hourlyForecasts)
    {
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

        foreach ($hourlyForecasts as $hour) {
            // Add check for missing full_time or invalid format
            if (empty($hour['full_time']) || !strtotime($hour['full_time'])) {
                continue;
            }

            $hourOfDay = (int)date('G', strtotime($hour['full_time']));

            // Make sure temperature exists before adding it
            if (!isset($hour['temperature'])) {
                continue;
            }

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

        foreach ($timeOfDayTemps as $period => $data) {
            if (!empty($data['temperatures'])) {
                $timeOfDayTemps[$period]['temperature'] = max($data['temperatures']);
                $timeOfDayTemps[$period]['temperature_night'] = min($data['temperatures']);
            } else {
                // Provide default values if no temperatures available
                $timeOfDayTemps[$period]['temperature'] = 25; // Default temperature
                $timeOfDayTemps[$period]['temperature_night'] = 20; // Default night temperature
            }
            unset($timeOfDayTemps[$period]['temperatures']);
        }

        return $timeOfDayTemps;
    }

    /**
     * Get Vietnamese day name from a date
     */
    public function getDayName($dateString)
    {
        if (empty($dateString)) {
            return 'T2'; // Default to Monday if date is missing
        }

        try {
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
}
