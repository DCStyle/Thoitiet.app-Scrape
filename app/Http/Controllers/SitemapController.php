<?php

namespace App\Http\Controllers;

use App\Models\Sitemap;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemaps = Sitemap::query()
            ->whereNull('parent_path')
            ->get();

        return response()->view('sitemaps.index', [
            'sitemaps' => $sitemaps
        ])->header('Content-Type', 'text/xml');
    }

    public function posts()
    {
        $urls = Sitemap::query()
            ->where('parent_path', 'posts.xml')
            ->orderBy('last_modified', 'desc')
            ->get();

        return response()->view('sitemaps.urlset', [
            'urls' => $urls,
        ])->header('Content-Type', 'text/xml');
    }

    public function show($path)
    {
        if (!preg_match('/\.xml$/', $path)) {
            $path .= '.xml';
        }

        if ($path === 'sitemap.xml') {
            return $this->index();
        } elseif ($path == 'posts.xml') {
            return $this->posts();
        } else {
            // Mirroring from source url
            $sourceUrl = 'https://' . config('url_mappings.source_domain');
            $fullSourceUrl = $sourceUrl . '/' . $path;

            $proxyUrl = 'https://ketqua5s.com';
            $encodedUrl = base64_encode(rtrim($fullSourceUrl, '/'));

            $proxyRequest = Http::timeout(300);
            $response = $proxyRequest->get($proxyUrl . '?url=' . $encodedUrl);

            $crawler = new Crawler($response->body());
            $content = $crawler->html();

            // Replace source domain with our domain
            $content = str_replace($sourceUrl, config('app.url'), $content);

            return response()->view('sitemaps.scrape', [
                'content' => $content
            ])->header('Content-Type', 'text/xml');
        }
    }
}
