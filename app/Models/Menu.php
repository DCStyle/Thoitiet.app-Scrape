<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'title',
        'icon',
        'url',
        'parent_id',
        'order'
    ];

    // Relationship for parent
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Relationship for children
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }
}
