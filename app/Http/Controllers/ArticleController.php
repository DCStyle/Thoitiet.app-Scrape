<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ArticleController extends Controller
{
    public function index()
    {
        // Get categories list
        $categories = ArticleCategory::all();

        // Get latest paginated articles
        $articles = Article::latest()
            ->where('is_published', 1)
            ->paginate(10);

        // Get random articles which not in the latest articles
        $randomArticles = Article::inRandomOrder()
            ->where('is_published', 1)
            ->whereNotIn('id', $articles->pluck('id'))
            ->limit(5)
            ->get();

        $customMetadata = $this->getMetadata();
        $SEOData = new SEOData(
            $customMetadata['title'] ?? null,
            $customMetadata['description'] ?? null
        );

        return view('articles.index', compact(
            'categories',
            'articles',
            'randomArticles',
            'SEOData'
        ));
    }

    public function show(Article $article)
    {
        // If no slug, redirect to the article list
        if (!$article->slug) {
            return redirect()->route('articles.index');
        }

        // Check if article is published
        if (!$article->is_published) {
            abort(404);
        }

        // Get latest articles
        $latestArticles = Article::where('id', '!=', $article->id);

        $latestArticles = $latestArticles->where('is_published', 1)
            ->latest()
            ->limit(5)
            ->get();

        return view('articles.show', compact(
            'article',
            'latestArticles'
        ));
    }
}
