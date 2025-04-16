<?php

namespace App\Http\Controllers;

use App\Models\Sitemap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class SitemapController extends Controller
{
    /**
     * Display the main sitemap index
     */
    public function index()
    {
        // Cache the sitemap index for 24 hours
        $sitemaps = Cache::remember('sitemap_index', 60 * 60 * 24, function () {
            return Sitemap::query()
                ->where('is_index', 1)
                ->whereNull('parent_path')
                ->orderBy('url')
                ->get();
        });

        return response()->view('sitemaps.index', [
            'sitemaps' => $sitemaps
        ])->header('Content-Type', 'text/xml');
    }

    /**
     * Show a specific sitemap file
     */
    public function show($path)
    {
        // Ensure path ends with .xml
        if (!preg_match('/\.xml$/', $path)) {
            $path .= '.xml';
        }

        // Handle special sitemaps
        if ($path === 'sitemap.xml') {
            return $this->index();
        }

        // Handle weather forecast sitemaps
        if ($this->isWeatherForecastSitemap($path)) {
            $cacheKey = 'sitemap_' . md5($path);

            $urls = Cache::remember($cacheKey, 60 * 60 * 24, function () use ($path) {
                return Sitemap::query()
                    ->where('parent_path', $path)
                    ->where('is_index', 0)
                    ->orderBy('url')
                    ->get();
            });

            return response()->view('sitemaps.urlset', [
                'urls' => $urls,
            ])->header('Content-Type', 'text/xml');
        }

        // Handle posts sitemap
        if ($path === 'posts.xml') {
            return $this->posts();
        }

        // Fallback to mirroring from source if needed
        return $this->mirrorFromSource($path);
    }

    /**
     * Handle the posts sitemap
     */
    public function posts()
    {
        $urls = Cache::remember('sitemap_posts', 60 * 60 * 24, function () {
            return Sitemap::query()
                ->where('parent_path', 'posts.xml')
                ->orderBy('last_modified', 'desc')
                ->get();
        });

        return response()->view('sitemaps.urlset', [
            'urls' => $urls,
        ])->header('Content-Type', 'text/xml');
    }

    /**
     * Check if a path is a weather forecast sitemap
     */
    protected function isWeatherForecastSitemap($path)
    {
        $patterns = [
            '/^hientai\d+\.xml$/',       // Current weather
            '/^theogio\d+\.xml$/',       // Hourly forecast
            '/^ngaymai\d+\.xml$/',       // Tomorrow forecast
            '/^\d+ngaytoi\d+\.xml$/',    // X-day forecast
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mirror a sitemap from source website when needed
     */
    protected function mirrorFromSource($path)
    {
        $cacheKey = 'sitemap_mirror_' . md5($path);

        return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($path) {
            try {
                // Mirroring from source url
                $sourceUrl = 'https://' . config('url_mappings.source_domain');
                $fullSourceUrl = $sourceUrl . '/' . $path;

                $proxyRequest = Http::timeout(300);
                $response = $proxyRequest->get($fullSourceUrl);

                if (!$response->successful()) {
                    \Log::warning("Sitemap not found on source: {$fullSourceUrl}");
                    return response('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>', 404)
                        ->header('Content-Type', 'text/xml');
                }

                $crawler = new Crawler($response->body());
                $content = $crawler->html();

                // Replace source domain with our domain
                $content = str_replace($sourceUrl, config('app.url'), $content);

                return response($content)
                    ->header('Content-Type', 'text/xml');
            } catch (\Exception $e) {
                \Log::error("Error mirroring sitemap {$path}: " . $e->getMessage());
                return response('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>', 500)
                    ->header('Content-Type', 'text/xml');
            }
        });
    }
}
