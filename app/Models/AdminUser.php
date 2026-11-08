<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'admin_role_id', 'active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'active' => 'boolean'];
    }

    public function role()
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    public function isOwner(): bool
    {
        return $this->active && $this->role?->isOwner();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->active && $this->role !== null;
    }

    public function hasPermission(string $section, string $action): bool
    {
        if (! $this->active || ! $this->role) {
            return false;
        }
        if ($this->role->isOwner()) {
            return true;
        }
        if (in_array($section, ['staff', 'roles'])) {
            return false;
        }

        return in_array('view', $this->role->permissions[$section] ?? []) && in_array($action, $this->role->permissions[$section] ?? []);
    }
}
