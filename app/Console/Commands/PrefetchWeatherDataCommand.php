<?php

namespace App\Console\Commands;

use App\Models\Province;
use App\Models\District;
use App\Services\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PrefetchWeatherDataCommand extends Command
{
    protected $signature = 'weather:prefetch
                            {--provinces-only : Prefetch only provinces}
                            {--districts-only : Prefetch only districts}
                            {--batch-size=20 : Number of locations to process in a batch}
                            {--data-level=basic : Data level (minimal, basic, full)}
                            {--delay=100 : Delay between API calls in milliseconds}
                            {--skip-cached : Skip locations with fresh cache}';
    protected $description = 'Prefetch weather data for all provinces and districts';

    protected $weatherService;
    protected $successCount = 0;
    protected $failureCount = 0;
    protected $skippedCount = 0;
    protected $totalCount = 0;

    public function __construct(WeatherService $weatherService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
    }

    public function handle()
    {
        $dataLevel = $this->option('data-level');
        $batchSize = (int)$this->option('batch-size');
        $delay = (int)$this->option('delay');
        $skipCached = $this->option('skip-cached');

        $startTime = microtime(true);
        $this->info('Starting weather data prefetch...');

        // Process featured locations first (they're more likely to be accessed)
        $this->info('Prefetching featured locations weather...');
        $this->weatherService->getFeaturedLocationsWeather();

        // Determine what to prefetch based on options
        $prefetchProvinces = !$this->option('districts-only');
        $prefetchDistricts = !$this->option('provinces-only');

        // Prefetch provinces if needed
        if ($prefetchProvinces) {
            $this->info('Prefetching all provinces weather...');
            $provinces = Province::all();
            $this->totalCount += $provinces->count();

            $this->processLocations($provinces, 'province', $dataLevel, $batchSize, $delay, $skipCached);
        }

        // Prefetch districts if needed
        if ($prefetchDistricts) {
            $this->info('Prefetching all districts weather...');
            $districts = District::all();
            $this->totalCount += $districts->count();

            $this->processLocations($districts, 'district', $dataLevel, $batchSize, $delay, $skipCached);
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->info("Prefetch completed in {$duration} seconds.");
        $this->info("Success: {$this->successCount}, Failed: {$this->failureCount}, Skipped: {$this->skippedCount} out of {$this->totalCount}");

        return 0;
    }

    /**
     * Process locations in batches
     */
    protected function processLocations($locations, $type, $dataLevel, $batchSize, $delay, $skipCached)
    {
        $bar = $this->output->createProgressBar($locations->count());
        $bar->start();

        $batch = [];

        foreach ($locations as $location) {
            // Check if we should skip cached locations
            if ($skipCached) {
                $cacheKey = 'weather_' . $location->code_name . '_' . $type . '_' . $dataLevel . '_' . date('Y-m-d');
                if (Cache::has($cacheKey) && (bool)setting('cache_enabled')) {
                    $this->skippedCount++;
                    $bar->advance();
                    continue;
                }
            }

            $batch[] = $location;

            // Process batch when it reaches the batch size
            if (count($batch) >= $batchSize) {
                $this->processBatch($batch, $type, $dataLevel, $delay);
                $bar->advance(count($batch));
                $batch = [];
            }
        }

        // Process any remaining items
        if (!empty($batch)) {
            $this->processBatch($batch, $type, $dataLevel, $delay);
            $bar->advance(count($batch));
        }

        $bar->finish();
        $this->line('');
    }

    /**
     * Process a batch of locations
     */
    protected function processBatch($batch, $type, $dataLevel, $delay)
    {
        foreach ($batch as $location) {
            try {
                $result = $this->weatherService->getWeatherData($location->code_name, $type, $dataLevel);
                if ($result) {
                    $this->successCount++;
                } else {
                    $this->failureCount++;
                    Log::warning("No weather data returned for {$type} {$location->code_name}");
                }
            } catch (\Exception $e) {
                $this->failureCount++;
                Log::error("Failed to prefetch weather for {$type} {$location->code_name}: " . $e->getMessage());
            }

            // Add a delay to avoid overwhelming the API
            if ($delay > 0) {
                usleep($delay * 1000); // Convert ms to microseconds
            }
        }
    }
}
