<?php

namespace App\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AdminRolePolicy extends ResourcePolicy
{
    protected string $section = 'roles';

    public function update(Authenticatable $user, Model $record): bool
    {
        return ! $record->isOwner() && parent::update($user, $record);
    }

    public function delete(Authenticatable $user, Model $record): bool
    {
        return ! $record->isOwner() && ! $record->users()->exists() && parent::delete($user, $record);
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return false;
    }
}
