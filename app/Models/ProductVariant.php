<?php

namespace App\Models;

use App\Services\ProductData;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'external_id', 'sku', 'article', 'name', 'price_minor', 'stock', 'active', 'attribute_value_ids', 'combination_key', 'retired_at', 'details', 'sale_price_minor', 'sale_starts_at', 'sale_ends_at'];

    protected $appends = ['available', 'max_quantity'];

    protected $hidden = ['external_id', 'product', 'details'];

    protected function casts(): array
    {
        return ['price_minor' => 'integer', 'stock' => 'integer', 'active' => 'boolean', 'attribute_value_ids' => 'array', 'retired_at' => 'datetime', 'details' => 'array', 'sale_price_minor' => 'integer', 'sale_starts_at' => 'datetime', 'sale_ends_at' => 'datetime'];
    }

    protected function priceMinor(): Attribute
    {
        return Attribute::get(fn ($value) => $this->sale_price_minor !== null && (! $this->sale_starts_at || $this->sale_starts_at->lte(now())) && (! $this->sale_ends_at || $this->sale_ends_at->gte(now())) ? $this->sale_price_minor : (int) $value);
    }

    public function settings(): array
    {
        return $this->product?->type === 'simple' ? $this->product->settings() : array_replace(ProductData::defaults('variable', true), $this->details ?? []);
    }

    public function stockOwner(): Product|self|null
    {
        if ($this->settings()['manage_stock']) {
            return $this;
        }

        return $this->product?->type === 'variable' && $this->product->settings()['manage_stock'] ? $this->product : null;
    }

    public function canSupply(int $quantity): bool
    {
        if ($quantity < 1 || ($this->product?->settings()['sold_individually'] && $quantity > 1)) {
            return false;
        }
        $owner = $this->stockOwner();
        if (! $owner) {
            return $this->settings()['stock_status'] !== 'outofstock';
        }
        $settings = $owner instanceof Product ? $owner->settings() : $this->settings();
        $stock = $owner instanceof Product ? $owner->inventory_stock : $owner->stock;

        return $stock >= $quantity || in_array($settings['backorders'], ['yes', 'notify']);
    }

    public function getAvailableAttribute(): bool
    {
        return $this->active && ! $this->retired_at && $this->canSupply(1);
    }

    public function backorderNotice(int $quantity): bool
    {
        $owner = $this->stockOwner();
        $settings = $owner instanceof Product ? $owner->settings() : $this->settings();
        $stock = $owner instanceof Product ? $owner->inventory_stock : ($owner?->stock ?? 0);

        return $settings['stock_status'] === 'onbackorder' || ($owner && $settings['backorders'] === 'notify' && $stock < $quantity);
    }

    public function getMaxQuantityAttribute(): int
    {
        if (! $this->available) {
            return 0;
        }
        if ($this->product?->settings()['sold_individually']) {
            return 1;
        }
        $owner = $this->stockOwner();
        $settings = $owner instanceof Product ? $owner->settings() : $this->settings();

        return ! $owner || $settings['backorders'] !== 'no' ? 99 : min(99, $owner instanceof Product ? $owner->inventory_stock : $owner->stock);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
