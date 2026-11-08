<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ContentEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContentEntry extends ManageRecords
{
    protected static string $resource = ContentEntryResource::class;

    protected function getHeaderActions(): array
    {
        return ContentEntryResource::canCreate() ? [CreateAction::make()] : [];
    }
}
