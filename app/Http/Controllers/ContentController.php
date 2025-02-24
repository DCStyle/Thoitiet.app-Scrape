<?php

namespace App\Http\Controllers;

use App\Services\ContentMirrorService;
use App\Services\Scrapers\BaseScraper;
use App\Services\Scrapers\CheckLotteryTicketScraper;
use App\Services\Scrapers\DefaultScraper;
use App\Services\Scrapers\HistoricalResultsScraper;
use App\Services\Scrapers\QuayThuScraper;
use App\Services\Scrapers\SoiCauScraper;
use App\Services\Scrapers\ThongKeScraper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ContentController extends Controller
{
    private ContentMirrorService $mirrorService;
    protected array $scrapers = [
        'do-ve-so' => CheckLotteryTicketScraper::class,
    ];

    protected function initializeScrapers(): array
    {
        $durations = [30, 60, 90, 100, 200, 300, 500];

        foreach ($durations as $days) {
            $this->scrapers["so-ket-qua-{$days}-ngay"] = function() use ($days) {
                return new HistoricalResultsScraper($days);
            };
        }

        $this->scrapers['so-ket-qua'] = function() {
            return new HistoricalResultsScraper(10);
        };

        $soiCauTypes = [
            'loto',
            'loto-theo-thu',
            'bach-thu',
            'loto-bach-thu-theo-thu',
            'giai-dac-biet',
            'giai-dac-biet-theo-thu',
            'an-hai-nhay',
            'tam-giac',
            'loai-loto',
            'loai-loto-bach-thu',
        ];

        foreach ($soiCauTypes as $type) {
            $this->scrapers["soi-cau/{$type}"] = function() use ($type) {
                return new SoiCauScraper($type);
            };
        }

        $thongKeTypes = [
            'loto-gan',
            'chu-ky-dan-loto',
            'ket-qua-xo-so',
            'dau-duoi-loto',
            'tan-suat-loto',
            'chu-ky-dan-dac-biet',
            'cang-loto',
            'theo-tong',
            'quan-trong',
            'dai-nhat',
            'chu-ky-gan-theo-tinh',
            'tan-so-nhip-loto',
            'dac-biet-tuan',
            'dac-biet-thang',
        ];

        foreach ($thongKeTypes as $type) {
            $this->scrapers["thong-ke/{$type}"] = function() use ($type) {
                return new ThongKeScraper($type);
            };
        }

        $quayThuTypes = [
            'xsmb',
            'xsmn',
            'xsmt',
        ];

        foreach ($quayThuTypes as $type) {
            $this->scrapers["quay-thu-{$type}"] = function() use ($type) {
                return new QuayThuScraper($type);
            };
        }

        $this->scrapers['quay-thu-xo-so-hom-nay'] = function() {
            return new QuayThuScraper('xsmb');
        };

        return $this->scrapers;
    }

    public function __construct(ContentMirrorService $mirrorService)
    {
        $this->mirrorService = $mirrorService;
        $this->scrapers = $this->initializeScrapers();
    }

    public function show(Request $request, string $path = ''): View
    {
        $result = $this->handlePath($request, $path);

        if (!$result) {
            abort(404);
        }

        if ((bool) setting('cache_enabled')) {
            // Track cache metrics
            Cache::put('last_cached_url', $path, now()->addDay());
            Cache::put('last_cache_time', now(), now()->addDay());
        }

        return $this->createResponse($result, $path);
    }

    private function handlePath(Request $request, string $path): ?array
    {
        // Normalize path by removing leading slash
        $path = ltrim($path, '/');

        // Check custom paths first
        $urlMappings = config('url_mappings.paths');
        foreach ($urlMappings as $mapping) {
            if (isset($mapping['our_paths'][$path]) || isset($mapping['our_paths']["/$path"])) {
                return [
                    'content' => null,
                    'template' => $mapping['our_paths'][$path] ?? $mapping['our_paths']["/$path"]
                ];
            }
        }

        if (isset($this->scrapers[$path])) {
            return $this->handleScraper($request, $path);
        }

        // Use DefaultScraper for unspecified paths
        $scraper = new DefaultScraper($path);
        return $scraper->handle($request->isMethod('post') ? $request->all() : []);
    }

    private function handleScraper(Request $request, string $path): ?array
    {
        $scraperFactory = $this->scrapers[$path];

        /** @var BaseScraper $scraper */
        $scraper = is_callable($scraperFactory) ? $scraperFactory() : app($scraperFactory);

        $params = $request->isMethod('post') ? $request->all() : [];
        return $scraper->handle($params);
    }

    private function createResponse(array $result, $path = ''): View
    {
        $metadata = $result['metadata'] ?? null;

        // Get the current path
        $currentPath = $path ?: '/';

        // Add prefix slash to path if it doesn't have one
        if (!str_starts_with($currentPath, '/')) {
            $currentPath = '/' . $currentPath;
        }

        // Add suffix slash to path if it doesn't have one
        if (!str_ends_with($currentPath, '/')) {
            $currentPath .= '/';
        }

        if ($currentPath == '/')
        {
            $customTitle = setting('site_name');
            $customDescription = setting('site_description');
        } else {
            // Get custom path settings
            $pathTitles = setting('site_path_title') ? json_decode(setting('site_path_title'), true) : [];
            $pathDescriptions = setting('site_path_description') ? json_decode(setting('site_path_description'), true) : [];

            // Try to find matching path in settings
            $customTitle = null;
            $customDescription = null;

            foreach ($pathTitles as $index => $titleData) {
                $settingPath = array_key_first($titleData);

                // Check if current path matches the setting path
                if ($currentPath === $settingPath || rtrim($currentPath, '/') === rtrim($settingPath, '/')) {
                    $customTitle = $titleData[$settingPath];

                    // Get matching description if it exists
                    if (isset($pathDescriptions[$index][$settingPath])) {
                        $customDescription = $pathDescriptions[$index][$settingPath];
                    }
                    break;
                }
            }
        }

        // Build metadata array
        $metadata['title'] = $customTitle ?? $metadata['title'] ?? setting('site_name');
        $metadata['description'] = $customDescription ?? $metadata['description'] ?? setting('site_description');
        $metadata['keywords'] = $metadata['keywords'] ?? setting('site_keywords');
        $metadata['canonical'] = url()->current();

        $viewData = $result['data'] ?? [];

        return view($result['template'], [
                'content' => $result['content'],
                'metadata' => $metadata
            ] + $viewData)->withHeaders([
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'public, max-age=300'
        ]);
    }
}
