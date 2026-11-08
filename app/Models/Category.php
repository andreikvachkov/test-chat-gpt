<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Category extends Model
{
    protected $fillable = ['external_id', 'name', 'slug', 'parent_id'];

    protected $hidden = ['external_id'];

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            $parent = $category->parent_id;
            $seen = [$category->id];
            while ($parent) {
                if (in_array($parent, $seen)) {
                    throw ValidationException::withMessages(['parent_id' => 'Категории не могут образовывать замкнутую цепочку.']);
                }
                $seen[] = $parent;
                $parent = self::find($parent)?->parent_id;
            }
        });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
