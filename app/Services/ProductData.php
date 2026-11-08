<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductData
{
    public static function defaults(string $type = 'simple', bool $variation = false): array
    {
        return ['gtin' => null, 'mpn' => null, 'tax_status' => 'taxable', 'tax_class' => $variation ? 'parent' : 'standard',
            'manage_stock' => $variation || $type === 'simple', 'backorders' => 'no', 'low_stock' => 2, 'stock_status' => 'instock',
            'sold_individually' => false, 'weight' => null, 'length' => null, 'width' => null, 'height' => null, 'shipping_class' => null,
            'purchase_note' => null, 'menu_order' => 0, 'reviews_enabled' => true, 'description' => null, 'image' => null];
    }

    public static function validate(array $data, string $prefix = 'details'): array
    {
        $rules = ['gtin' => 'nullable|string|max:100', 'mpn' => 'nullable|string|max:100', 'tax_status' => Rule::in(['taxable', 'shipping', 'none']),
            'tax_class' => Rule::in(['parent', 'standard', 'reduced', 'zero']), 'manage_stock' => 'boolean', 'backorders' => Rule::in(['no', 'notify', 'yes']),
            'stock_status' => Rule::in(['instock', 'outofstock', 'onbackorder']), 'low_stock' => 'nullable|integer|min:0|max:100000000',
            'sold_individually' => 'boolean', 'shipping_class' => 'nullable|string|max:120', 'purchase_note' => 'nullable|string|max:10000',
            'menu_order' => 'integer|min:0|max:100000000', 'reviews_enabled' => 'boolean', 'description' => 'nullable|string|max:10000', 'image' => 'nullable|string|max:500'];
        foreach (['weight', 'length', 'width', 'height'] as $field) {
            $rules[$field] = 'nullable|numeric|min:0|max:1000000';
        }
        $input = [];
        data_set($input, $prefix, $data);
        $valid = Validator::make($input, collect($rules)->mapWithKeys(fn ($rule, $field) => [$prefix.'.'.$field => $rule])->all())->validate();
        $result = data_get($valid, $prefix);
        $result['image'] = ProductMedia::url($result['image'] ?? null);

        return $result;
    }
}
