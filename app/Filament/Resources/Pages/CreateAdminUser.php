<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\AdminUserResource;
use App\Services\AdminAccountManager;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(AdminAccountManager::class)->save(Auth::guard('admin')->user(), null, $data);
        } catch (ValidationException $e) {
            throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($messages, $key) => ['data.'.$key => $messages])->all());
        }
    }

    protected function getRedirectUrl(): string
    {
        return AdminUserResource::getUrl('edit', ['record' => $this->record]);
    }
}
