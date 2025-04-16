<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use App\Services\GeoCodingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GeocodeLocationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocode:locations
                            {--api-key= : API key for geocode.maps.co}
                            {--level=all : Level to geocode (provinces, districts, wards, all)}
                            {--batch-size=100 : Number of locations to process in one batch}
                            {--sleep=1 : Seconds to sleep between requests}
                            {--resume : Resume from last processed location}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Geocode all provinces, districts, and wards in Vietnam';

    /**
     * The geocoding service
     *
     * @var \App\Services\GeoCodingService
     */
    protected $geoService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get command options
        $apiKey = $this->option('api-key');
        $level = $this->option('level');
        $batchSize = (int)$this->option('batch-size');
        $sleepTime = (int)$this->option('sleep');
        $resume = $this->option('resume');

        if (empty($apiKey)) {
            $apiKey = $this->ask('Please provide your geocode.maps.co API key');
        }

        // Initialize geocoding service
        $this->geoService = new GeoCodingService($apiKey);

        // Process based on level
        try {
            if ($level === 'all' || $level === 'provinces') {
                $this->geocodeProvinces($resume, $batchSize, $sleepTime);
            }

            if ($level === 'all' || $level === 'districts') {
                $this->geocodeDistricts($resume, $batchSize, $sleepTime);
            }

            if ($level === 'all' || $level === 'wards') {
                $this->geocodeWards($resume, $batchSize, $sleepTime);
            }

            $this->info('Geocoding completed!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Geocode provinces
     *
     * @param bool $resume Resume from last position
     * @param int $batchSize Batch size
     * @param int $sleepTime Sleep time between requests
     * @return void
     */
    protected function geocodeProvinces($resume = false, $batchSize = 100, $sleepTime = 1)
    {
        $this->info('Starting to geocode provinces...');

        // Get the last processed province ID if resuming
        $lastProcessedId = $resume ? Cache::get('geocode_last_province_id') : null;

        // Get provinces that don't have coordinates yet
        $query = Province::query();

        if ($lastProcessedId) {
            $this->info("Resuming from province ID: {$lastProcessedId}");
            $query->where('code', '>', $lastProcessedId);
        } else {
            $query->where(function($q) {
                $q->whereNull('lat')->orWhereNull('lng');
            });
        }

        $totalProvinces = $query->count();
        $this->info("Found {$totalProvinces} provinces to geocode");

        if ($totalProvinces === 0) {
            $this->info('No provinces to geocode. All provinces have coordinates.');
            return;
        }

        $provinces = $query->orderBy('code')->take($batchSize)->get();
        $bar = $this->output->createProgressBar(count($provinces));
        $bar->start();

        foreach ($provinces as $province) {
            try {
                // Check if we're approaching the daily limit
                if ($this->geoService->isApproachingDailyLimit()) {
                    $this->warn("Approaching daily API limit! (" . $this->geoService->getRequestsCount() . " requests made)");
                    if ($this->confirm('Do you want to provide a new API key to continue?')) {
                        $newApiKey = $this->ask('Please provide a new API key');
                        $this->geoService->setApiKey($newApiKey);
                        $this->info("API key updated. Continuing geocoding...");
                    } else {
                        // Save the last processed ID for resuming later
                        Cache::put('geocode_last_province_id', $province->code, now()->addDays(30));
                        $this->info("Operation paused. Run the command with --resume to continue later.");
                        break;
                    }
                }

                // Create geocoding address
                $addressToGeocode = $province->full_name;

                // Get coordinates
                $coordinates = $this->geoService->geocode($addressToGeocode);

                if ($coordinates) {
                    $province->lat = $coordinates['lat'];
                    $province->lng = $coordinates['lng'];
                    $province->save();

                    // Save the last processed ID
                    Cache::put('geocode_last_province_id', $province->code, now()->addDays(30));

                    $this->info("\nGeocoded {$province->name}: " . json_encode($coordinates), 'v');
                } else {
                    $this->warn("\nCouldn't get coordinates for province: {$province->name}");
                }

                // Sleep between requests if specified
                if ($sleepTime > 0) {
                    sleep($sleepTime);
                }

                $bar->advance();

            } catch (\Exception $e) {
                $this->error("\nError processing province {$province->name}: " . $e->getMessage());

                // Save the last processed ID for resuming later
                Cache::put('geocode_last_province_id', $province->code, now()->addDays(30));

                if ($this->confirm('Do you want to continue with the next province?')) {
                    continue;
                } else {
                    $this->info("Operation paused. Run the command with --resume to continue later.");
                    break;
                }
            }
        }

        $bar->finish();
        $this->info("\nProvinces geocoding batch completed.");

        // Check if there are more provinces to process
        if (count($provinces) > 0) {
            $remainingProvinces = Province::where('code', '>', $provinces->last()->code)
                ->where(function($q) {
                    $q->whereNull('lat')->orWhereNull('lng');
                })
                ->count();

            if ($remainingProvinces > 0) {
                $this->info("{$remainingProvinces} provinces remaining.");
                if ($this->confirm('Do you want to process another batch?')) {
                    $this->geocodeProvinces(true, $batchSize, $sleepTime);
                } else {
                    $this->info("You can resume later with: php artisan geocode:locations --level=provinces --resume");
                }
            } else {
                $this->info("All provinces have been geocoded!");
                Cache::forget('geocode_last_province_id');
            }
        }
    }

    /**
     * Geocode districts
     *
     * @param bool $resume Resume from last position
     * @param int $batchSize Batch size
     * @param int $sleepTime Sleep time between requests
     * @return void
     */
    protected function geocodeDistricts($resume = false, $batchSize = 100, $sleepTime = 1)
    {
        $this->info('Starting to geocode districts...');

        // Get the last processed district ID if resuming
        $lastProcessedId = $resume ? Cache::get('geocode_last_district_id') : null;

        // Get districts that don't have coordinates yet
        $query = District::query();

        if ($lastProcessedId) {
            $this->info("Resuming from district ID: {$lastProcessedId}");
            $query->where('code', '>', $lastProcessedId);
        } else {
            $query->where(function($q) {
                $q->whereNull('lat')->orWhereNull('lng');
            });
        }

        $totalDistricts = $query->count();
        $this->info("Found {$totalDistricts} districts to geocode");

        if ($totalDistricts === 0) {
            $this->info('No districts to geocode. All districts have coordinates.');
            return;
        }

        $districts = $query->with('province')->orderBy('code')->take($batchSize)->get();
        $bar = $this->output->createProgressBar(count($districts));
        $bar->start();

        foreach ($districts as $district) {
            try {
                // Check if we're approaching the daily limit
                if ($this->geoService->isApproachingDailyLimit()) {
                    $this->warn("Approaching daily API limit! (" . $this->geoService->getRequestsCount() . " requests made)");
                    if ($this->confirm('Do you want to provide a new API key to continue?')) {
                        $newApiKey = $this->ask('Please provide a new API key');
                        $this->geoService->setApiKey($newApiKey);
                        $this->info("API key updated. Continuing geocoding...");
                    } else {
                        // Save the last processed ID for resuming later
                        Cache::put('geocode_last_district_id', $district->code, now()->addDays(30));
                        $this->info("Operation paused. Run the command with --resume to continue later.");
                        break;
                    }
                }

                // Create geocoding address with province information
                $addressToGeocode = $district->full_name;

                if ($district->province) {
                    $addressToGeocode .= ', ' . $district->province->name;
                }

                // Get coordinates
                $coordinates = $this->geoService->geocode($addressToGeocode);

                if ($coordinates) {
                    $district->lat = $coordinates['lat'];
                    $district->lng = $coordinates['lng'];
                    $district->save();

                    // Save the last processed ID
                    Cache::put('geocode_last_district_id', $district->code, now()->addDays(30));

                    $this->info("\nGeocoded {$district->name}: " . json_encode($coordinates), 'v');
                } else {
                    $this->warn("\nCouldn't get coordinates for district: {$district->name}");
                }

                // Sleep between requests if specified
                if ($sleepTime > 0) {
                    sleep($sleepTime);
                }

                $bar->advance();

            } catch (\Exception $e) {
                $this->error("\nError processing district {$district->name}: " . $e->getMessage());

                // Save the last processed ID for resuming later
                Cache::put('geocode_last_district_id', $district->code, now()->addDays(30));

                if ($this->confirm('Do you want to continue with the next district?')) {
                    continue;
                } else {
                    $this->info("Operation paused. Run the command with --resume to continue later.");
                    break;
                }
            }
        }

        $bar->finish();
        $this->info("\nDistricts geocoding batch completed.");

        // Check if there are more districts to process
        if (count($districts) > 0) {
            $remainingDistricts = District::where('code', '>', $districts->last()->code)
                ->where(function($q) {
                    $q->whereNull('lat')->orWhereNull('lng');
                })
                ->count();

            if ($remainingDistricts > 0) {
                $this->info("{$remainingDistricts} districts remaining.");
                if ($this->confirm('Do you want to process another batch?')) {
                    $this->geocodeDistricts(true, $batchSize, $sleepTime);
                } else {
                    $this->info("You can resume later with: php artisan geocode:locations --level=districts --resume");
                }
            } else {
                $this->info("All districts have been geocoded!");
                Cache::forget('geocode_last_district_id');
            }
        }
    }

    /**
     * Geocode wards
     *
     * @param bool $resume Resume from last position
     * @param int $batchSize Batch size
     * @param int $sleepTime Sleep time between requests
     * @return void
     */
    protected function geocodeWards($resume = false, $batchSize = 100, $sleepTime = 1)
    {
        $this->info('Starting to geocode wards...');

        // Get the last processed ward ID if resuming
        $lastProcessedId = $resume ? Cache::get('geocode_last_ward_id') : null;

        // Get wards that don't have coordinates yet
        $query = Ward::query();

        if ($lastProcessedId) {
            $this->info("Resuming from ward ID: {$lastProcessedId}");
            $query->where('code', '>', $lastProcessedId);
        } else {
            $query->where(function($q) {
                $q->whereNull('lat')->orWhereNull('lng');
            });
        }

        $totalWards = $query->count();
        $this->info("Found {$totalWards} wards to geocode");

        if ($totalWards === 0) {
            $this->info('No wards to geocode. All wards have coordinates.');
            return;
        }

        $wards = $query->with(['district', 'district.province'])->orderBy('code')->take($batchSize)->get();
        $bar = $this->output->createProgressBar(count($wards));
        $bar->start();

        foreach ($wards as $ward) {
            try {
                // Check if we're approaching the daily limit
                if ($this->geoService->isApproachingDailyLimit()) {
                    $this->warn("Approaching daily API limit! (" . $this->geoService->getRequestsCount() . " requests made)");
                    if ($this->confirm('Do you want to provide a new API key to continue?')) {
                        $newApiKey = $this->ask('Please provide a new API key');
                        $this->geoService->setApiKey($newApiKey);
                        $this->info("API key updated. Continuing geocoding...");
                    } else {
                        // Save the last processed ID for resuming later
                        Cache::put('geocode_last_ward_id', $ward->code, now()->addDays(30));
                        $this->info("Operation paused. Run the command with --resume to continue later.");
                        break;
                    }
                }

                // Create geocoding address with district and province information
                $addressToGeocode = $ward->full_name;

                if ($ward->district) {
                    $addressToGeocode .= ', ' . $ward->district->name;

                    if ($ward->district->province) {
                        $addressToGeocode .= ', ' . $ward->district->province->name;
                    }
                }

                // Get coordinates
                $coordinates = $this->geoService->geocode($addressToGeocode);

                if ($coordinates) {
                    $ward->lat = $coordinates['lat'];
                    $ward->lng = $coordinates['lng'];
                    $ward->save();

                    // Save the last processed ID
                    Cache::put('geocode_last_ward_id', $ward->code, now()->addDays(30));

                    $this->info("\nGeocoded {$ward->name}: " . json_encode($coordinates), 'v');
                } else {
                    $this->warn("\nCouldn't get coordinates for ward: {$ward->name}");
                }

                // Sleep between requests if specified
                if ($sleepTime > 0) {
                    sleep($sleepTime);
                }

                $bar->advance();

            } catch (\Exception $e) {
                $this->error("\nError processing ward {$ward->name}: " . $e->getMessage());

                // Save the last processed ID for resuming later
                Cache::put('geocode_last_ward_id', $ward->code, now()->addDays(30));

                if ($this->confirm('Do you want to continue with the next ward?')) {
                    continue;
                } else {
                    $this->info("Operation paused. Run the command with --resume to continue later.");
                    break;
                }
            }
        }

        $bar->finish();
        $this->info("\nWards geocoding batch completed.");

        // Check if there are more wards to process
        if (count($wards) > 0) {
            $remainingWards = Ward::where('code', '>', $wards->last()->code)
                ->where(function($q) {
                    $q->whereNull('lat')->orWhereNull('lng');
                })
                ->count();

            if ($remainingWards > 0) {
                $this->info("{$remainingWards} wards remaining.");
                if ($this->confirm('Do you want to process another batch?')) {
                    $this->geocodeWards(true, $batchSize, $sleepTime);
                } else {
                    $this->info("You can resume later with: php artisan geocode:locations --level=wards --resume");
                }
            } else {
                $this->info("All wards have been geocoded!");
                Cache::forget('geocode_last_ward_id');
            }
        }
    }
}
