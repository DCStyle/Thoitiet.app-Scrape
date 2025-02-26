<?php

namespace App\Services;

use App\Exceptions\TooManyRequestsException;
use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\DomCrawler\Crawler;

class ContentMirrorService
{
    private $urlMappings;
    private $sourceDomain;

    private $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0'
    ];

    public function __construct()
    {
        $config = config('url_mappings');
        $this->urlMappings = collect($config['paths']);
        $this->sourceDomain = $config['source_domain'];
    }

    public function makeRequest(string $path, array $params, string $template, string $selector, string $sourceUrl, string $method = 'POST'): ?array
    {
        if (!$this->checkRateLimit()) {
            throw new TooManyRequestsException();
        }

        $cacheKey = $this->generateCacheKey($path, $params);

        if (!(bool)setting('cache_enabled')) {
            return $this->fetchAndProcessContent($path, $params, $template, $selector, $sourceUrl, $method);
        }

        return Cache::remember($cacheKey, setting('cache_lifetime', 5) * 60, function () use ($path, $params, $template, $selector, $sourceUrl, $method) {
            return $this->fetchAndProcessContent($path, $params, $template, $selector, $sourceUrl, $method);
        });
    }

    private function fetchAndProcessContent(string $path, array $params, string $template, string $selector, string $sourceUrl, string $method): ?array
    {
        try {
            $response = $this->sendRequest($sourceUrl . '/' . $path, $params, $method);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch content: {$response->status()}");
            }

            $crawler = new Crawler($response->body());
            $metadata = $this->extractMetadata($crawler);
            $content = $this->extractContent($response->body(), $selector);

            return [
                'content' => $this->processContent($content),
                'template' => $template,
                'metadata' => $metadata
            ];
        } catch (\Exception $e) {
            Log::error("Request failed for {$path}: " . $e->getMessage());
            return null;
        }
    }

    private function extractMetadata(Crawler $crawler): array
    {
        $metadata = [
            'title' => setting('site_name'),
            'description' => setting('site_description'),
            'keywords' => setting('site_keywords'),
            'robots' => 'noindex,nofollow',
            'og_tags' => [
                'og:title' => setting('site_name'),
                'og:description' => setting('site_description'),
                'og:site_name' => setting('site_name'),
                'og:locale' => 'vi_VN'
            ],
            'twitter_tags' => [
                'twitter:card' => 'summary_large_image',
                'twitter:creator' => 'Kết Quả Xổ Số'
            ],
            'canonical' => url()->current()
        ];

        // Extract title
        $titleNode = $crawler->filter('title');
        if ($titleNode->count() > 0) {
            $metadata['title'] = trim($titleNode->text());
        }

        // Extract meta tags
        $crawler->filter('meta')->each(function (Crawler $node) use (&$metadata) {
            $name = $node->attr('name') ?? $node->attr('property');
            $content = $node->attr('content');

            if (!$name || !$content) return;

            switch (strtolower($name)) {
                case 'description':
                    $metadata['description'] = $content;
                    break;
                case 'keywords':
                    $metadata['keywords'] = $content;
                    break;
                case 'robots':
                    $metadata['robots'] = $content;
                    break;
                default:
                    if (str_starts_with($name, 'og:')) {
                        // Do not take these tags
                        if (!in_array($name, ['og:image', 'og:url', 'og:image:url'])) {
                            $metadata['og_tags'][$name] = $content;
                        }
                    } elseif (str_starts_with($name, 'twitter:')) {
                        // Do not take these tags
                        if (!in_array($name, ['twitter:image'])) {
                            $metadata['twitter_tags'][$name] = $content;
                        }
                    }
            }
        });

        // Extract canonical URL
        $canonical = $crawler->filter('link[rel="canonical"]');
        if ($canonical->count() > 0) {
            // Replace source domain with our domain
            $canonical->each(function (Crawler $node) {
                $node->getNode(0)->setAttribute('href', str_replace($this->sourceDomain, env('APP_URL'), $node->attr('href')));
            });
        }

        return $metadata;
    }

    public function getContent(string $path): ?array
    {
        $customMapping = $this->urlMappings->first(function ($mapping) use ($path) {
            return isset($mapping['our_paths'][$path]);
        });

        if ($customMapping) {
            return [
                'content' => null,
                'template' => $customMapping['our_paths'][$path]
            ];
        }

        $defaultConfig = config('url_mappings.default_scrape');
        return $this->makeRequest(
            $path,
            request()->all(),
            $defaultConfig['template'],
            $defaultConfig['main_selector'],
            $defaultConfig['source_url'],
            request()->method()
        );
    }

    private function checkRateLimit(): bool
    {
        return RateLimiter::attempt('scraping', 60, function() { return true; });
    }

    private function generateCacheKey(string $path, array $params): string
    {
        return 'page_' . md5($path . serialize($params));
    }

    private function sendRequest(string $url, array $params, string $method): \Illuminate\Http\Client\Response
    {
        $proxyRequest = Http::timeout(300);
        return $proxyRequest->get($url);
    }

    private function extractContent(string $html, string $selector): string
    {
        $crawler = new Crawler($html);
        return $crawler->filter($selector)->html();
    }

    private function processContent($content): string
    {
        $ourDomain = rtrim(env('APP_URL'), '/');
        $ourBaseDomain = parse_url($ourDomain, PHP_URL_HOST);
        $sourceBaseDomain = config('url_mappings.source_domain');

        // Wrap the content in a temporary root element to ensure proper parsing
        $wrappedContent = "<div class=\"content-wrapper\">{$content}</div>";
        $crawler = new Crawler($wrappedContent);

        // Find the element ".container .row .content-city-inner" and remove the parent ".container"
        // only if found to avoid removing the wrong element
        try {
            $crawler->filter('.container .row .content-city-inner')->each(function (Crawler $node) {
                // Find the closest .container parent
                $containerNode = $node->closest('.container');
                if ($containerNode->count() > 0) {
                    // Remove the entire container and its contents
                    $containerNode->getNode(0)->parentNode->removeChild($containerNode->getNode(0));
                }
            });
        } catch (\Exception $e) {
        }

        // Find element ".news-popular" then remove it
        try {
            $crawler->filter('.news-popular')->each(function (Crawler $node) {
                $node->getNode(0)->parentNode->removeChild($node->getNode(0));
            });
        } catch (\Exception $e) {
        }

        // Find element ".weather-highlight-live .card .card-body .new-cate"
        // if found, remove the parent ".card"
        try {
            $crawler->filter('.weather-highlight-live .card .card-body .new-cate')->each(function (Crawler $node) {
                $cardNode = $node->closest('.card');
                if ($cardNode->count() > 0) {
                    $cardNode->getNode(0)->parentNode->removeChild($cardNode->getNode(0));
                }
            });
        } catch (\Exception $e) {
        }

        // Prepend text "Thời tiết " to each element ".weather-highlight-list .weather-sub .title"
        try {
            $crawler->filter('.weather-highlight-list .weather-sub .title')->each(function (Crawler $node) {
                $node->getNode(0)->nodeValue = 'Thời tiết ' . $node->text();
            });
        } catch (\Exception $e) {
        }

        // Prepend text "Thời tiết " to each element ".weather-city .weather-city-inner li .list-city-lq a"
        try {
            $crawler->filter('.weather-city .weather-city-inner li .list-city-lq a')->each(function (Crawler $node) {
                $node->getNode(0)->nodeValue = 'Thời tiết ' . $node->text();
            });
        } catch (\Exception $e) {
        }

        // Replace href attributes in anchor tags
        $crawler->filter('a')->each(function (Crawler $node) use ($ourDomain, $sourceBaseDomain) {
            $href = $node->attr('href');
            if ($href && str_contains($href, $sourceBaseDomain)) {
                // Convert absolute URLs to relative paths
                $path = parse_url($href, PHP_URL_PATH);
                $query = parse_url($href, PHP_URL_QUERY);
                $newHref = $path . ($query ? "?{$query}" : '');
                $node->getNode(0)->setAttribute('href', $ourDomain . $newHref);
            }
        });

        // Replace domain names in text nodes (excluding script and style tags)
        $crawler->filter('*')->each(function (Crawler $node) use ($ourBaseDomain, $sourceBaseDomain) {
            // Skip processing if it's an img tag
            if ($node->nodeName() === 'img') {
                return;
            }

            $children = $node->getNode(0)->childNodes;

            foreach ($children as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $text = $child->nodeValue;
                    // Replace domain in text while preserving the rest of the content
                    if (str_contains($text, $sourceBaseDomain)) {
                        $newText = str_replace($sourceBaseDomain, $ourBaseDomain, $text);
                        $child->nodeValue = $newText;
                    }
                }
            }
        });

        // Get the processed HTML content, removing the wrapper div
        $processedContent = $crawler->filter('.content-wrapper')->html();

        // Clean up any remaining absolute URLs in the content
        $processedContent = str_replace(
            ['https://' . $sourceBaseDomain, 'http://' . $sourceBaseDomain],
            $ourDomain,
            $processedContent
        );

        $crawler = new Crawler($processedContent);

        // Insert component view "latest-articles" after ".weather-city" element
        try {
            $crawler->filter('.weather-city')->each(function (Crawler $node) {
                $articles = Article::latest()
                    ->where('is_published', 1)
                    ->limit(5)
                    ->get();

                $latestArticlesView = view('components.latest-articles', compact('articles'))
                    ->render();

                // Create a new DOMDocument with proper encoding
                $dom = new \DOMDocument('1.0', 'UTF-8');

                // Prevent HTML5 errors
                libxml_use_internal_errors(true);

                // Convert HTML to UTF-8 before loading
                $utf8Html = mb_convert_encoding($latestArticlesView, 'HTML-ENTITIES', 'UTF-8');

                // Load the HTML with encoding options
                $dom->loadHTML($utf8Html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors();

                // Import the node into the current document
                $newNode = $node->getNode(0)->ownerDocument->importNode($dom->documentElement, true);

                // Insert the new node after the weather-city element
                $node->getNode(0)->parentNode->insertBefore($newNode, $node->getNode(0)->nextSibling);
            });
        } catch (\Exception $e) {

        }

        $processedContent = $crawler->html();

        return $processedContent;
    }

    private function getRandomUserAgent()
    {
        return $this->userAgents[array_rand($this->userAgents)];
    }

    private function getCustomContent($mapping)
    {
        return view($mapping['template'])->render();
    }

    private function getScrapedContent($mapping, $path)
    {
        $cacheEnabled = setting('cache_enabled');

        if ($cacheEnabled) {
            return Cache::remember('page_' . md5($path), setting('cache_lifetime'), function() use ($mapping, $path) {
                return $this->scrapeContent($mapping, $path);
            });
        } else {
            return $this->scrapeContent($mapping, $path);
        }
    }

    private function scrapeContent($mapping, $path)
    {
        if (! RateLimiter::attempt('scraping', 60, function() { })) {
            throw new TooManyRequestsException();
        }

        try {
            $sourceUrl = $mapping['source_url'] . $path;
            $response = Http::withHeaders([
                'User-Agent' => $this->getRandomUserAgent()
            ])->get($sourceUrl);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch content: {$response->status()}");
            }

            $crawler = new Crawler($response->body());
            $content = $crawler->filter($mapping['main_selector'])->html();
            return $this->processContent($content);
        } catch (\Exception $e) {
            Log::error("Scraping failed for {$path}: " . $e->getMessage());
            return Cache::get('page_' . md5($path));
        }
    }
}
