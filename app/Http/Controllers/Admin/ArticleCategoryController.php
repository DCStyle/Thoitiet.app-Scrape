<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class ArticleCategoryController extends Controller
{
    public function index()
    {
        $categories = ArticleCategory::all();

        return view('admin.article-categories.index', compact('categories'));
    }

    public function create()
    {
        $category = new ArticleCategory();

        return view('admin.article-categories.add_edit', compact('category'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        ArticleCategory::create($validated);

        return redirect()->route('admin.article-categories.index');
    }

    public function edit(ArticleCategory $category)
    {
        return view('admin.article-categories.add_edit', compact('category'));
    }

    public function update(Request $request, ArticleCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('admin.article-categories.index');
    }

    public function destroy(ArticleCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.article-categories.index');
    }
}
