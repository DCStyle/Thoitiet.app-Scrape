<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(10);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $page = new Page;

        return view('admin.pages.add_edit', compact('page'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $page = Page::create($validated);

        $this->attachContentImages($page, $validated['content']);

        if ($validated['is_active']) {
            $this->addToSitemap($page);
        }

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Trang đã được tạo thành công.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.add_edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $page->update($validated);

        $this->attachContentImages($page, $validated['content']);

        if ($validated['is_active']) {
            $this->addToSitemap($page);
        } else {
            $this->removeFromSitemap($page);
        }

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Trang đã được cập nhật thành công.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        $this->removeFromSitemap($page);

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Trang đã được xóa thành công.');
    }

    private function attachContentImages($page, $content)
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                $path = str_replace(asset('storage/'), '', $src);
                Image::where('path', $path)
                    ->whereNull('imageable_type')
                    ->update([
                        'imageable_type' => Page::class,
                        'imageable_id' => $page->id
                    ]);
            }
        }
    }

    private function addToSitemap($page)
    {
        $baseUrl = config('app.url');
        $pageUrl = "{$baseUrl}/trang/{$page->slug}";

        // Remove the article from sitemap first if it exists
        if (DB::table('sitemaps')->where('url', $pageUrl)->exists()) {
            $this->removeFromSitemap($page);
        }

        // Then insert new record
        DB::table('sitemaps')->insert([
            'url' => $pageUrl,
            'parent_path' => 'pages.xml',
            'last_modified' => now()->format('Y-m-d H:i:s'),
            'level' => 1,
            'is_index' => false,
            'priority' => '0.8',
            'changefreq' => 'daily',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function removeFromSitemap($page)
    {
        $baseUrl = config('app.url');
        $pageUrl = "{$baseUrl}/trang/{$page->slug}";

        DB::table('sitemaps')
            ->where([
                'url' => $pageUrl,
                'parent_path' => 'pages.xml'
            ])
            ->delete();
    }
}
