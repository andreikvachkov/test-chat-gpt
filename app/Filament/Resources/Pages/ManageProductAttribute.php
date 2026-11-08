<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ProductAttributeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductAttribute extends ManageRecords
{
    protected static string $resource = ProductAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
