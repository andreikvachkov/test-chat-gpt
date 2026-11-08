<?php

namespace App\Services;

use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Normalized boundary for a future 1C/CommerceML adapter; deliberately no public upload endpoint. */
class CatalogImporter
{
    public function import(array $products): int
    {
        Validator::make(['products' => $products], [
            'products' => 'required|array|max:10000', 'products.*.sku' => 'required|string|max:255|distinct:ignore_case',
            'products.*.name' => 'required|string|max:255', 'products.*.slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|distinct',
            'products.*.description' => 'nullable|string', 'products.*.type' => 'required|in:simple,variable', 'products.*.article' => 'nullable|string|max:255', 'products.*.variants' => 'required|array|min:1|max:100',
            'products.*.variants.*.sku' => 'required|string|max:255|distinct:ignore_case', 'products.*.variants.*.name' => 'required|string|max:255',
            'products.*.variants.*.price_minor' => 'required|integer|min:0|max:10000000000', 'products.*.variants.*.stock' => 'required|integer|min:0|max:100000000',
            'products.*.variants.*.attributes' => 'array',
        ])->validate();

        return DB::transaction(function () use ($products) {
            DB::select('SELECT pg_advisory_xact_lock(71438511)');
            $editor = app(ProductEditor::class);
            foreach ($products as $data) {
                $product = Product::withTrashed()->whereRaw('lower(sku) = ?', [Str::lower(trim($data['sku']))])->first();
                if ($product?->trashed() || ($product && $product->type !== $data['type'])) {
                    throw ValidationException::withMessages(['products' => 'Импорт не восстанавливает удалённые товары и не меняет тип товара: '.$data['sku']]);
                }
                $state = $product ? $editor->state($product) : [
                    'name' => $data['name'], 'slug' => $data['slug'], 'sku' => $data['sku'], 'article' => $data['article'] ?? null,
                    'type' => $data['type'], 'status' => 'draft', 'description' => $data['description'] ?? '', 'short_description' => '',
                    'category_ids' => [], 'collection_ids' => [], 'image' => null, 'gallery' => [], 'attribute_config' => [], 'variants' => [],
                ];
                if ($state['type'] === 'simple') {
                    if (count($data['variants']) !== 1 || $data['variants'][0]['sku'] !== $data['sku']) {
                        throw ValidationException::withMessages(['products' => 'У простого товара одна складская позиция с SKU товара.']);
                    }
                    $v = $data['variants'][0];
                    $state['simple'] = ['price' => ProductEditor::money($v['price_minor']), 'stock' => $v['stock'], 'name' => $v['name']];
                } else {
                    if (! $product) {
                        $config = [];
                        foreach ($data['variants'] as $v) {
                            if (empty($v['attributes'])) {
                                throw ValidationException::withMessages(['products' => 'Для новой вариации укажите attributes: код атрибута → код значения.']);
                            }
                            foreach ($v['attributes'] as $attributeSlug => $valueSlug) {
                                $value = AttributeValue::whereHas('attribute', fn ($q) => $q->where('slug', $attributeSlug))->where('slug', $valueSlug)->first();
                                if (! $value) {
                                    throw ValidationException::withMessages(['products' => 'Неизвестное значение атрибута: '.$attributeSlug.' / '.$valueSlug]);
                                }
                                $id = $value->product_attribute_id;
                                $config[$id] ??= ['attribute_id' => $id, 'value_ids' => [], 'for_variations' => true, 'visible' => true];
                                $config[$id]['value_ids'][] = $value->id;
                            }
                        }
                        foreach ($config as &$row) {
                            $row['value_ids'] = array_values(array_unique($row['value_ids']));
                        }
                        unset($row);
                        $state['attribute_config'] = array_values($config);
                        $generated = $editor->generate($state['attribute_config'], [], $state['sku']);
                    } else {
                        $generated = [];
                    }
                    $rows = collect($state['variants'])->keyBy(fn ($v) => Str::lower($v['sku']));
                    foreach ($data['variants'] as $v) {
                        $key = Str::lower($v['sku']);
                        $row = $rows->get($key);
                        if (! $row && ! $product) {
                            $ids = [];
                            foreach ($v['attributes'] as $attributeSlug => $valueSlug) {
                                $ids[] = AttributeValue::whereHas('attribute', fn ($q) => $q->where('slug', $attributeSlug))->where('slug', $valueSlug)->value('id');
                            }
                            sort($ids);
                            $row = collect($generated)->first(function ($candidate) use ($ids) {
                                $values = $candidate['attribute_value_ids'];
                                sort($values);

                                return $ids === $values;
                            });
                        }
                        if (! $row) {
                            throw ValidationException::withMessages(['products' => 'Сначала создайте вариацию в админке: '.$v['sku']]);
                        }
                        $rows->put($key, array_replace($row, ['sku' => $v['sku'], 'price' => ProductEditor::money($v['price_minor']), 'stock' => $v['stock']]));
                    }
                    $state['variants'] = $rows->values()->all();
                }
                $editor->save($product, $state);
            }

            return count($products);
        });
    }
}
