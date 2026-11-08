<?php

namespace App\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class MediaAssetPolicy extends ResourcePolicy
{
    protected string $section = 'media';

    public function delete(Authenticatable $user, Model $record): bool
    {
        return ! $record->isInUse() && parent::delete($user, $record);
    }
}
