<?php

namespace App\Filament\Resources;

use App\Models\Product;
use App\Models\ProductAttribute;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class ProductAttributeResource extends ShopResource
{
    protected static ?string $model = ProductAttribute::class;

    public static function canDelete($record): bool
    {
        return ! Product::withTrashed()->whereJsonContains('attribute_config', [['attribute_id' => $record->id]])->exists();
    }

    public static function getNavigationLabel(): string
    {
        return 'Атрибуты';
    }

    public static function getModelLabel(): string
    {
        return 'атрибут';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Атрибуты';
    }

    public static function fields(): array
    {
        return [TextInput::make('name')->label('Название')->required()->maxLength(255), TextInput::make('slug')->label('Адрес')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true),
            Repeater::make('values')->label('Значения атрибута')->relationship()->schema([TextInput::make('name')->label('Значение')->required()->maxLength(255), TextInput::make('slug')->label('Код значения')->required()->regex('/^[a-z0-9-]+$/')->distinct()])->columns(2)->addActionLabel('Добавить значение')->columnSpanFull(),
        ];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Название')->searchable(), TextColumn::make('values.name')->label('Значения')->badge()];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProductAttribute::route('/')];
    }
}
