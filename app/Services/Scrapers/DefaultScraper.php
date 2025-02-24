<?php

namespace App\Services\Scrapers;

use App\Services\Scrapers\BaseScraper;

class DefaultScraper extends BaseScraper
{

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->template = config('url_mappings.default_scrape.template');
        $this->selector = config('url_mappings.default_scrape.main_selector');
    }

    protected function getSourceUrl(): string
    {
        return config('url_mappings.default_scrape.source_url');
    }

    protected function processResponse($content): array
    {
        return [
            'content' => $content,
            'template' => $this->template,
            'metadata' => $this->metadata
        ];
    }
}
