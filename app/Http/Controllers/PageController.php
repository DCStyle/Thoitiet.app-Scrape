<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;

class PageController extends Controller
{
    public function show(Page $page)
    {
        $latestArticles = Article::latest()
            ->take(5)
            ->get();

        return view('pages.show', compact('page', 'latestArticles'));
    }
}
