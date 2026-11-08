<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOrder extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return OrderResource::canCreate() ? [CreateAction::make()] : [];
    }
}
