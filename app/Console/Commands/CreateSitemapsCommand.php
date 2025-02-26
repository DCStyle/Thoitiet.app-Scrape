<?php

namespace App\Console\Commands;

use App\Models\Sitemap;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateSitemapsCommand extends Command
{
    protected $signature = 'sitemap:create';
    protected $description = 'Create predefined sitemaps';

    public function handle()
    {
        $siteUrl = config('app.url');

        $this->info('Creating predefined sitemaps...');

        // Clear existing sitemaps
        Sitemap::truncate();
        $this->info('Existing sitemaps cleared.');

        // Creating current sitemaps
        $this->info('Creating current sitemaps...');

        for($i = 1; $i <= 12; $i++) {
            Sitemap::create([
                'url' => "$siteUrl/hientai{$i}.xml",
                'parent_path' => null,
                'last_modified' => Carbon::now(),
                'level' => 0,
                'is_index' => 1,
                'priority' => '0.5',
                'changefreq' => 'daily',
            ]);
        }

        $this->info('Created current sitemaps.');

        // Creating worldwide sitemaps
        $this->info('Creating worldwide sitemaps...');

        for($i = 1; $i <= 12; $i++) {
            Sitemap::create([
                'url' => "$siteUrl/thegioi{$i}.xml",
                'parent_path' => null,
                'last_modified' => Carbon::now(),
                'level' => 0,
                'is_index' => 1,
                'priority' => '0.5',
                'changefreq' => 'daily',
            ]);
        }

        $this->info('Created worldwide sitemaps.');

        // Creating tomorrow sitemaps
        $this->info('Creating tomorrow sitemaps...');

        for($i = 1; $i <= 12; $i++) {
            Sitemap::create([
                'url' => "$siteUrl/ngaymai{$i}.xml",
                'parent_path' => null,
                'last_modified' => Carbon::now(),
                'level' => 0,
                'is_index' => 1,
                'priority' => '0.5',
                'changefreq' => 'daily',
            ]);
        }

        $this->info('Created tomorrow sitemaps.');

        // Creating next-X-days sitemaps
        $this->info('Creating next-X-days sitemaps...');

        $nextDays = [3, 5, 7, 10, 15, 20, 30];

        foreach($nextDays as $nextDay) {
            for($i = 1; $i <= 12; $i++) {
                Sitemap::create([
                    'url' => "$siteUrl/{$nextDay}ngaytoi{$i}.xml",
                    'parent_path' => null,
                    'last_modified' => Carbon::now(),
                    'level' => 0,
                    'is_index' => 1,
                    'priority' => '0.5',
                    'changefreq' => 'daily',
                ]);
            }
        }

        $this->info('Created next-X-days sitemaps.');

        // Creating posts sitemaps
        $this->info('Creating posts sitemaps...');

        Sitemap::create([
            'url' => "$siteUrl/posts.xml",
            'parent_path' => null,
            'last_modified' => Carbon::now(),
            'level' => 0,
            'is_index' => 1,
            'priority' => '0.5',
            'changefreq' => 'daily',
        ]);

        $this->info('Created posts sitemaps.');

        $this->info('Created ' . Sitemap::count() . ' sitemap entries.');

        return 0;
    }
}
