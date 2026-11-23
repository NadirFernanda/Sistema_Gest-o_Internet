<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = ['image_path', 'eyebrow', 'title', 'highlighted_title', 'description', 'primary_label', 'primary_url', 'secondary_label', 'secondary_url', 'sort_order', 'active'];

    protected $casts = ['active' => 'boolean', 'sort_order' => 'integer'];

    public function getImageUrlAttribute(): string
    {
        return str_starts_with($this->image_path, '/')
            ? asset(ltrim($this->image_path, '/'))
            : route('hero-slides.image', $this);
    }
}
