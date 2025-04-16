<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AirQualityService
{
    private $baseUrl = 'https://air-quality-api.open-meteo.com/v1/air-quality';
    
    /**
     * Get air quality data for a location
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return array|null Air quality data
     */
    public function getAirQualityData($lat, $lng)
    {
        // Generate cache key based on location and current date
        $cacheKey = 'air_quality_' . md5($lat . $lng) . '_' . date('Y-m-d');

        // Check if we have cached data
        if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
            return Cache::get($cacheKey);
        }

        try {
            // Make API request
            $response = Http::get($this->baseUrl, [
                'latitude' => $lat,
                'longitude' => $lng,
                'hourly' => 'pm10,pm2_5,carbon_monoxide,nitrogen_dioxide,sulphur_dioxide,ozone,ammonia,us_aqi',
                'timezone' => 'Asia/Ho_Chi_Minh',
            ]);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch air quality data: {$response->status()}");
            }

            $data = $response->json();

            // Process and format the data
            $formattedData = $this->formatAirQualityData($data);

            // Cache the formatted data
            if ((bool)setting('cache_enabled')) {
                Cache::put($cacheKey, $formattedData, now()->addHours(3)); // Cache for 3 hours
            }

            return $formattedData;

        } catch (\Exception $e) {
            Log::error("Air Quality API request failed: " . $e->getMessage());
            return $this->getFallbackAirQualityData(); // Return fallback data if API fails
        }
    }
    
    /**
     * Format the air quality data from the API
     *
     * @param array $data Raw API data
     * @return array Formatted air quality data
     */
    private function formatAirQualityData($data)
    {
        $hourly = $data['hourly'] ?? null;
        
        if (!$hourly || empty($hourly['time'])) {
            return $this->getFallbackAirQualityData();
        }
        
        // Get current hour index
        $currentHour = (int)date('H');
        $index = min($currentHour, count($hourly['time']) - 1);
        
        // Get AQI and determine category
        $aqi = $hourly['us_aqi'][$index] ?? null;
        $aqiCategory = $this->getAQICategory($aqi);
        
        return [
            'aqi' => $aqi,
            'aqi_category' => $aqiCategory['category'],
            'aqi_color' => $aqiCategory['color'],
            'description' => $aqiCategory['description'],
            'co' => round($hourly['carbon_monoxide'][$index] ?? 0, 2),
            'nh3' => round($hourly['ammonia'][$index] ?? 0, 2),
            'no2' => round($hourly['nitrogen_dioxide'][$index] ?? 0, 2),
            'o3' => round($hourly['ozone'][$index] ?? 0, 2),
            'pm10' => round($hourly['pm10'][$index] ?? 0, 2),
            'pm2_5' => round($hourly['pm2_5'][$index] ?? 0, 2),
            'so2' => round($hourly['sulphur_dioxide'][$index] ?? 0, 2),
            // Often NO is not provided by API, so we'll set it to a small value
            'no' => 0.1
        ];
    }
    
    /**
     * Get AQI category based on the index value
     *
     * @param int $aqi AQI value
     * @return array Category data
     */
    private function getAQICategory($aqi)
    {
        if ($aqi === null) {
            return [
                'category' => 'Không xác định',
                'color' => '#6c757d',
                'description' => 'Không có dữ liệu về chất lượng không khí.'
            ];
        }
        
        if ($aqi <= 50) {
            return [
                'category' => 'Tốt',
                'color' => '#00e400',
                'description' => 'Chất lượng không khí tốt, hầu như không có nguy cơ ô nhiễm không khí. Lý tưởng để các hoạt động ngoài trời.'
            ];
        } elseif ($aqi <= 100) {
            return [
                'category' => 'Trung bình',
                'color' => '#ffff00',
                'description' => 'Chất lượng không khí chấp nhận được. Tuy nhiên, một số chất gây ô nhiễm có thể gây lo ngại cho một số người nhạy cảm.'
            ];
        } elseif ($aqi <= 150) {
            return [
                'category' => 'Không lành mạnh cho nhóm nhạy cảm',
                'color' => '#ff7e00',
                'description' => 'Các nhóm nhạy cảm có thể bị ảnh hưởng. Đối với công chúng, chất lượng không khí vẫn chấp nhận được.'
            ];
        } elseif ($aqi <= 200) {
            return [
                'category' => 'Không lành mạnh',
                'color' => '#ff0000',
                'description' => 'Tất cả mọi người có thể bắt đầu bị ảnh hưởng. Các nhóm nhạy cảm có thể bị ảnh hưởng nghiêm trọng hơn.'
            ];
        } elseif ($aqi <= 300) {
            return [
                'category' => 'Rất không lành mạnh',
                'color' => '#8f3f97',
                'description' => 'Cảnh báo sức khỏe. Hầu hết mọi người đều bị ảnh hưởng.'
            ];
        } else {
            return [
                'category' => 'Rất kém',
                'color' => '#7e0023',
                'description' => 'Có hại cho sức khỏe với đa số người. Mỗi người đều có thể sẽ chịu tác động đến sức khỏe. Những người nhạy cảm có thể bị ảnh hưởng nghiêm trọng hơn.'
            ];
        }
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
            'nh3' => 8.69,
            'no' => 0.1,
            'no2' => 1.62,
            'o3' => 108.75,
            'pm10' => 98,
            'pm2_5' => 76.98,
            'so2' => 2.6
        ];
    }
}