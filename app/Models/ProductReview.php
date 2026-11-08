<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    public const STATUSES = ['pending' => 'На модерации', 'approved' => 'Одобрен', 'rejected' => 'Отклонён'];

    protected $fillable = ['product_id', 'user_id', 'rating', 'body', 'status'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
