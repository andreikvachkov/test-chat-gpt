<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'external_id', 'name', 'slug', 'description', 'ingredients', 'usage', 'image', 'gallery', 'badge', 'published'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'gallery' => 'array'];
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
