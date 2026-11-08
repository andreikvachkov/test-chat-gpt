<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\MediaAssetResource;
use App\Services\MediaLibrary;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ManageRecords;

class ManageMediaAsset extends ManageRecords
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('uploadFiles')->label('Добавить медиафайлы')->authorize(fn () => MediaAssetResource::canCreate())->modalHeading('Загрузить файлы')->modalSubmitActionLabel('Добавить')->schema([
            FileUpload::make('files')->label('Перетащите файлы или выберите с компьютера')->multiple()->required()->maxFiles(20)->maxSize(10240)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'application/pdf', 'video/mp4', 'video/webm'])->storeFiles(false),
        ])->action(function (array $data, Action $action) {
            foreach ($data['files'] as $file) {
                MediaLibrary::upload($file);
            }
            $this->resetTable();
            $action->success();
        })->successNotificationTitle('Файлы добавлены')];
    }
}
