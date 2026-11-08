<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class AttributeValue extends Model
{
    protected $fillable = ['product_attribute_id', 'name', 'slug'];

    protected static function booted(): void
    {
        static::deleting(function (self $value) {
            if (Product::withTrashed()->whereJsonContains('attribute_config', [['attribute_id' => $value->product_attribute_id, 'value_ids' => [$value->id]]])->exists()) {
                throw ValidationException::withMessages(['values' => 'Значение используется товарами. Сначала уберите его из этих товаров.']);
            }
        });
    }

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }
}
