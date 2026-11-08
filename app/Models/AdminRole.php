<?php

namespace App\Models;

use App\Support\AdminPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminRole extends Model
{
    protected $fillable = ['name', 'permissions'];

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }

    public function users()
    {
        return $this->hasMany(AdminUser::class);
    }

    public function isOwner(): bool
    {
        return $this->code === 'owner';
    }

    protected static function booted(): void
    {
        static::creating(function (self $role) {
            $role->code ??= 'custom-'.Str::lower((string) Str::ulid());
        });
        static::saving(function (self $role) {
            if ($role->exists && ($role->getOriginal('code') === 'owner' || $role->isDirty('code'))) {
                throw ValidationException::withMessages(['name' => 'Системную роль владельца и код существующей роли изменять нельзя.']);
            }
            $permissions = $role->permissions ?? [];
            if (! is_array($permissions)) {
                throw ValidationException::withMessages(['permissions' => 'Некорректные права.']);
            }
            foreach ($permissions as $section => $actions) {
                if (! array_key_exists($section, AdminPermissions::all()) || ! is_array($actions) || array_diff($actions, array_keys(AdminPermissions::actions($section)))) {
                    throw ValidationException::withMessages(['permissions' => 'Неизвестное разрешение.']);
                }
                if ($actions && ! in_array('view', $actions)) {
                    throw ValidationException::withMessages(['permissions.'.$section => 'Для действий в разделе включите «Просмотр».']);
                }
            }
            $role->permissions = array_map(fn ($actions) => array_values(array_unique($actions)), $permissions);
        });
        static::deleting(function (self $role) {
            if ($role->isOwner() || $role->users()->exists()) {
                throw ValidationException::withMessages(['role' => 'Роль владельца и роли, назначенные сотрудникам, удалить нельзя.']);
            }
        });
    }
}
