<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/** Normalized boundary for a future 1C/CommerceML adapter; deliberately no public upload endpoint. */
class CatalogImporter
{
    public function import(array $products): int
    {
        Validator::make(['products' => $products], [
            'products' => 'required|array|max:10000', 'products.*.external_id' => 'required|string|max:255|distinct',
            'products.*.name' => 'required|string|max:255', 'products.*.slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|distinct',
            'products.*.description' => 'nullable|string', 'products.*.variants' => 'required|array|min:1',
            'products.*.variants.*.external_id' => 'required|string|max:255|distinct',
            'products.*.variants.*.sku' => 'required|string|max:255|distinct', 'products.*.variants.*.name' => 'required|string|max:255',
            'products.*.variants.*.price_minor' => 'required|integer|min:0|max:10000000000', 'products.*.variants.*.stock' => 'required|integer|min:0|max:100000000',
        ])->validate();

        return DB::transaction(function () use ($products) {
            foreach ($products as $data) {
                $product = Product::firstOrNew(['external_id' => $data['external_id']]);
                if (Product::where('slug', $data['slug'])->when($product->exists, fn ($q) => $q->where('id', '!=', $product->id))->exists()) {
                    throw ValidationException::withMessages(['products' => 'Адрес товара уже занят: '.$data['slug']]);
                }
                // Editorial images, composition, usage and publication remain controlled by the CMS.
                $product->fill(collect($data)->only(['name', 'slug', 'description'])->all())->save();
                foreach ($data['variants'] as $v) {
                    $variant = ProductVariant::where('external_id', $v['external_id'])->lockForUpdate()->first();
                    if ($variant && $variant->product_id !== $product->id) {
                        throw ValidationException::withMessages(['products' => 'Вариант принадлежит другому товару.']);
                    }
                    if (ProductVariant::where('sku', $v['sku'])->when($variant, fn ($q) => $q->where('id', '!=', $variant->id))->exists()) {
                        throw ValidationException::withMessages(['products' => 'Артикул уже занят: '.$v['sku']]);
                    }
                    $variant ??= new ProductVariant(['external_id' => $v['external_id'], 'product_id' => $product->id]);
                    // Input stock is the available-to-sell quantity from the source, not warehouse gross stock.
                    $variant->fill(collect($v)->only(['sku', 'name', 'price_minor', 'stock'])->all())->save();
                }
            }

            return count($products);
        });
    }
}
