<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class CrawlSitemaps extends Command
{
    protected $signature = 'sitemap:crawl';
    protected $description = 'Crawl sitemaps from ketqua.vn';

    private string $sourceUrl = 'https://ketqua.vn';
    private array $processed = [];
    private array $excludedParentPaths = ['posts.xml']; // Add excluded parent paths here

    public function handle()
    {
        $this->info('Starting sitemap crawl...');

        try {
            // Add default sitemap
            $urls = [
                'https://ketqua.vn/pages.xml',
                'https://ketqua.vn/posts.xml',
                'https://ketqua.vn/predictions.xml',
                'https://ketqua.vn/results.xml'
            ];

            foreach ($urls as $url) {
                DB::table('sitemaps')->insert([
                    'url' => $url,
                    'is_index' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Add default sitemap with parent_path = 'results.xml'
            $urls = [
                'https://ketqua.vn/result-2005.xml',
                'https://ketqua.vn/result-2006.xml',
                'https://ketqua.vn/result-2007.xml',
                'https://ketqua.vn/result-2008.xml',
                'https://ketqua.vn/result-2009.xml',
                'https://ketqua.vn/result-2010.xml',
                'https://ketqua.vn/result-2011.xml',
                'https://ketqua.vn/result-2012.xml',
                'https://ketqua.vn/result-2013.xml',
                'https://ketqua.vn/result-2014.xml',
                'https://ketqua.vn/result-2015.xml',
                'https://ketqua.vn/result-2016.xml',
                'https://ketqua.vn/result-2017.xml',
                'https://ketqua.vn/result-2018.xml',
                'https://ketqua.vn/result-2019.xml',
                'https://ketqua.vn/result-2020.xml',
                'https://ketqua.vn/result-2021.xml',
                'https://ketqua.vn/result-2022.xml',
                'https://ketqua.vn/result-2023.xml',
                'https://ketqua.vn/result-2024.xml',
                'https://ketqua.vn/result-2025.xml',
            ];

            foreach ($urls as $url) {
                DB::table('sitemaps')->insert([
                    'url' => $url,
                    'level' => 1,
                    'parent_path' => 'results.xml',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->processSitemapFile($this->sourceUrl . '/sitemap.xml', 0, 'sitemap.xml');

            $this->info("Completed processing " . count($this->processed) . " URLs");

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error("Sitemap crawler error", ['error' => $e->getMessage()]);
        }
    }

    private function processSitemapFile(string $url, int $level, string $parentPath = null)
    {
        // Skip if parent path is in excluded list
        if (in_array($parentPath, $this->excludedParentPaths)) {
            $this->info("Skipping excluded parent path: " . $parentPath);
            return;
        }

        if (in_array($url, $this->processed)) {
            return;
        }

        $this->processed[] = $url;
        $this->info("Processing sitemap: " . $url);

        $content = $this->fetchUrl($url);
        if (!$content) {
            return;
        }

        // Try XML parsing first
        if ($this->isXmlContent($content)) {
            $this->processXmlSitemap($url, $content, $level, $parentPath);
            return;
        }

        // Fallback to HTML parsing
        $this->processHtmlSitemap($url, $content, $level, $parentPath);
    }

    private function isXmlContent($content)
    {
        return strpos(trim($content), '<?xml') === 0 ||
            strpos(trim($content), '<urlset') === 0 ||
            strpos(trim($content), '<sitemapindex') === 0;
    }

    private function processXmlSitemap(string $url, string $content, int $level, ?string $parentPath)
    {
        try {
            $xml = simplexml_load_string($content);

            if (!$xml) {
                throw new \Exception("Failed to parse XML");
            }

            // Handle sitemap index
            if ($xml->getName() === 'sitemapindex') {
                $this->storeSitemap($url, null, $level, true, null, null, $parentPath);

                foreach ($xml->sitemap as $sitemap) {
                    $loc = (string)$sitemap->loc;
                    $lastmod = (string)$sitemap->lastmod;
                    $path = basename($loc);

                    // Skip if path is in excluded list
                    if (in_array($path, $this->excludedParentPaths)) {
                        $this->info("Skipping excluded path: " . $path);
                        continue;
                    }

                    $this->processSitemapFile($loc, $level + 1, $path);
                }
            }
            // Handle URL set
            else if ($xml->getName() === 'urlset') {
                foreach ($xml->url as $urlElement) {
                    $loc = (string)$urlElement->loc;
                    $lastmod = (string)$urlElement->lastmod;
                    $priority = (string)$urlElement->priority;
                    $changefreq = (string)$urlElement->changefreq;

                    $this->storeSitemap($loc, $lastmod, $level, false, $priority, $changefreq, $parentPath);
                }
            }
        } catch (\Exception $e) {
            $this->error("XML processing error for $url: " . $e->getMessage());
            // Fallback to HTML parsing
            $this->processHtmlSitemap($url, $content, $level, $parentPath);
        }
    }

    private function processHtmlSitemap(string $url, string $content, int $level, ?string $parentPath)
    {
        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($content);
            $xpath = new DOMXPath($doc);

            // Find all links in the table
            $rows = $xpath->query("//table//tr[position()>1]");

            foreach ($rows as $row) {
                $cells = $xpath->query(".//td", $row);
                if ($cells->length > 0) {
                    $linkNode = $xpath->query(".//a", $cells->item(0))->item(0);
                    if (!$linkNode) continue;

                    $loc = $linkNode->getAttribute('href');
                    $priority = $cells->length > 1 ? str_replace('%', '', trim($cells->item(1)->textContent)) / 100 : null;
                    $changefreq = $cells->length > 2 ? trim($cells->item(2)->textContent) : null;
                    $lastmod = $cells->length > 3 ? trim($cells->item(3)->textContent) : null;

                    if (!in_array($loc, $this->processed)) {
                        $path = basename($loc);

                        // Skip if path is in excluded list
                        if (in_array($path, $this->excludedParentPaths)) {
                            $this->info("Skipping excluded path: " . $path);
                            continue;
                        }

                        $this->storeSitemap($loc, $lastmod, $level, false, $priority, $changefreq, $parentPath);

                        if (substr($loc, -4) === '.xml') {
                            $this->processSitemapFile($loc, $level + 1, $path);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("HTML processing error for $url: " . $e->getMessage());
            Log::error("HTML processing error", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function fetchUrl(string $url): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; SitemapBot/1.0)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml'
                ])
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }

            $this->error("Failed to fetch $url: " . $response->status());
            return null;
        } catch (\Exception $e) {
            $this->error("Error fetching $url: " . $e->getMessage());
            return null;
        }
    }

    private function storeSitemap(
        string $url,
        ?string $lastmod,
        int $level,
        bool $isIndex,
        ?string $priority = null,
        ?string $changefreq = null,
        ?string $parentPath = null
    ): void {
        try {
            // Skip storing if parent path is in excluded list
            if (in_array($parentPath, $this->excludedParentPaths)) {
                $this->info("Skipping storing excluded parent path: " . $parentPath);
                return;
            }

            DB::table('sitemaps')->insert([
                'url' => $url,
                'parent_path' => $parentPath,
                'last_modified' => $lastmod ? date('Y-m-d H:i:s', strtotime($lastmod)) : null,
                'level' => $level,
                'is_index' => $isIndex,
                'priority' => $priority,
                'changefreq' => $changefreq,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->line("Stored URL: $url (Parent: $parentPath)");
        } catch (\Exception $e) {
            $this->error("Failed to store sitemap: " . $e->getMessage());
            Log::error("Database error", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }
}
