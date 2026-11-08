<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Checkout
{
    public function place(User $user, array $data, array $quantities): Order
    {
        return DB::transaction(function () use ($user, $data, $quantities) {
            // Serialize simultaneous submissions by this customer, including retries.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($existing = $user->orders()->where('checkout_token', $data['checkout_token'])->first()) {
                return $existing;
            }
            if (! $quantities) {
                throw ValidationException::withMessages(['cart' => 'Корзина пуста.']);
            }
            $productIds = ProductVariant::whereIn('id', array_keys($quantities))->pluck('product_id')->unique();
            $products = Product::withTrashed()->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $variants = ProductVariant::whereIn('id', array_keys($quantities))->orderBy('id')->lockForUpdate()->get();
            foreach ($variants as $variant) {
                $variant->setRelation('product', $products->get($variant->product_id));
            }
            if ($variants->count() !== count($quantities)) {
                throw ValidationException::withMessages(['cart' => 'Состав корзины изменился.']);
            }
            $subtotal = 0;
            $reservations = [];
            foreach ($variants as $v) {
                $q = $quantities[$v->id];
                if (! is_int($q) || $q < 1 || $q > 99 || ! $v->active || $v->retired_at || ! $v->product?->isVisible() || ! $v->canSupply($q)) {
                    throw ValidationException::withMessages(['cart' => 'Недостаточно товара: '.$v->product->name]);
                }$subtotal += $v->price_minor * $q;
                if ($owner = $v->stockOwner()) {
                    $key = $owner->getTable().':'.$owner->id;
                    $reservations[$key] ??= ['owner' => $owner, 'quantity' => 0, 'settings' => $owner instanceof Product ? $owner->settings() : $v->settings()];
                    $reservations[$key]['quantity'] += $q;
                }
            }
            foreach ($reservations as $reservation) {
                $owner = $reservation['owner'];
                $stock = $owner instanceof Product ? $owner->inventory_stock : $owner->stock;
                if ($reservation['settings']['backorders'] === 'no' && $stock < $reservation['quantity']) {
                    throw ValidationException::withMessages(['cart' => 'Недостаточно общего остатка товара.']);
                }
            }
            // The customer must confirm the current server prices, never a client-supplied total.
            if ((int) $data['expected_subtotal_minor'] !== $subtotal) {
                throw ValidationException::withMessages(['cart' => 'Цена изменилась. Проверьте корзину и подтвердите заказ снова.']);
            }
            $order = $user->orders()->create(collect($data)->only(['checkout_token', 'name', 'email', 'phone', 'address', 'comment', 'delivery_method'])->all() + ['number' => (string) Str::uuid(), 'subtotal_minor' => $subtotal]);
            foreach ($variants as $v) {
                $q = $quantities[$v->id];
                $owner = $v->stockOwner();
                $poolQuantity = $owner ? $reservations[$owner->getTable().':'.$owner->id]['quantity'] : $q;
                $order->items()->create(['product_variant_id' => $v->id, 'name' => $v->product->name.' — '.$v->name, 'sku' => $v->sku, 'price_minor' => $v->price_minor, 'quantity' => $q, 'backordered' => $v->backorderNotice($poolQuantity)]);
            }
            foreach ($reservations as $reservation) {
                $owner = $reservation['owner'];
                $owner->decrement($owner instanceof Product ? 'inventory_stock' : 'stock', $reservation['quantity']);
            }

            return $order;
        }, 3);
    }
}
