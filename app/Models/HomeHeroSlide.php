<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeHeroSlide extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'button_text',
        'button_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
