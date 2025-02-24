<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RssController extends Controller
{
    public function index()
    {
        $items = Article::orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        $lastBuildDate = $items->first()
            ? Carbon::parse($items->first()->last_modified)->setTimezone('Asia/Ho_Chi_Minh')
            : Carbon::now('Asia/Ho_Chi_Minh');

        return response()
            ->view('feeds.rss', [
                'items' => $items,
                'title' => setting('site_name'),
                'description' => setting('site_description'),
                'language' => 'vi-VN',
                'lastBuildDate' => $lastBuildDate->toW3cString(),
            ])
            ->header('Content-Type', 'application/xml');
    }

    public function category($category)
    {
        $items = Cache::remember("rss_feed_{$category}", 60, function () use ($category) {
            return DB::table('sitemaps')
                ->whereNotNull('last_modified')
                ->where('is_index', false)
                ->where('parent_path', 'posts.xml')
                ->where('url', 'like', "%/{$category}/%")
                ->orderBy('last_modified', 'desc')
                ->limit(50)
                ->get();
        });

        $lastBuildDate = $items->first()
            ? Carbon::parse($items->first()->last_modified)->setTimezone('Asia/Ho_Chi_Minh')
            : Carbon::now('Asia/Ho_Chi_Minh');

        return response()
            ->view('feeds.rss', [
                'items' => $items,
                'title' => setting('site_name') . " - {$category}",
                'description' => setting('site_description'),
                'language' => 'vi-VN',
                'lastBuildDate' => $lastBuildDate->toW3cString(),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
