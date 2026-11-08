<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProduct extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return ProductResource::canCreate() ? [CreateAction::make()] : [];
    }
}
