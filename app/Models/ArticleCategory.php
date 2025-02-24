<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ArticleCategory extends Model
{
    use HasSEO;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $slug = Str::slug($category->name);

            $count = ArticleCategory::where('slug', 'like', $slug . '%')->count();
            if ($count > 0) {
                $i = 1;
                while (ArticleCategory::where('slug', $slug)->exists()) {
                    $slug = Str::slug($category->name . '-' . $i);
                    $i++;
                }
            }

            $category->slug = $slug;
        });
    }

    protected static function booted()
    {
        static::deleting(function($category) {
            $category->articles->each(function($article) {
                $article->delete();
            });
        });
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->meta_title && $this->meta_title !== '' ? $this->meta_title : $this->title,
            description: $this->meta_description && $this->meta_description !== '' ? $this->meta_description : '',
            image: $this->articles()->latest()->first()?->getThumbnail() ?? '',
        );
    }
}
