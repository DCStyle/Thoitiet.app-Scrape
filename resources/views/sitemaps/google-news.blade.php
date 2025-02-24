<?xml version="1.0" encoding="UTF-8" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach($items as $item)
        <url>
            <loc>{{ $item['link'] }}</loc>
            <image:image>
                <image:loc>{{ $item['image'] }}</image:loc>
                <image:title>{{ $item['title'] }}</image:title>
            </image:image>
            <lastmod>{{ $item['lastModified'] }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>1.0</priority>
            <news:news>
                <news:publication>
                    <news:name>{{ $siteName }}</news:name>
                    <news:language>{{ $language }}</news:language>
                </news:publication>
                <news:geo_locations>{{ $location }}</news:geo_locations>
                <news:publication_date>{{ $item['published'] }}</news:publication_date>
                <news:title>{{ $item['title'] }}</news:title>
                <news:keywords/>
            </news:news>
        </url>
    @endforeach
</urlset>
