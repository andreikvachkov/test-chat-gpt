<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['number', 'user_id', 'checkout_token', 'status', 'payment_status', 'delivery_method', 'delivery_status', 'subtotal_minor', 'shipping_minor', 'name', 'email', 'phone', 'address', 'comment'];

    protected $hidden = ['checkout_token'];

    protected function casts(): array
    {
        return ['subtotal_minor' => 'integer', 'shipping_minor' => 'integer'];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
