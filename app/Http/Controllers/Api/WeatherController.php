<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function findLocal()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://thoitiet247.vn/api/find-local");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Set headers to mimic a browser request
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
