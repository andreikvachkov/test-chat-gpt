<?php

namespace App\Filament\Resources;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;

class ProductResource extends ShopResource
{
    protected static ?string $model = Product::class;

    public static function getNavigationLabel(): string
    {
        return 'Товары';
    }

    public static function getModelLabel(): string
    {
        return 'Товары';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Товары';
    }

    public static function fields(): array
    {
        return [Select::make('category_id')->label('Категория')->relationship('category', 'name')->searchable(), TextInput::make('name')->label('Название')->required(), TextInput::make('slug')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true), TextInput::make('external_id')->label('ID в 1С')->unique(ignoreRecord: true), Textarea::make('description')->label('Описание')->columnSpanFull(), Textarea::make('ingredients')->label('Состав')->columnSpanFull(), Textarea::make('usage')->label('Применение')->columnSpanFull(), Select::make('image')->label('Изображение')->options(fn () => static::imageOptions())->searchable(), Select::make('gallery')->label('Галерея')->multiple()->options(fn () => static::imageOptions())->searchable(), TextInput::make('badge')->label('Метка'), Toggle::make('published')->label('Опубликован')];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('category.name')->label('Категория'), TextColumn::make('published')->label('Опубликован')];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProduct::route('/')];
    }
}
