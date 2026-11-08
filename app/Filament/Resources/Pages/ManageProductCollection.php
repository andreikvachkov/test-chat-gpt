<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ProductCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductCollection extends ManageRecords
{
    protected static string $resource = ProductCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
