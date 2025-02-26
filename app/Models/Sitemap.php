<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sitemap extends Model
{
    protected $fillable = [
        'url',
        'parent_path',
        'last_modified',
        'level',
        'is_index',
        'priority',
        'changefreq',
    ];

    public function children()
    {
        return $this->hasMany(Sitemap::class, 'parent_path', 'url');
    }

    public function parent()
    {
        return $this->belongsTo(Sitemap::class, 'url', 'parent_path');
    }
}
