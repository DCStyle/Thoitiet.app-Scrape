<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
                // For local development, use a sample Vietnamese IP
                $ip = '123.16.0.1'; // Hanoi example
            }

            $response = Http::get("https://ipinfo.io/{$ip}?token=3a4a3178d9a9d2");

            if ($response->failed()) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Failed to get location data'
                ], 500);
            }

            $locationData = $response->json();
            $englishCityName = $locationData['city'];
            $fallbackName = $this->findVietnameseProvince($englishCityName);

            return response()->json([
                'success' => 'true',
                'name' => $fallbackName,
                'slug' => '/' . Str::slug($fallbackName)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'message' => 'Error processing location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchHeader(Request $request)
    {
        $key = $request->input('key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://thoitiet247.vn/api/search-header?key=" . urlencode($key));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: application/json',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: https://thoitiet247.vn/',
            'Origin: https://thoitiet247.vn'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
