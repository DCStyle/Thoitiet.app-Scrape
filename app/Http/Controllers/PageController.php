<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PageController extends Controller
{
    public function show(Page $page)
    {
        $latestArticles = Article::latest()
            ->take(5)
            ->get();

        $customMetadata = $this->getMetadata();
        $SEOData = new SEOData(
            $customMetadata['title'] ?? null,
            $customMetadata['description'] ?? null
        );

        return view('pages.show', compact(
            'page',
            'latestArticles',
            'SEOData'
        ));
    }
}
