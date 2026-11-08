<?php

namespace App\Filament\Resources;

use App\Models\ProductCollection;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class ProductCollectionResource extends ShopResource
{
    protected static ?string $model = ProductCollection::class;

    public static function getNavigationLabel(): string
    {
        return 'Подборки';
    }

    public static function getModelLabel(): string
    {
        return 'подборка';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Подборки';
    }

    public static function fields(): array
    {
        return [TextInput::make('name')->label('Название')->required()->maxLength(255), TextInput::make('slug')->label('Адрес')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true)];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('slug')->label('Адрес')];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProductCollection::route('/')];
    }
}
