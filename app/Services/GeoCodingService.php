<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoCodingService
{
    /**
     * @var string The base URL for the geocoding API
     */
    protected $baseUrl = 'https://geocode.maps.co/search';

    /**
     * @var string The current API key
     */
    protected $apiKey;

    /**
     * @var int The delay between requests in seconds
     */
    protected $requestDelay = 1;

    /**
     * @var int The number of requests made with the current API key
     */
    protected $requestsCount = 0;

    /**
     * @var int The maximum number of requests per day
     */
    protected $maxRequestsPerDay = 5000;

    /**
     * Constructor
     *
     * @param string|null $apiKey
     */
    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Set API key
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->requestsCount = 0;
        return $this;
    }

    /**
     * Get current API key
     *
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Get current request count
     *
     * @return int
     */
    public function getRequestsCount()
    {
        return $this->requestsCount;
    }

    /**
     * Reset request count
     *
     * @return $this
     */
    public function resetRequestsCount()
    {
        $this->requestsCount = 0;
        return $this;
    }

    /**
     * Check if we've reached the daily limit
     *
     * @return bool
     */
    public function hasReachedDailyLimit()
    {
        return $this->requestsCount >= $this->maxRequestsPerDay;
    }

    /**
     * Check if we're approaching the daily limit (90% of max)
     *
     * @return bool
     */
    public function isApproachingDailyLimit()
    {
        return $this->requestsCount >= ($this->maxRequestsPerDay * 0.9);
    }

    /**
     * Geocode an address
     *
     * @param string $address The address to geocode
     * @param bool $appendCountry Whether to append "Vietnam" to the address
     * @return array|null The geocoded coordinates or null if geocoding failed
     * @throws \Exception If API key is missing or daily limit reached
     */
    public function geocode($address, $appendCountry = true)
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API key is required');
        }

        if ($this->hasReachedDailyLimit()) {
            throw new \Exception('Daily API limit reached (5000 requests)');
        }

        // Add Vietnam to the address if appendCountry is true
        if ($appendCountry) {
            $address = trim($address) . ', Vietnam';
        }

        try {
            // Throttle requests to 1 per second
            sleep($this->requestDelay);

            // Make the request - FIXED: using api_key instead of key
            $response = Http::get($this->baseUrl, [
                'q' => $address,
                'api_key' => $this->apiKey, // Changed from 'key' to 'api_key'
                'format' => 'json'
            ]);

            $this->requestsCount++;

            if (!$response->successful()) {
                Log::warning("Geocoding failed for: {$address}. Response: " . $response->body());
                return null;
            }

            $data = $response->json();

            // Check if we got any results
            if (empty($data)) {
                Log::warning("No geocoding results for: {$address}");
                return null;
            }

            // Get the first result
            $result = $data[0];

            return [
                'lat' => (float) $result['lat'],
                'lng' => (float) $result['lon']
            ];

        } catch (\Exception $e) {
            Log::error("Geocoding error for {$address}: " . $e->getMessage());
            return null;
        }
    }
}
