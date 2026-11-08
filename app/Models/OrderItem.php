<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['product_variant_id', 'name', 'sku', 'price_minor', 'quantity', 'purchase_note', 'backordered'];

    protected function casts(): array
    {
        return ['price_minor' => 'integer', 'quantity' => 'integer', 'backordered' => 'boolean'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
