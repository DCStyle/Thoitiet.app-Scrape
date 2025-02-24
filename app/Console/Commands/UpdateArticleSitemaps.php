<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateArticleSitemaps extends Command
{
    protected $signature = 'articles:update-sitemaps';
    protected $description = 'Add sitemaps for existing published articles';

    public function handle()
    {
        $sourceDomain = 'https://' . config('url_mappings.source_domain');

        // Truncate sitemaps table for articles
        DB::table('sitemaps')->where('parent_path', 'posts.xml')->delete();

        $articles = Article::where('is_published', true)->get();
        $count = 0;

        foreach ($articles as $article) {
            $articleUrl = url("{$sourceDomain}/tin-tuc/{$article->slug}");

            if (!DB::table('sitemaps')->where('url', $articleUrl)->exists()) {
                DB::table('sitemaps')->insert([
                    'url' => $articleUrl,
                    'parent_path' => 'posts.xml',
                    'last_modified' => $article->updated_at,
                    'level' => 1,
                    'is_index' => false,
                    'priority' => '0.8',
                    'changefreq' => 'daily',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $count++;
            }
        }

        $this->info("Added {$count} articles to sitemap");
    }
}
