<?php

namespace App\Policies;

use App\Models\AdminUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

abstract class ResourcePolicy
{
    protected string $section;

    protected function permits(Authenticatable $user, string $action): bool
    {
        return $user instanceof AdminUser && $user->hasPermission($this->section, $action);
    }

    public function viewAny(Authenticatable $user): bool
    {
        return $this->permits($user, 'view');
    }

    public function view(Authenticatable $user, Model $record): bool
    {
        return $this->permits($user, 'view');
    }

    public function create(Authenticatable $user): bool
    {
        return $this->permits($user, 'create');
    }

    public function update(Authenticatable $user, Model $record): bool
    {
        return $this->permits($user, 'update');
    }

    public function delete(Authenticatable $user, Model $record): bool
    {
        return $this->permits($user, 'delete');
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return $this->permits($user, 'delete');
    }

    public function restore(Authenticatable $user, Model $record): bool
    {
        return $this->permits($user, 'restore');
    }

    public function restoreAny(Authenticatable $user): bool
    {
        return $this->permits($user, 'restore');
    }

    public function forceDelete(Authenticatable $user, Model $record): bool
    {
        return false;
    }

    public function forceDeleteAny(Authenticatable $user): bool
    {
        return false;
    }
}
