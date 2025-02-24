<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public function index()
    {
        $mainSitemaps = DB::table('sitemaps')
            ->whereNull('parent_path')
            ->get()
            ->map(fn($item) => $this->formatUrl($item));

        return response()->view('sitemaps.urlset', [
            'urls' => $mainSitemaps
        ])->header('Content-Type', 'text/xml');
    }

    public function show($path)
    {
        if (!preg_match('/\.xml$/', $path)) {
            $path .= '.xml';
        }

        $config = config('url_mappings');
        $sourceDomain = $config['source_domain'];

        if ($path === 'results.xml') {
            $urls = DB::table('sitemaps')
                ->where('parent_path', 'results.xml')
                // Where url not ending with 'results.xml'
                ->where('url', 'not like', '%results.xml')
                ->get()
                ->map(fn($item) => $this->formatSitemapUrl($item));

            return response()->view('sitemaps.sitemapindex', [
                'urls' => $urls
            ])->header('Content-Type', 'text/xml');
        }

        if (preg_match('/result-(\d{4})\.xml/', $path, $matches)) {
            $query = DB::table('sitemaps')
                ->where('parent_path', $path)
                ->orderBy('url');

            $urls = $query->get()->map(fn($item) => $this->formatUrl($item));

            return response()->view('sitemaps.urlset', [
                'urls' => $urls
            ])->header('Content-Type', 'text/xml');
        }

        $urls = DB::table('sitemaps')
            ->where('parent_path', $path)
            ->orderBy('url')
            ->get()
            ->map(fn($item) => $this->formatUrl($item));

        return response()->view('sitemaps.urlset', [
            'urls' => $urls
        ])->header('Content-Type', 'text/xml');
    }

    private function formatUrl($item)
    {
        $sourceDomain = config('url_mappings.source_domain');
        return [
            'loc' => str_replace($sourceDomain, request()->getHost(), $item->url),
            'lastmod' => Carbon::parse($item->last_modified)
                ->setTimezone('Asia/Ho_Chi_Minh')
                ->toW3cString(),
            'changefreq' => $item->changefreq ?? 'daily',
            'priority' => $item->priority ?? '0.5'
        ];
    }

    private function formatSitemapUrl($item)
    {
        $sourceDomain = config('url_mappings.source_domain');
        return [
            'loc' => str_replace($sourceDomain, request()->getHost(), $item->url),
            'lastmod' => Carbon::parse($item->last_modified)
                ->setTimezone('Asia/Ho_Chi_Minh')
                ->toW3cString()
        ];
    }
}
