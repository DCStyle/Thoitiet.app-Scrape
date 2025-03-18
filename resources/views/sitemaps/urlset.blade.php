<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach($urls as $url)
        <url>
            <loc>{{ str_replace(config('url_mappings.source_domain'), parse_url(env('APP_URL'), PHP_URL_HOST), $url->url) }}</loc>
            @if($url->last_modified)
                <lastmod>{{ \Carbon\Carbon::parse($url->last_modified)
                ->setTimezone('Asia/Ho_Chi_Minh')
                ->toW3cString() }}</lastmod>
            @endif
            <changefreq>{{ $url->changefreq }}</changefreq>
            <priority>{{ $url->priority }}</priority>
        </url>
    @endforeach
</urlset>
