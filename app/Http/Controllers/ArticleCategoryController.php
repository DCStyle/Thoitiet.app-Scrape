<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;

class ArticleCategoryController extends Controller
{
    public function show(ArticleCategory $category)
    {
        $categories = ArticleCategory::where('id', '!=', $category->id)->get();
        $articles = $category->articles()->paginate(10);

        $latestArticles = Article::latest()
            ->where('article_category_id', '!=', $category->id)
            ->limit(5)
            ->get();

        return view('article-categories.show', compact(
            'category',
            'categories',
            'latestArticles',
            'articles'
        ));
    }
}
