<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AirQualityService
{
    private $baseUrl = 'http://api.weatherapi.com/v1';

    /**
     * Get air quality data for a location
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return array|null Air quality data
     */
    public function getAirQualityData($lat, $lng)
    {
        $cacheKey = 'air_quality_' . md5($lat . $lng) . '_' . date('Y-m-d');

        if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
            return Cache::get($cacheKey);
        }

        try {
            $apiKey = config('services.weatherapi.key');
            $response = Http::get($this->baseUrl . '/forecast.json', [
                'key' => $apiKey,
                'q' => $lat . ',' . $lng,
                'days' => 1,
                'aqi' => 'yes',
                'alerts' => 'no',
                'lang' => 'vi'
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch air quality data: ' . $response->status());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::warning("WeatherAPI error: " . $data['error']['message']);
                return $this->getFallbackAirQualityData();
            }

            $airQuality = $data['current']['air_quality'] ?? null;

            if (!$airQuality) {
                return $this->getFallbackAirQualityData();
            }

            $formattedData = $this->formatAirQualityData($airQuality);

            if ((bool)setting('cache_enabled')) {
                Cache::put($cacheKey, $formattedData, now()->addHours(3));
            }

            return $formattedData;
        } catch (\Exception $e) {
            Log::error("Air Quality API request failed: " . $e->getMessage());
            return $this->getFallbackAirQualityData();
        }
    }

    /**
     * Format the air quality data from the API
     *
     * @param array $airQuality Raw air quality data
     * @return array Formatted air quality data
     */
    private function formatAirQualityData($airQuality)
    {
        $index = $airQuality['us-epa-index'] ?? null;
        $aqiCategory = $this->getAQICategory($index);
        $aqi = $this->getRepresentativeAQI($index);

        return [
            'aqi' => $aqi,
            'aqi_category' => $aqiCategory['category'],
            'aqi_color' => $aqiCategory['color'],
            'description' => $aqiCategory['description'],
            'co' => round($airQuality['co'] ?? 0, 2),
            'nh3' => null, // Not provided by WeatherAPI.com
            'no' => null, // Not provided by WeatherAPI.com
            'no2' => round($airQuality['no2'] ?? 0, 2),
            'o3' => round($airQuality['o3'] ?? 0, 2),
            'pm10' => round($airQuality['pm10'] ?? 0, 2),
            'pm2_5' => round($airQuality['pm2_5'] ?? 0, 2),
            'so2' => round($airQuality['so2'] ?? 0, 2),
        ];
    }

    /**
     * Get AQI category based on the US EPA index
     *
     * @param int $index US EPA index (1-6)
     * @return array Category data
     */
    private function getAQICategory($index)
    {
        $categories = [
            1 => [
                'category' => 'Tốt',
                'color' => '#00e400',
                'description' => 'Chất lượng không khí tốt, hầu như không có nguy cơ ô nhiễm không khí. Lý tưởng để các hoạt động ngoài trời.'
            ],
            2 => [
                'category' => 'Trung bình',
                'color' => '#ffff00',
                'description' => 'Chất lượng không khí chấp nhận được. Tuy nhiên, một số chất gây ô nhiễm có thể gây lo ngại cho một số người nhạy cảm.'
            ],
            3 => [
                'category' => 'Không lành mạnh cho nhóm nhạy cảm',
                'color' => '#ff7e00',
                'description' => 'Các nhóm nhạy cảm có thể bị ảnh hưởng. Đối với công chúng, chất lượng không khí vẫn chấp nhận được.'
            ],
            4 => [
                'category' => 'Không lành mạnh',
                'color' => '#ff0000',
                'description' => 'Tất cả mọi người có thể bắt đầu bị ảnh hưởng. Các nhóm nhạy cảm có thể bị ảnh hưởng nghiêm trọng hơn.'
            ],
            5 => [
                'category' => 'Rất không lành mạnh',
                'color' => '#8f3f97',
                'description' => 'Cảnh báo sức khỏe. Hầu hết mọi người đều bị ảnh hưởng.'
            ],
            6 => [
                'category' => 'Nguy hiểm',
                'color' => '#7e0023',
                'description' => 'Cảnh báo sức khỏe khẩn cấp. Toàn bộ dân số có thể bị ảnh hưởng nghiêm trọng.'
            ],
        ];

        return $categories[$index] ?? [
            'category' => 'Không xác định',
            'color' => '#6c757d',
            'description' => 'Không có dữ liệu về chất lượng không khí.'
        ];
    }

    /**
     * Get a representative AQI value based on the US EPA index
     *
     * @param int $index US EPA index (1-6)
     * @return int|null Representative AQI value
     */
    private function getRepresentativeAQI($index)
    {
        $representativeValues = [
            1 => 25,  // Good
            2 => 75,  // Moderate
            3 => 125, // Unhealthy for Sensitive Groups
            4 => 175, // Unhealthy
            5 => 250, // Very Unhealthy
            6 => 400, // Hazardous
        ];

        return $representativeValues[$index] ?? null;
    }

    /**
     * Get fallback air quality data when API fails
     *
     * @return array Fallback data
     */
    private function getFallbackAirQualityData()
    {
        return [
            'aqi' => 151,
            'aqi_category' => 'Rất kém',
            'aqi_color' => '#7e0023',
            'description' => 'Có hại cho sức khỏe với đa số người. Mỗi người đều có thể sẽ chịu tác động đến sức khỏe. Những người nhạy cảm có thể bị ảnh hưởng nghiêm trọng hơn.',
            'co' => 333.79,
            'nh3' => null,
            'no' => null,
            'no2' => 1.62,
            'o3' => 108.75,
            'pm10' => 98,
            'pm2_5' => 76.98,
            'so2' => 2.6
        ];
    }
}
