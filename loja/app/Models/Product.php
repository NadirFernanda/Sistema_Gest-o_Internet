<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_aoa',
        'stock',
        'image_path',
        'category',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'stock' => 'integer',
        'price_aoa' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
