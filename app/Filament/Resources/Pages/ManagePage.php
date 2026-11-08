<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePage extends ManageRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return PageResource::canCreate() ? [CreateAction::make()] : [];
    }
}
