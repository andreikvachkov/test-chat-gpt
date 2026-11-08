<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\ProductEditor;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return app(ProductEditor::class)->state($this->record);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(ProductEditor::class)->save($record, $data);
        } catch (ValidationException $e) {
            ProductResource::formErrors($e, $this->data);
        }
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }

    protected function afterSave(): void
    {
        $this->fillForm();
    }
}
