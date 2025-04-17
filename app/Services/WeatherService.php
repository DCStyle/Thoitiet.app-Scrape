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
                    $q = $locationModel->full_name;
                } elseif ($type === 'district') {
                    $q = $locationModel->full_name . ', ' . $locationModel->province->name;
                } elseif ($type === 'ward') {
                    $q = $locationModel->full_name . ', ' . $locationModel->district->name . ', ' . $locationModel->district->province->name;
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
            return Cache::get($cacheKey);
        }

        try {
            $apiKey = config('services.weatherapi.key');
            $endpoint = '/forecast.json';

            $params = [
                'key' => $apiKey,
                'q' => $q,
                'days' => ($dataLevel === 'minimal') ? 1 : 7, // Free plan: max 3 days
                'aqi' => 'no',
                'alerts' => 'no',
            ];

            $response = Http::get($this->baseUrl . $endpoint, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch weather data: ' . $response->status());
            }

            $data = $response->json();

            // Check for API error response
            if (isset($data['error'])) {
                Log::warning("WeatherAPI error: " . $data['error']['message']);
                return null;
            }

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

            // Cache the data
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
        $current = $data['current'] ?? null;

        if (!$current) {
            return null;
        }

        $condition = $current['condition'];
        $isDay = $current['is_day'];
        $weatherIconCode = $this->getWeatherIconCode($condition['code'], $isDay);

        return [
            'location' => $locationName,
            'current' => [
                'temperature' => round($current['temp_c']),
                'weather_code' => $condition['code'],
                'weather_description' => $condition['text'],
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
            $condition = $day['day']['condition'];
            $weatherIconCode = $this->getWeatherIconCode($condition['code'], true); // Daytime for daily forecast

            $dailyForecasts[] = [
                'date' => date('d/m', strtotime($day['date'])),
                'day_name' => $this->getDayName($day['date']),
                'max_temp' => round($day['day']['maxtemp_c']),
                'min_temp' => round($day['day']['mintemp_c']),
                'weather_code' => $condition['code'],
                'weather_description' => $condition['text'],
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

        if (!$current || !$forecast) {
            return null;
        }

        $condition = $current['condition'];
        $isDay = $current['is_day'];
        $weatherIconCode = $this->getWeatherIconCode($condition['code'], $isDay);

        // Format current weather
        $currentWeather = [
            'temperature' => round($current['temp_c']),
            'feels_like' => round($current['feelslike_c']),
            'weather_code' => $condition['code'],
            'weather_description' => $condition['text'],
            'weather_icon' => $weatherIconCode,
            'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png',
            'humidity' => $current['humidity'],
            'wind_speed' => number_format($current['wind_kph'], 2),
            'precipitation' => $current['precip_mm'],
            'precipitation_probability' => $forecast[0]['hour'][date('H')]['chance_of_rain'], // Use hourly for current
            'visibility' => $current['vis_km']
        ];

        // Format daily forecast
        $dailyForecasts = [];
        foreach ($forecast as $day) {
            $condition = $day['day']['condition'];
            $weatherIconCode = $this->getWeatherIconCode($condition['code'], true); // Daytime for daily

            $dailyForecasts[] = [
                'date' => date('d/m', strtotime($day['date'])),
                'full_date' => date('Y-m-d', strtotime($day['date'])),
                'day_name' => $this->getDayName($day['date']),
                'max_temp' => round($day['day']['maxtemp_c']),
                'min_temp' => round($day['day']['mintemp_c']),
                'weather_code' => $condition['code'],
                'weather_description' => $condition['text'],
                'weather_icon' => $weatherIconCode,
                'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png',
                'precipitation_sum' => $day['day']['totalprecip_mm'],
                'precipitation_probability' => $day['day']['daily_chance_of_rain'],
                'sunrise' => date('H:i', strtotime($day['astro']['sunrise'])),
                'sunset' => date('H:i', strtotime($day['astro']['sunset']))
            ];
        }

        // Format hourly forecast for next 48 hours
        $hourlyForecasts = [];
        $currentTime = time();
        $count = 0;

        foreach ($forecast as $day) {
            foreach ($day['hour'] as $hour) {
                $hourTime = strtotime($hour['time']);
                if ($hourTime > $currentTime && $count < 48) {
                    $condition = $hour['condition'];
                    $isDay = $hour['is_day'];
                    $weatherIconCode = $this->getWeatherIconCode($condition['code'], $isDay);

                    $hourlyForecasts[] = [
                        'time' => date('H:i', $hourTime),
                        'full_time' => $hour['time'],
                        'temperature' => round($hour['temp_c']),
                        'weather_code' => $condition['code'],
                        'weather_description' => $condition['text'],
                        'weather_icon' => $weatherIconCode,
                        'weather_image' => '/assets/images/weather-1/' . $weatherIconCode . '.png',
                        'precipitation' => $hour['precip_mm'],
                        'precipitation_probability' => $hour['chance_of_rain'],
                        'wind_speed' => $hour['wind_kph'],
                        'humidity' => $hour['humidity'],
                        'visibility' => $hour['vis_km']
                    ];
                    $count++;
                }
            }
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
     * Get location data from the database
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
            $data = $this->getWeatherData($province->code_name, 'province', 'minimal');
            if ($data) {
                $results[] = [
                    'location' => $data['location'],
                    'current' => $data['current']
                ];
            }
        }

        if ((bool)setting('cache_enabled')) {
            Cache::put($cacheKey, $results, now()->addHours(6));
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
