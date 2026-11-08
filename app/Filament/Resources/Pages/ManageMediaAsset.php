<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\MediaAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMediaAsset extends ManageRecords
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return MediaAssetResource::canCreate() ? [CreateAction::make()] : [];
    }
}
