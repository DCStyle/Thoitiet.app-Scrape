<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($sitemaps as $sitemap)
        <sitemap>
            <loc>{{ $sitemap->url }}</loc>
            @if($sitemap->last_modified)
                <lastmod>{{ \Carbon\Carbon::parse($sitemap->last_modified)
                ->setTimezone('Asia/Ho_Chi_Minh')
                ->toW3cString() }}</lastmod>
            @endif
        </sitemap>
    @endforeach
</sitemapindex>
