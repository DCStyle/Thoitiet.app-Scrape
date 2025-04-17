<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WeatherController extends Controller
{
    /**
     * Find Vietnamese province name from English name
     */
    private function findVietnameseProvince($englishName)
    {
        // Map of common English province names to Vietnamese
        $provinceMap = [
            'Hanoi' => 'Hà Nội',
            'Ho Chi Minh' => 'Hồ Chí Minh',
            'Thanh Hoa' => 'Thanh Hóa',
            'Da Nang' => 'Đà Nẵng',
            'Hai Phong' => 'Hải Phòng',
            'Can Tho' => 'Cần Thơ',
            'An Giang' => 'An Giang',
            'Bac Giang' => 'Bắc Giang',
            'Bac Kan' => 'Bắc Kạn',
            'Bac Ninh' => 'Bắc Ninh',
            'Ba Ria-Vung Tau' => 'Bà Rịa-Vũng Tàu',
            'Ben Tre' => 'Bến Tre',
            'Binh Dinh' => 'Bình Định',
            'Binh Duong' => 'Bình Dương',
            'Binh Phuoc' => 'Bình Phước',
            'Binh Thuan' => 'Bình Thuận',
            'Ca Mau' => 'Cà Mau',
            'Cao Bang' => 'Cao Bằng',
            'Dak Lak' => 'Đắk Lắk',
            'Dak Nong' => 'Đắk Nông',
            'Dien Bien' => 'Điện Biên',
            'Dong Nai' => 'Đồng Nai',
            'Dong Thap' => 'Đồng Tháp',
            'Gia Lai' => 'Gia Lai',
            'Ha Giang' => 'Hà Giang',
            'Ha Nam' => 'Hà Nam',
            'Ha Tinh' => 'Hà Tĩnh',
            'Hai Duong' => 'Hải Dương',
            'Hau Giang' => 'Hậu Giang',
            'Hoa Binh' => 'Hòa Bình',
            'Hung Yen' => 'Hưng Yên',
            'Khanh Hoa' => 'Khánh Hòa',
            'Kien Giang' => 'Kiên Giang',
            'Kon Tum' => 'Kon Tum',
            'Lai Chau' => 'Lai Châu',
            'Lam Dong' => 'Lâm Đồng',
            'Lang Son' => 'Lạng Sơn',
            'Lao Cai' => 'Lào Cai',
            'Long An' => 'Long An',
            'Nam Dinh' => 'Nam Định',
            'Nghe An' => 'Nghệ An',
            'Ninh Binh' => 'Ninh Bình',
            'Ninh Thuan' => 'Ninh Thuận',
            'Phu Tho' => 'Phú Thọ',
            'Phu Yen' => 'Phú Yên',
            'Quang Binh' => 'Quảng Bình',
            'Quang Nam' => 'Quảng Nam',
            'Quang Ngai' => 'Quảng Ngãi',
            'Quang Ninh' => 'Quảng Ninh',
            'Quang Tri' => 'Quảng Trị',
            'Soc Trang' => 'Sóc Trăng',
            'Son La' => 'Sơn La',
            'Tay Ninh' => 'Tây Ninh',
            'Thai Binh' => 'Thái Bình',
            'Thai Nguyen' => 'Thái Nguyên',
            'Thua Thien Hue' => 'Thừa Thiên Huế',
            'Tien Giang' => 'Tiền Giang',
            'Tra Vinh' => 'Trà Vinh',
            'Tuyen Quang' => 'Tuyên Quang',
            'Vinh Long' => 'Vĩnh Long',
            'Vinh Phuc' => 'Vĩnh Phúc',
            'Yen Bai' => 'Yên Bái'
        ];

        // Try to find a match in our map
        foreach ($provinceMap as $english => $vietnamese) {
            if (Str::contains(Str::lower($englishName), Str::lower($english))) {
                return $vietnamese;
            }
        }

        // If no match, return the original name
        return $englishName;
    }

    /**
     * Get location data based on IP address
     */
    public function findLocal(Request $request)
    {
        try {
            // Get visitor IP with fallbacks for proxies and load balancers
            $ip = $request->header('X-Forwarded-For') ??
                $request->header('X-Real-IP') ??
                $request->ip();

            // If multiple IPs are returned (happens with X-Forwarded-For), get the first one
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }

            // Check if IP is localhost/local development
            if ($ip == '127.0.0.1' || $ip == '::1' || substr($ip, 0, 4) == '192.' || substr($ip, 0, 3) == '10.') {
                // For local development, use a sample Vietnamese IP from environment or default
                $ip = env('VN_TEST_IP', '123.16.0.1'); // Hanoi by default
            }

            // Generate a reliable cache key
            $cacheKey = 'location_data_' . md5($ip);

            // Check if we have cached data for this IP
            if (Cache::has($cacheKey)) {
                Log::info("Using cached location data for IP: {$ip}");
                $locationData = Cache::get($cacheKey);
                $englishCityName = $locationData['city'] ?? '';
                $vietnameseName = $this->findVietnameseProvince($englishCityName);

                return response()->json([
                    'success' => 'true',
                    'name' => $vietnameseName,
                    'slug' => '/' . Str::slug($vietnameseName)
                ]);
            }

            // Get API token from environment
            $token = env('IPINFO_TOKEN', '3a4a3178d9a9d2');
            if (empty($token)) {
                Log::warning("Missing IPINFO_TOKEN in environment variables");
            }

            // Make API request with improved timeout and error handling
            $response = Http::timeout(5)
                ->retry(3, 1000) // Retry 3 times with 1 second delay between attempts
                ->get("https://ipinfo.io/{$ip}", [
                    'token' => $token
                ]);

            if ($response->failed()) {
                Log::warning("IP geolocation failed for IP: {$ip}, Status: {$response->status()}");

                return response()->json([
                    'success' => 'true', // Still return success to the user
                    'name' => 'Hà Nội', // Default location
                    'slug' => '/ha-noi'
                ]);
            }

            $locationData = $response->json();

            // Store response in cache regardless of country
            $cacheDuration = 1440; // Default 24 hours
            Cache::put($cacheKey, $locationData, now()->addMinutes($cacheDuration));

            if (isset($locationData['country']) && $locationData['country'] === 'VN') {
                // Handle Vietnamese location
                $englishCityName = $locationData['city'] ?? '';

                // Use the province mapping as fallback
                $vietnameseName = $this->findVietnameseProvince($englishCityName);

                return response()->json([
                    'success' => 'true',
                    'name' => $vietnameseName,
                    'slug' => '/' . Str::slug($vietnameseName)
                ]);
            } else {
                // Not a Vietnamese IP, provide default
                $defaultName = env('DEFAULT_LOCATION_NAME', 'Hà Nội');

                return response()->json([
                    'success' => 'true',
                    'name' => $defaultName,
                    'slug' => '/' . Str::slug($defaultName)
                ]);
            }
        } catch (\Exception $e) {
            // Log the full exception for debugging
            Log::error("Error in findLocal: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => 'false',
                'message' => 'Error processing location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchHeader(Request $request)
    {
        $key = $request->input('key');

        // First search for related province
        $province = Province::where('name', 'like', '%' . $key . '%')
            ->orWhere('name_en', 'like', '%' . $key . '%')
            ->orderBy('name')
            ->first();

        if ($province)
        {
            return view('partials.location-search-results', [
                'url' => $province->getUrl(),
                'name' => $province->name
            ]);
        }

        // Then search for related district
        $district = District::where('name', 'like', '%' . $key . '%')
            ->orWhere('name_en', 'like', '%' . $key . '%')
            ->orderBy('name')
            ->first();

        if ($district)
        {
            return view('partials.location-search-results', [
                'url' => $district->getUrl(),
                'name' => $district->name
            ]);
        }

        // Then search for related ward
        $ward = Ward::where('name', 'like', '%' . $key . '%')
            ->orWhere('name_en', 'like', '%' . $key . '%')
            ->orderBy('name')
            ->first();

        if ($ward)
        {
            return view('partials.location-search-results', [
                'url' => $ward->getUrl(),
                'name' => $ward->name
            ]);
        }

        return '';
    }
}
