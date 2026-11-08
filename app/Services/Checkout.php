<?php

namespace App\Services;

use App\Models\Order;
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
            $variants = ProductVariant::with(['product' => fn ($q) => $q->withTrashed()])->whereIn('id', array_keys($quantities))->orderBy('id')->lockForUpdate()->get();
            if ($variants->count() !== count($quantities)) {
                throw ValidationException::withMessages(['cart' => 'Состав корзины изменился.']);
            }
            $subtotal = 0;
            foreach ($variants as $v) {
                $q = $quantities[$v->id];
                if (! is_int($q) || $q < 1 || $q > 99 || ! $v->active || $v->retired_at || ! $v->product?->isVisible() || $v->stock < $q) {
                    throw ValidationException::withMessages(['cart' => 'Недостаточно товара: '.$v->product->name]);
                }$subtotal += $v->price_minor * $q;
            }
            // The customer must confirm the current server prices, never a client-supplied total.
            if ((int) $data['expected_subtotal_minor'] !== $subtotal) {
                throw ValidationException::withMessages(['cart' => 'Цена изменилась. Проверьте корзину и подтвердите заказ снова.']);
            }
            $order = $user->orders()->create(collect($data)->only(['checkout_token', 'name', 'email', 'phone', 'address', 'comment', 'delivery_method'])->all() + ['number' => (string) Str::uuid(), 'subtotal_minor' => $subtotal]);
            foreach ($variants as $v) {
                $q = $quantities[$v->id];
                $order->items()->create(['product_variant_id' => $v->id, 'name' => $v->product->name.' — '.$v->name, 'sku' => $v->sku, 'price_minor' => $v->price_minor, 'quantity' => $q]);
                $v->decrement('stock', $q);
            }

            return $order;
        }, 3);
    }
}
