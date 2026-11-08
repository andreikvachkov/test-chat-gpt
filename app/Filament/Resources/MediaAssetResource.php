<?php

namespace App\Filament\Resources;

use App\Models\MediaAsset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class MediaAssetResource extends ShopResource
{
    protected static ?string $model = MediaAsset::class;

    public static function getNavigationLabel(): string
    {
        return 'Медиатека';
    }

    public static function getModelLabel(): string
    {
        return 'Медиатека';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Медиатека';
    }

    public static function fields(): array
    {
        return [TextInput::make('name')->label('Название')->required(), FileUpload::make('path')->label('Изображение')->image()->disk('public')->directory('content')->visibility('public')->maxSize(10240)->required()];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('path')->label('Файл')];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMediaAsset::route('/')];
    }
}
