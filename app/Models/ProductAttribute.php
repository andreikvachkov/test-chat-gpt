<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProductAttribute extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::deleting(function (self $attribute) {
            if (Product::withTrashed()->whereJsonContains('attribute_config', [['attribute_id' => $attribute->id]])->exists()) {
                throw ValidationException::withMessages(['values' => 'Атрибут используется товарами. Сначала уберите его из этих товаров.']);
            }
        });
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
