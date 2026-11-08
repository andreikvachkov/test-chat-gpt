<?php

namespace App\Services;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductEditor
{
    public static function money(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function minor(string $price): int
    {
        [$rubles, $kopecks] = array_pad(explode('.', str_replace(',', '.', $price)), 2, '');

        return ((int) $rubles * 100) + (int) str_pad($kopecks, 2, '0');
    }

    public function state(Product $product): array
    {
        $product = $product->fresh() ?? $product;
        $variants = $product->variants()->orderBy('id')->get()->map(fn ($v) => $this->variantState($v))->all();

        return [...$product->getAttributes(), 'status' => $product->status === 'scheduled' && $product->published_at?->lte(now()) ? 'published' : $product->status, 'published_at' => $product->published_at?->format('Y-m-d H:i:s'),
            'type' => in_array($product->type, ['simple', 'variable']) ? $product->type : 'simple', 'details' => $product->settings(),
            'image' => ProductMedia::fieldPath($product->image), 'gallery' => array_map(ProductMedia::fieldPath(...), $product->gallery ?? []),
            'attribute_config' => $product->attribute_config ?? [], 'category_ids' => $product->categories()->pluck('categories.id')->all(),
            'collection_ids' => $product->collections()->pluck('product_collections.id')->all(), 'variants' => $product->type === 'variable' ? $this->compatibleVariations($product->attribute_config ?? [], $variants) : [],
            'variation_cache' => $product->type === 'variable' ? collect($variants)->filter(fn ($row) => filled($row['combination_key'] ?? null))->keyBy('combination_key')->all() : [],
            'simple' => array_replace($variants[0] ?? [], ['price' => $variants[0]['price'] ?? '0.00', 'stock' => $variants[0]['stock'] ?? 0, 'name' => $variants[0]['name'] ?? $product->name]),
        ];
    }

    public function variantState(ProductVariant $variant): array
    {
        $details = ProductData::settings($variant->details, 'variable', true);
        $details['image'] = ProductMedia::fieldPath($details['image'] ?? null);

        return collect($variant->getAttributes())->only(['id', 'sku', 'article', 'name', 'stock', 'combination_key'])->all()
            + ['price' => self::money((int) $variant->getRawOriginal('price_minor')), 'sale_price' => $variant->sale_price_minor !== null ? self::money($variant->sale_price_minor) : null, 'details' => $details, 'active' => $variant->active, 'attribute_value_ids' => $variant->attribute_value_ids ?? []];
    }

    public function config(array $config): array
    {
        $config = array_values($config);
        Validator::make(['attribute_config' => $config], [
            'attribute_config' => 'array|max:10', 'attribute_config.*.attribute_id' => 'required|integer|distinct|exists:product_attributes,id',
            'attribute_config.*.value_ids' => 'required|array|min:1|max:100', 'attribute_config.*.value_ids.*' => 'required|integer|distinct|exists:attribute_values,id',
            'attribute_config.*.for_variations' => 'boolean', 'attribute_config.*.visible' => 'boolean',
        ])->validate();
        foreach ($config as $i => &$row) {
            $row['attribute_id'] = (int) $row['attribute_id'];
            $row['value_ids'] = array_values(array_unique(array_map('intval', $row['value_ids'])));
            $row['for_variations'] = (bool) ($row['for_variations'] ?? false);
            $row['visible'] = (bool) ($row['visible'] ?? true);
            if (AttributeValue::where('product_attribute_id', $row['attribute_id'])->whereIn('id', $row['value_ids'])->count() !== count($row['value_ids'])) {
                throw ValidationException::withMessages(["attribute_config.$i.value_ids" => 'Значения должны принадлежать выбранному атрибуту.']);
            }
        }

        return $config;
    }

    public function combinations(array $config): array
    {
        $rows = array_values(array_filter($this->config($config), fn ($row) => $row['for_variations']));
        if (! $rows) {
            throw ValidationException::withMessages(['attribute_config' => 'Выберите атрибуты и включите «Использовать для вариаций».']);
        }
        usort($rows, fn ($a, $b) => $a['attribute_id'] <=> $b['attribute_id']);
        $combinations = [[]];
        foreach ($rows as $row) {
            if (count($combinations) * count($row['value_ids']) > 100) {
                throw ValidationException::withMessages(['attribute_config' => 'Максимум 100 комбинаций на товар. Уменьшите число значений.']);
            }
            $next = [];
            foreach ($combinations as $combination) {
                foreach ($row['value_ids'] as $id) {
                    $next[] = [...$combination, $id];
                }
            }
            $combinations = $next;
        }
        $terms = AttributeValue::whereIn('id', array_merge(...$combinations))->get()->keyBy('id');

        return collect($combinations)->mapWithKeys(fn ($ids) => [implode('|', array_map(fn ($id) => $terms[$id]->product_attribute_id.':'.$id, $ids)) => [
            'name' => implode(' / ', array_map(fn ($id) => $terms[$id]->name, $ids)), 'attribute_value_ids' => $ids,
        ]])->all();
    }

    public function compatibleVariations(array $config, array $rows): array
    {
        $variationConfig = array_values(array_filter($config, fn ($row) => ! empty($row['for_variations'])));
        if (! $variationConfig) {
            return [];
        }
        try {
            $combinations = $this->combinations($variationConfig);
        } catch (ValidationException) {
            return [];
        }

        return array_filter($rows, fn ($row) => isset($combinations[$row['combination_key'] ?? '']));
    }

    public function generate(array $config, array $existing, string $sku, ?Product $product = null): array
    {
        $old = $product?->type === 'variable' ? ProductVariant::where('product_id', $product->id)->get()->mapWithKeys(fn ($v) => [$v->combination_key => $this->variantState($v)])->all() : [];
        foreach ($existing as $row) {
            if (filled($row['combination_key'] ?? null)) {
                $old[$row['combination_key']] = $row;
            }
        }
        $result = [];
        foreach ($this->combinations($config) as $key => $combination) {
            $result[] = ($old[$key] ?? ['sku' => Str::substr($sku, 0, 254 - strlen(implode('-', $combination['attribute_value_ids']))).'-'.implode('-', $combination['attribute_value_ids']), 'price' => '0.00', 'stock' => 0, 'active' => true])
                + ['combination_key' => $key, ...$combination, 'details' => ProductData::defaults('variable', true), 'sale_price' => null];
        }

        return $result;
    }

    public function save(?Product $product, array $data): Product
    {
        $data['type'] ??= 'simple';
        $data['details'] = ProductData::validate(array_replace(ProductData::defaults($data['type']), $product?->details ?? [], $data['details'] ?? []));
        $data['variants'] = array_values($data['variants'] ?? []);
        $data['sku'] = trim($data['sku'] ?? '');
        $priceRule = ['required', 'regex:/^\d{1,8}([.,]\d{1,2})?$/'];
        Validator::make($data, [
            'name' => 'required|string|max:255', 'slug' => ['required', 'regex:/^[a-z0-9-]+$/', Rule::unique('products')->ignore($product?->id)],
            'sku' => 'required|string|max:255', 'article' => 'nullable|string|max:255', 'type' => ['required', Rule::in(['simple', 'variable'])],
            'description' => 'nullable|string', 'short_description' => 'nullable|string', 'status' => ['required', Rule::in(array_keys(Product::STATUSES))],
            'published_at' => 'nullable|date', 'category_ids' => 'array', 'category_ids.*' => 'integer|distinct|exists:categories,id',
            'collection_ids' => 'array', 'collection_ids.*' => 'integer|distinct|exists:product_collections,id',
            'simple.price' => $data['type'] === 'simple' ? $priceRule : ['nullable'], 'simple.stock' => $data['type'] === 'simple' ? 'required|integer|min:-100000000|max:100000000' : 'nullable',
            'inventory_stock' => 'nullable|integer|min:-100000000|max:100000000',
            'variants' => $data['type'] === 'variable' ? 'required|array|min:1|max:100' : 'array',
        ])->validate();
        if (($data['inventory_stock'] ?? 0) < 0 && $data['details']['backorders'] === 'no') {
            throw ValidationException::withMessages(['inventory_stock' => 'Отрицательный остаток разрешён только для предзаказов.']);
        }
        $config = $this->config($data['attribute_config'] ?? []);
        if ($data['status'] === 'scheduled' && (empty($data['published_at']) || ! Carbon::parse($data['published_at'])->isFuture())) {
            throw ValidationException::withMessages(['published_at' => 'Укажите будущую дату отложенной публикации.']);
        }
        if ($data['type'] === 'variable') {
            Validator::make($data, [
                'variants.*.sku' => 'required|string|max:255', 'variants.*.article' => 'nullable|string|max:255',
                'variants.*.price' => $priceRule, 'variants.*.stock' => 'required|integer|min:-100000000|max:100000000', 'variants.*.active' => 'boolean',
                'variants.*.combination_key' => 'required|string|distinct', 'variants.*.id' => 'nullable|integer|distinct',
            ])->validate();
            $combinations = $this->combinations($config);
            foreach ($data['variants'] as $i => &$row) {
                $combination = $combinations[$row['combination_key']] ?? null;
                if (! $combination) {
                    throw ValidationException::withMessages(["variants.$i.name" => 'Атрибуты изменились. Сформируйте вариации заново.']);
                }
                $row = array_replace($row, $combination);
            }
            unset($row);
        }

        return DB::transaction(function () use ($product, $data, $config) {
            // Serialize catalogue writers across both SKU namespaces; checkout locks inventory separately.
            DB::select('SELECT pg_advisory_xact_lock(71438511)');
            if ($product) {
                $product = Product::withTrashed()->lockForUpdate()->findOrFail($product->id);
            }
            $rootSku = Str::lower($data['sku']);
            if (Product::withTrashed()->whereRaw('lower(sku) = ?', [$rootSku])->when($product, fn ($q) => $q->where('id', '!=', $product->id))->exists()
                || ProductVariant::whereRaw('lower(sku) = ?', [$rootSku])->when($product, fn ($q) => $q->where('product_id', '!=', $product->id))->exists()) {
                throw ValidationException::withMessages(['sku' => 'Этот SKU уже занят другим товаром или вариацией.']);
            }
            $rows = $data['type'] === 'simple'
                ? [['id' => $product?->variants()->orderBy('id')->value('id'), 'sku' => $data['sku'], 'article' => $data['article'] ?? null, 'name' => $data['simple']['name'] ?? $product?->variants()->orderBy('id')->value('name') ?? $data['name'], 'price' => $data['simple']['price'], 'stock' => $data['simple']['stock'], 'active' => true, 'combination_key' => null, 'attribute_value_ids' => [], 'details' => $data['details'], 'sale_price' => $data['simple']['sale_price'] ?? null]]
                : $data['variants'];
            $skus = [];
            foreach ($rows as $i => &$row) {
                $row['details'] = ProductData::validate(array_replace(ProductData::defaults('variable', true), $row['details'] ?? []), 'variants.'.$i.'.details');
                $this->validatePricing($row, $data['type'] === 'simple' ? 'simple' : 'variants.'.$i);
                if ($row['stock'] < 0 && $row['details']['backorders'] === 'no') {
                    throw ValidationException::withMessages([($data['type'] === 'simple' ? 'simple' : 'variants.'.$i).'.stock' => 'Отрицательный остаток разрешён только для предзаказов.']);
                }
                $row['sku'] = trim($row['sku']);
                $key = Str::lower($row['sku']);
                $id = $row['id'] ?? null;
                if ($id && (! $product || ! ProductVariant::where('product_id', $product->id)->whereKey($id)->exists())) {
                    throw ValidationException::withMessages(["variants.$i.id" => 'Вариация принадлежит другому товару.']);
                }
                if ($id && $data['type'] === 'variable' && ProductVariant::findOrFail($id)->combination_key !== $row['combination_key']) {
                    throw ValidationException::withMessages(["variants.$i.name" => 'Нельзя переименовать складскую позицию в другую комбинацию. Сформируйте вариации из атрибутов.']);
                }
                if (isset($skus[$key]) || ($data['type'] === 'variable' && $key === $rootSku)
                    || ProductVariant::whereRaw('lower(sku) = ?', [$key])->when($id, fn ($q) => $q->whereKeyNot($id))->exists()
                    || Product::withTrashed()->whereRaw('lower(sku) = ?', [$key])->when($data['type'] === 'simple' && $product, fn ($q) => $q->whereKeyNot($product->id))->exists()) {
                    throw ValidationException::withMessages([$data['type'] === 'simple' ? 'sku' : "variants.$i.sku" => 'SKU должен быть уникальным; SKU вариации отличается от SKU родительского товара.']);
                }
                $skus[$key] = true;
            }
            unset($row);
            $product ??= new Product;
            $product->fill(collect($data)->only(['name', 'slug', 'sku', 'article', 'type', 'description', 'short_description', 'status', 'published_at', 'details', 'inventory_stock'])->all());
            $product->attribute_config = $config;
            $product->image = ProductMedia::url($data['image'] ?? null);
            $product->gallery = array_values(array_filter(array_map(ProductMedia::url(...), $data['gallery'] ?? [])));
            $product->save();
            $product->categories()->sync($data['category_ids'] ?? []);
            $product->collections()->sync($data['collection_ids'] ?? []);
            $ids = [];
            foreach ($rows as $row) {
                $variant = isset($row['id']) ? ProductVariant::where('product_id', $product->id)->lockForUpdate()->findOrFail($row['id']) : new ProductVariant(['product_id' => $product->id]);
                $variant->fill(collect($row)->only(['sku', 'article', 'name', 'stock', 'active', 'combination_key', 'attribute_value_ids', 'details'])->all());
                $variant->price_minor = self::minor((string) $row['price']);
                $variant->sale_price_minor = filled($row['sale_price'] ?? null) ? self::minor((string) $row['sale_price']) : null;
                $variant->sale_starts_at = null;
                $variant->sale_ends_at = null;
                $variant->retired_at = null;
                $variant->save();
                $ids[] = $variant->id;
            }
            ProductVariant::where('product_id', $product->id)->whereNotIn('id', $ids)->update(['active' => false, 'retired_at' => now()]);

            return $product->fresh();
        }, 3);
    }

    private function validatePricing(array $row, string $prefix): void
    {
        $input = [];
        data_set($input, $prefix, $row);
        Validator::make($input, [$prefix.'.sale_price' => ['nullable', 'regex:/^\d{1,8}([.,]\d{1,2})?$/']])->validate();
        if (filled($row['sale_price'] ?? null) && self::minor((string) $row['sale_price']) >= self::minor((string) $row['price'])) {
            throw ValidationException::withMessages([$prefix.'.sale_price' => 'Цена распродажи должна быть ниже обычной цены.']);
        }

    }
}
