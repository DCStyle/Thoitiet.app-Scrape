<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WeatherController extends Controller
{
    public function findLocal(Request $request)
    {
        try {
            // Get IP address (or use request IP)
            $ip = $request->ip();

            // Call ipinfo.io API
            $token = "3a4a3178d9a9d2";
            $response = Http::get("https://ipinfo.io/{$ip}?token={$token}");

            if ($response->failed()) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Failed to get location data'
                ], 500);
            }

            $locationData = $response->json();

            // Check if the location is in Vietnam
            if (isset($locationData['country']) && $locationData['country'] === 'VN') {
                return response()->json([
                    'success' => 'true',
                    'name' => $locationData['city'],
                    'slug' => '/' . Str::slug($locationData['city'])
                ]);
            }

            // Default fallback for non-Vietnamese locations
            return response()->json([
                'success' => 'true',
                'name' => 'Hà Nội', // Default to capital
                'slug' => '/ha-noi'
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
