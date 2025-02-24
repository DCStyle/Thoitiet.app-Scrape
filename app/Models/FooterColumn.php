<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterColumn extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'order'];

    public function items()
    {
        return $this->hasMany(FooterColumnItem::class)->orderBy('order');
    }
}
