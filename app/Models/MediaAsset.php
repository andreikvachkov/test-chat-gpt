<?php

namespace App\Models;

use App\Services\MediaLibrary;
use App\Services\ProductMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaAsset extends Model
{
    protected $fillable = ['name', 'path', 'alt_text', 'caption', 'description'];

    public function getUrlAttribute(): string
    {
        return ProductMedia::url($this->path);
    }

    public function isInUse(): bool
    {
        if (str_starts_with($this->path, '/assets/')) {
            return true;
        }
        $url = $this->url;

        return Product::withTrashed()->where('image', $url)->orWhereJsonContains('gallery', $url)->exists()
            || ProductVariant::where('details->image', $url)->exists()
            || ContentEntry::whereIn('value', [$url, $this->path])->exists()
            || Page::whereRaw('content::text LIKE ?', ['%'.$this->path.'%'])->exists();
    }

    protected static function booted(): void
    {
        static::saving(fn (self $asset) => $asset->forceFill(MediaLibrary::metadata($asset->path)));
        static::deleting(function (self $asset) {
            if ($asset->isInUse()) {
                throw ValidationException::withMessages(['media' => 'Файл используется на сайте. Сначала замените его в материалах.']);
            }
        });
        static::deleted(fn (self $asset) => DB::afterCommit(fn () => Storage::disk('public')->delete(ProductMedia::fieldPath($asset->path))));
    }
}
