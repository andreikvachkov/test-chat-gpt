<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'external_id', 'sku', 'article', 'name', 'price_minor', 'stock', 'active', 'attribute_value_ids', 'combination_key', 'retired_at'];

    protected $hidden = ['external_id'];

    protected function casts(): array
    {
        return ['price_minor' => 'integer', 'stock' => 'integer', 'active' => 'boolean', 'attribute_value_ids' => 'array', 'retired_at' => 'datetime'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
