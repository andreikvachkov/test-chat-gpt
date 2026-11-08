<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductVariant extends ManageRecords
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return ProductVariantResource::canCreate() ? [CreateAction::make()] : [];
    }
}
