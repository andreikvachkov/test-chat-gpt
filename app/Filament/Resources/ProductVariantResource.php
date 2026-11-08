<?php

namespace App\Filament\Resources;

use App\Models\ProductVariant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;

class ProductVariantResource extends ShopResource
{
    protected static ?string $model = ProductVariant::class;

    public static function getNavigationLabel(): string
    {
        return 'Варианты и остатки';
    }

    public static function getModelLabel(): string
    {
        return 'Варианты и остатки';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Варианты и остатки';
    }

    public static function fields(): array
    {
        return [Select::make('product_id')->label('Товар')->relationship('product', 'name')->required()->searchable(), TextInput::make('name')->label('Вариант / объём')->required(), TextInput::make('sku')->label('Артикул')->required()->unique(ignoreRecord: true), TextInput::make('external_id')->label('ID предложения в 1С')->unique(ignoreRecord: true), TextInput::make('price_minor')->label('Цена в копейках')->numeric()->integer()->minValue(0)->maxValue(10000000000)->required(), TextInput::make('stock')->label('Остаток')->numeric()->integer()->minValue(0)->required(), Toggle::make('active')->label('Активен')];
    }

    public static function columns(): array
    {
        return [TextColumn::make('product.name')->label('Товар')->searchable(), TextColumn::make('sku')->searchable(), TextColumn::make('price_minor')->label('Цена, коп.')->sortable(), TextColumn::make('stock')->label('Остаток')];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProductVariant::route('/')];
    }
}
