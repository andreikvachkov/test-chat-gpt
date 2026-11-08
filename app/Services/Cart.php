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
            $variant = ProductVariant::with('product')->find($id);
            if (! $variant || ! $variant->active || ! $variant->product->published || $variant->stock < $quantity) {
                throw ValidationException::withMessages(['cart' => 'Товар недоступен в указанном количестве.']);
            }
            $cart[$id] = $quantity;
        }
        Session::put('cart', $cart);
    }

    public function summary(): array
    {
        $quantities = $this->quantities();
        $items = ProductVariant::with('product')->whereIn('id', array_keys($quantities))->get()->map(fn ($v) => [
            'id' => $v->id, 'product_slug' => $v->product->slug, 'name' => $v->product->name, 'variant' => $v->name, 'image' => $v->product->image,
            'quantity' => $quantities[$v->id], 'price_minor' => $v->price_minor, 'available' => $v->active && $v->product->published && $v->stock >= $quantities[$v->id],
        ])->values();

        return ['items' => $items, 'count' => $items->sum('quantity'), 'subtotal_minor' => $items->sum(fn ($i) => $i['price_minor'] * $i['quantity'])];
    }
}
