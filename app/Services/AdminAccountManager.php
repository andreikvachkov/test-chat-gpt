<?php

namespace App\Services;

use App\Models\AdminRole;
use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminAccountManager
{
    public function save(AdminUser $actor, ?AdminUser $record, array $data): AdminUser
    {
        $data['email'] = Str::lower(trim($data['email'] ?? ''));
        Validator::make($data, ['name' => 'required|string|max:120', 'email' => ['required', 'email', 'max:255', Rule::unique('admin_users')->ignore($record?->id)],
            'admin_role_id' => 'required|integer|exists:admin_roles,id', 'active' => 'required|boolean',
            'password' => [$record ? 'nullable' : 'required', 'string', Password::min(12)->letters()->numbers()],
        ])->validate();

        return DB::transaction(function () use ($actor, $record, $data) {
            DB::select('SELECT pg_advisory_xact_lock(71438512)');
            abort_unless($actor->fresh()?->isOwner(), 403);
            $record = $record ? AdminUser::lockForUpdate()->findOrFail($record->id) : new AdminUser;
            $role = AdminRole::findOrFail($data['admin_role_id']);
            if ($record->exists && $record->isOwner() && (! $data['active'] || ! $role->isOwner())
                && ! AdminUser::where('active', true)->whereHas('role', fn ($q) => $q->where('code', 'owner'))->whereKeyNot($record->id)->exists()) {
                throw ValidationException::withMessages(['admin_role_id' => 'Нельзя заблокировать или понизить последнего активного владельца.']);
            }
            $record->fill(collect($data)->only(['name', 'email', 'admin_role_id', 'active'])->all());
            if (filled($data['password'] ?? null)) {
                $record->password = $data['password'];
            }
            if (! $record->active || filled($data['password'] ?? null)) {
                $record->remember_token = null;
            }
            $record->save();

            return $record;
        }, 3);
    }
}
