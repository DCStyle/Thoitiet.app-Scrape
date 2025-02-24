<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($sitemaps as $sitemap)
        <sitemap>
            <loc>{{ url(str_replace('ketqua.vn', request()->getHost(), parse_url($sitemap->url, PHP_URL_PATH))) }}</loc>
            @if($sitemap->last_modified)
                <lastmod>{{ $sitemap->last_modified }}</lastmod>
            @endif
        </sitemap>
    @endforeach
</sitemapindex>
