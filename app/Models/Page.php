<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            // Prepare slug before saving
            $slug = Str::slug($page->title);

            // Check if slug already exists
            $count = Page::where('slug', 'like', $slug . '%')->count();
            if ($count > 0) {
                // Check if slug already exists using a while loop
                $i = 1;
                while (Page::where('slug', $slug)->exists()) {
                    $slug = Str::slug($page->title . '-' . $i);
                    $i++;
                }
            }

            $page->slug = $slug;
        });
    }

    protected static function booted()
    {
        static::deleting(function($article) {
            $article->images->each(function($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
        });
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->meta_title && $this->meta_title !== '' ? $this->meta_title : $this->title,
            description: $this->meta_description && $this->meta_description !== '' ? $this->meta_description : $this->exceprt(),
            image: $this->getThumbnail()
        );
    }

    public function readTime()
    {
        $wordCount = str_word_count(strip_tags(html_entity_decode($this->content)));
        $minutes = floor($wordCount / 200);
        $seconds = floor($wordCount % 200 / (200 / 60));
        $time = '';
        if ($minutes) {
            $time .= $minutes . ' phút';
        }
        if ($seconds) {
            $time .= ' ' . $seconds . ' giây';
        }
        return $time;
    }

    public function exceprt($length = 200)
    {
        return Str::limit(strip_tags(html_entity_decode($this->content)), $length);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getThumbnail()
    {
        // Check if the article has any images
        if ($this->images->count() > 0) {
            return Storage::url($this->images->first()->path);
        }

        // If not, check if the article has an image
        $matches = [];
        preg_match_all('/<img[^>]+>/i', $this->content, $matches);
        if (count($matches) > 0) {
            $img = (isset($matches[0][0])) ? $matches[0][0] : '';
            preg_match('/src="([^"]+)"/', $img, $src);
            return $src[1] ?? 'https://placehold.co/300?text=' . urlencode($this->title);
        }

        // If not, return a default image
        return 'https://placehold.co/300?text=' . urlencode($this->title);
    }
}
