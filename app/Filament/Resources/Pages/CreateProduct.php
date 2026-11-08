<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\ProductEditor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(ProductEditor::class)->save(null, $data);
        } catch (ValidationException $e) {
            ProductResource::formErrors($e, $this->data);
        }
    }

    protected function getRedirectUrl(): string
    {
        return ProductResource::getUrl('edit', ['record' => $this->record]);
    }
}
