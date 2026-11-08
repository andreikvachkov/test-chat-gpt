<?php

namespace App\Filament\Resources;

use App\Models\Category;
use Filament\Forms\Components\Select;
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
        return [TextInput::make('name')->label('Название')->required()->maxLength(255), TextInput::make('slug')->label('Адрес')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true), Select::make('parent_id')->label('Родительская категория')->options(fn (?Category $record) => Category::when($record, fn ($q) => $q->whereKeyNot($record->id))->orderBy('name')->pluck('name', 'id'))->searchable()];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('parent.name')->label('Родительская категория')];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCategory::route('/')];
    }
}
