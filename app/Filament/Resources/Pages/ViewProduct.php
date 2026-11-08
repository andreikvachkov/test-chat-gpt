<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\ProductEditor;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return app(ProductEditor::class)->state($this->record);
    }
}
