<?php

namespace App\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AdminUserPolicy extends ResourcePolicy
{
    protected string $section = 'staff';

    public function delete(Authenticatable $user, Model $record): bool
    {
        return false;
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return false;
    }
}
