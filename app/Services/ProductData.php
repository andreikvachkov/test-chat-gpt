<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductData
{
    public static function defaults(string $type = 'simple', bool $variation = false): array
    {
        return ['manage_stock' => $variation || $type === 'simple', 'backorders' => 'no', 'low_stock' => 2, 'stock_status' => 'instock',
            'weight' => null, 'length' => null, 'width' => null, 'height' => null, 'menu_order' => 0, 'image' => null];
    }

    public static function validate(array $data, string $prefix = 'details'): array
    {
        $rules = ['manage_stock' => 'boolean', 'backorders' => Rule::in(['no', 'notify', 'yes']),
            'stock_status' => Rule::in(['instock', 'outofstock', 'onbackorder']), 'low_stock' => 'nullable|integer|min:0|max:100000000',
            'menu_order' => 'integer|min:0|max:100000000', 'image' => 'nullable|string|max:500'];
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

    public static function settings(?array $details, string $type = 'simple', bool $variation = false): array
    {
        $defaults = self::defaults($type, $variation);

        return array_replace($defaults, array_intersect_key($details ?? [], $defaults));
    }
}
