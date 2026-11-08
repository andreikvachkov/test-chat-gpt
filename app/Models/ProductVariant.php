<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'external_id', 'sku', 'name', 'price_minor', 'stock', 'active'];

    protected function casts(): array
    {
        return ['price_minor' => 'integer', 'stock' => 'integer', 'active' => 'boolean'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
