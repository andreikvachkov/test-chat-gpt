<?php

namespace App\Models;

use App\Services\ProductData;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft' => 'Черновик', 'pending' => 'На утверждении', 'private' => 'Личный', 'published' => 'Опубликован', 'scheduled' => 'Запланирован'];

    protected $fillable = ['category_id', 'external_id', 'name', 'slug', 'description', 'short_description', 'ingredients', 'usage', 'image', 'gallery', 'badge', 'published', 'sku', 'article', 'type', 'attribute_config', 'status', 'published_at', 'details', 'inventory_stock'];

    protected $attributes = ['type' => 'simple'];

    protected $hidden = ['details', 'inventory_stock', 'external_id', 'ingredients', 'usage', 'badge', 'category_id'];

    protected $appends = ['description_html', 'reviews_enabled'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'gallery' => 'array', 'attribute_config' => 'array', 'published_at' => 'datetime', 'details' => 'array', 'inventory_stock' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->isDirty('published') && ! $product->isDirty('status')) {
                $product->status = $product->published ? 'published' : 'draft';
            }
            if ($product->status === 'published' && $product->published_at?->isFuture()) {
                $product->status = 'scheduled';
            }
            if (in_array($product->status, ['published', 'scheduled']) && ! $product->published_at) {
                $product->published_at = now();
            }
            $product->published = in_array($product->status, ['published', 'scheduled']);
        });
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('published', true)->whereIn('status', ['published', 'scheduled'])->where('published_at', '<=', now());
    }

    public function isVisible(): bool
    {
        return ! $this->trashed() && $this->published && in_array($this->status, ['published', 'scheduled']) && $this->published_at?->lte(now());
    }

    public function getDescriptionHtmlAttribute(): string
    {
        if (blank($this->description)) {
            return '';
        }

        return RichContentRenderer::make($this->description ?? '')->toHtml();
    }

    public function getReviewsEnabledAttribute(): bool
    {
        return true;
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->whereNull('retired_at');
    }

    public function settings(): array
    {
        return ProductData::settings($this->details, $this->type);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function collections()
    {
        return $this->belongsToMany(ProductCollection::class, 'collection_product');
    }
}
