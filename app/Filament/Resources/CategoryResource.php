<?php

namespace App\Filament\Resources;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class CategoryResource extends ShopResource
{
    protected static ?string $model = Category::class;

    public static function getNavigationLabel(): string
    {
        return 'Категории';
    }

    public static function getModelLabel(): string
    {
        return 'Категории';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Категории';
    }

    public static function fields(): array
    {
        return [TextInput::make('name')->label('Название')->required(), TextInput::make('slug')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true), TextInput::make('external_id')->label('ID в 1С')->unique(ignoreRecord: true)];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('external_id')->label('ID в 1С')];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCategory::route('/')];
    }
}
