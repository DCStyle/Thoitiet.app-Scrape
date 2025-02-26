<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter by category
        if ($request->has('category')) {
            $category = $request->get('category');
            $query->where('article_category_id', $category);
        }

        $articles = $query->latest()
            ->paginate(50)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $article = new Article();
        $categories = ArticleCategory::all();

        return view('admin.articles.add_edit', compact('article', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'article_category_id' => 'required|exists:article_categories,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('articles', 'public');
        }

        $validated['user_id'] = auth()->id();

        $article = Article::create($validated);

        $this->attachContentImages($article, $validated['content']);

        if ($article->is_published) {
            $this->addToSitemap($article);
        }

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article created successfully');
    }

    public function edit(Article $article)
    {
        $categories = ArticleCategory::all();

        return view('admin.articles.add_edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'article_category_id' => 'required|exists:article_categories,id',
        ]);

        if (!isset($validated['is_prediction'])) {
            $validated['is_prediction'] = false;
            $validated['prediction_type'] = null;
        }

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $validated['image'] = $request->file('image')->store('articles', 'public');
        }

        $article->update($validated);

        $this->attachContentImages($article, $validated['content']);

        if ($article->is_published) {
            $this->addToSitemap($article);
        } else {
            $this->removeFromSitemap($article);
        }

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article updated successfully');
    }

    public function destroy(Article $article)
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $this->removeFromSitemap($article);

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'selected_articles' => 'required|array',
            'selected_articles.*' => 'exists:articles,id'
        ]);

        $articles = Article::whereIn('id', $validated['selected_articles'])->get();
        $deletedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($articles as $article) {
                // Delete article image if exists
                if ($article->image) {
                    Storage::disk('public')->delete($article->image);
                }

                // Remove from sitemap
                $this->removeFromSitemap($article);

                $deletedCount++;
            }

            // Delete all selected articles
            Article::destroy($validated['selected_articles']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} bài viết đã được xóa thành công",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa bài viết: ' . $e->getMessage()
            ], 500);
        }
    }

    private function attachContentImages($article, $content)
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                $path = str_replace(asset('storage/'), '', $src);
                Image::where('path', $path)
                    ->whereNull('imageable_type')
                    ->update([
                        'imageable_type' => Article::class,
                        'imageable_id' => $article->id
                    ]);
            }
        }
    }

    private function addToSitemap($article)
    {
        $sourceDomain = 'https://' . config('url_mappings.source_domain');
        $articleUrl = url("{$sourceDomain}/tin-tuc/{$article->slug}");

        // Remove the article from sitemap first if it exists
        if (DB::table('sitemaps')->where('url', $articleUrl)->exists()) {
            $this->removeFromSitemap($article);
        }

        // Then insert new record
        DB::table('sitemaps')->insert([
            'url' => $articleUrl,
            'parent_path' => 'posts.xml',
            'last_modified' => now()->format('Y-m-d H:i:s'),
            'level' => 1,
            'is_index' => false,
            'priority' => '0.8',
            'changefreq' => 'daily',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function removeFromSitemap($article)
    {
        $sourceDomain = 'https://' . config('url_mappings.source_domain');
        DB::table('sitemaps')
            ->where([
                'url' => url("{$sourceDomain}/tin-tuc/{$article->slug}"),
                'parent_path' => 'posts.xml'
            ])
            ->delete();
    }
}
