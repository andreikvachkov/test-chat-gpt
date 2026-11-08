<?php

namespace App\Policies;

use App\Models\AdminUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class PagePolicy extends ResourcePolicy
{
    protected string $section = 'pages';

    public function view(Authenticatable $user, Model $record): bool
    {
        return $this->permitsRecord($user, $record, 'view');
    }

    public function update(Authenticatable $user, Model $record): bool
    {
        return $this->permitsRecord($user, $record, 'update');
    }

    public function delete(Authenticatable $user, Model $record): bool
    {
        return $this->permitsRecord($user, $record, 'delete');
    }

    private function permitsRecord(Authenticatable $user, Model $record, string $action): bool
    {
        return $user instanceof AdminUser && $user->hasPermission($record->template === 'article' ? 'articles' : 'pages', $action);
    }
}
