<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class Cart
{
    public function quantities(): array
    {
        return Session::get('cart', []);
    }

    public function set(int $id, int $quantity): void
    {
        $cart = $this->quantities();
        if ($quantity === 0) {
            unset($cart[$id]);
        } else {
            $variant = ProductVariant::with(['product' => fn ($q) => $q->withTrashed()])->find($id);
            if (! $variant || ! $variant->active || $variant->retired_at || ! $variant->product?->isVisible() || ! $variant->canSupply($quantity)) {
                throw ValidationException::withMessages(['cart' => 'Товар недоступен в указанном количестве.']);
            }
            if ($variant->product->settings()['sold_individually']) {
                $otherVariants = ProductVariant::where('product_id', $variant->product_id)->whereKeyNot($id)->pluck('id');
                if ($quantity + collect($cart)->only($otherVariants->all())->sum() > 1) {
                    throw ValidationException::withMessages(['cart' => 'Этот товар продаётся только по одной штуке в заказе.']);
                }
            }
            $cart[$id] = $quantity;
        }
        Session::put('cart', $cart);
    }

    public function summary(): array
    {
        $quantities = $this->quantities();
        $items = ProductVariant::with(['product' => fn ($q) => $q->withTrashed()])->whereIn('id', array_keys($quantities))->get()->map(fn ($v) => [
            'id' => $v->id, 'product_id' => $v->product_id, 'product_slug' => $v->product->slug, 'name' => $v->product->name, 'variant' => $v->name, 'image' => $v->product->image,
            'quantity' => $quantities[$v->id], 'price_minor' => $v->price_minor, 'available' => $v->active && ! $v->retired_at && $v->product?->isVisible() && $v->canSupply($quantities[$v->id]),
            'backorder_notice' => $v->backorderNotice($quantities[$v->id]),
        ])->values();

        return ['items' => $items, 'count' => $items->sum('quantity'), 'subtotal_minor' => $items->sum(fn ($i) => $i['price_minor'] * $i['quantity'])];
    }
}
