<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Models\Sitemap;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateSitemapsCommand extends Command
{
    protected $signature = 'sitemap:create
                            {--clear : Clear all existing sitemaps}
                            {--type= : Specific sitemap type to generate (current, hourly, tomorrow, etc.)}';
    protected $description = 'Create and populate sitemaps for weather forecasts';

    protected $siteUrl;
    protected $forecastTypes = [
        'hientai' => ['name' => 'Current', 'priority' => '0.8', 'path' => ''],
        'theogio' => ['name' => 'Hourly', 'priority' => '0.7', 'path' => 'theo-gio'],
        'ngaymai' => ['name' => 'Tomorrow', 'priority' => '0.7', 'path' => 'ngay-mai'],
        '3ngaytoi' => ['name' => '3-Day', 'priority' => '0.6', 'path' => '3-ngay-toi'],
        '5ngaytoi' => ['name' => '5-Day', 'priority' => '0.6', 'path' => '5-ngay-toi'],
        '7ngaytoi' => ['name' => '7-Day', 'priority' => '0.6', 'path' => '7-ngay-toi'],
        '10ngaytoi' => ['name' => '10-Day', 'priority' => '0.5', 'path' => '10-ngay-toi'],
        '15ngaytoi' => ['name' => '15-Day', 'priority' => '0.5', 'path' => '15-ngay-toi'],
        '20ngaytoi' => ['name' => '20-Day', 'priority' => '0.5', 'path' => '20-ngay-toi'],
        '30ngaytoi' => ['name' => '30-Day', 'priority' => '0.5', 'path' => '30-ngay-toi'],
    ];

    public function handle()
    {
        $this->siteUrl = config('app.url');
        $this->info('Starting sitemap generation process...');

        // Clear existing sitemaps if requested
        if ($this->option('clear')) {
            Sitemap::truncate();
            $this->info('Existing sitemaps cleared.');
        }

        // Determine which sitemap types to generate
        $typeOption = $this->option('type');
        $typesToGenerate = [];

        if ($typeOption) {
            // If a specific type is requested, validate it
            foreach ($this->forecastTypes as $key => $details) {
                if ($key === $typeOption || $details['name'] === $typeOption) {
                    $typesToGenerate[$key] = $details;
                    break;
                }
            }

            if (empty($typesToGenerate)) {
                $this->error("Invalid sitemap type: {$typeOption}");
                return 1;
            }
        } else {
            // Generate all types if no specific type is requested
            $typesToGenerate = $this->forecastTypes;
        }

        // Create sitemap indices
        $this->createSitemapIndices($typesToGenerate);

        // Generate URLs for each sitemap type
        $this->generateSitemapUrls($typesToGenerate);

        // Create posts sitemap if generating all types
        if (!$typeOption) {
            $this->createPostsSitemap();
        }

        $this->info('Sitemap generation completed. Total entries: ' . Sitemap::count());

        return 0;
    }

    protected function createSitemapIndices($types)
    {
        $this->info('Creating sitemap indices...');

        foreach ($types as $type => $details) {
            $this->info("Creating indices for {$details['name']} forecast...");

            for ($i = 1; $i <= 12; $i++) {
                Sitemap::updateOrCreate(
                    ['url' => "{$this->siteUrl}/{$type}{$i}.xml"],
                    [
                        'parent_path' => null,
                        'last_modified' => Carbon::now(),
                        'level' => 0,
                        'is_index' => 1,
                        'priority' => $details['priority'],
                        'changefreq' => 'daily',
                    ]
                );
            }
        }
    }

    protected function createPostsSitemap()
    {
        $this->info('Creating posts sitemap index...');

        Sitemap::updateOrCreate(
            ['url' => "{$this->siteUrl}/posts.xml"],
            [
                'parent_path' => null,
                'last_modified' => Carbon::now(),
                'level' => 0,
                'is_index' => 1,
                'priority' => '0.5',
                'changefreq' => 'daily',
            ]
        );
    }

    protected function generateSitemapUrls($types)
    {
        // Get all provinces
        $provinces = Province::all();
        $provinceCount = $provinces->count();

        $this->info("Found {$provinceCount} provinces. Starting URL generation...");

        // Count approximate total locations for progress bar
        $districtCount = District::count();
        $wardCount = Ward::count();
        $totalLocations = $provinceCount + $districtCount + $wardCount;

        // Distribute evenly across 12 sitemaps
        $chunkSize = ceil($totalLocations / 12);

        foreach ($types as $type => $details) {
            $this->info("Generating URLs for {$details['name']} forecast type ({$type})...");
            $this->processLocations($provinces, $type, $details['path'], $details['priority'], $chunkSize);
        }
    }

    protected function processLocations($provinces, $type, $pathSuffix, $priority, $chunkSize)
    {
        $currentCount = 0;
        $sitemapIndex = 1;
        $parent = "{$type}{$sitemapIndex}.xml";

        $bar = $this->output->createProgressBar(count($provinces));
        $bar->start();

        // First create all province-level URLs
        foreach ($provinces as $province) {
            $provinceSlug = $province->getSlug();
            $url = $provinceSlug;

            if (!empty($pathSuffix)) {
                $url .= "/{$pathSuffix}";
            }

            $this->createUrlEntry($url, $parent, $priority);
            $currentCount++;

            // Move to next sitemap if we've reached the chunk size
            if ($currentCount >= $chunkSize) {
                $sitemapIndex++;
                if ($sitemapIndex > 12) $sitemapIndex = 1; // Wrap around if needed
                $parent = "{$type}{$sitemapIndex}.xml";
                $currentCount = 0;
            }

            $bar->advance();
        }

        // Reset progress bar
        $bar->finish();
        $this->line("\nProcessed all provinces. Now processing districts and wards...");

        // Now process districts and wards
        $bar = $this->output->createProgressBar(count($provinces));
        $bar->start();

        foreach ($provinces as $province) {
            $provinceSlug = $province->getSlug();
            $districts = $province->districts;

            // Process all districts for this province
            foreach ($districts as $district) {
                $districtSlug = $district->getSlug();
                $url = "{$provinceSlug}/{$districtSlug}";

                if (!empty($pathSuffix)) {
                    $url .= "/{$pathSuffix}";
                }

                $this->createUrlEntry($url, $parent, $priority);
                $currentCount++;

                // Move to next sitemap if needed
                if ($currentCount >= $chunkSize) {
                    $sitemapIndex++;
                    if ($sitemapIndex > 12) $sitemapIndex = 1;
                    $parent = "{$type}{$sitemapIndex}.xml";
                    $currentCount = 0;
                }

                // Process all wards for this district
                $wards = $district->wards;
                foreach ($wards as $ward) {
                    $wardSlug = $ward->getSlug();
                    $url = "{$provinceSlug}/{$districtSlug}/{$wardSlug}";

                    if (!empty($pathSuffix)) {
                        $url .= "/{$pathSuffix}";
                    }

                    $this->createUrlEntry($url, $parent, $priority);
                    $currentCount++;

                    // Move to next sitemap if needed
                    if ($currentCount >= $chunkSize) {
                        $sitemapIndex++;
                        if ($sitemapIndex > 12) $sitemapIndex = 1;
                        $parent = "{$type}{$sitemapIndex}.xml";
                        $currentCount = 0;
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');
    }

    protected function createUrlEntry($relativeUrl, $parent, $priority)
    {
        // Ensure the URL doesn't have a leading slash
        $relativeUrl = ltrim($relativeUrl, '/');

        try {
            Sitemap::updateOrCreate(
                ['url' => "{$this->siteUrl}/{$relativeUrl}"],
                [
                    'parent_path' => $parent,
                    'last_modified' => Carbon::now(),
                    'level' => 1,
                    'is_index' => 0,
                    'priority' => $priority,
                    'changefreq' => 'daily',
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error creating sitemap URL ({$relativeUrl}): {$e->getMessage()}");
        }
    }
}
