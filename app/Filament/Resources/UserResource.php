<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class UserResource extends ShopResource
{
    protected static ?string $model = User::class;

    public static function getNavigationLabel(): string
    {
        return 'Покупатели';
    }

    public static function getModelLabel(): string
    {
        return 'Покупатели';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Покупатели';
    }

    public static function fields(): array
    {
        return [TextInput::make('name')->label('Имя')->required(), TextInput::make('email')->email()->required()->unique(ignoreRecord: true), TextInput::make('phone')->label('Телефон')];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Имя')->searchable(), TextColumn::make('email')->searchable(), TextColumn::make('phone')->label('Телефон')];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageUser::route('/')];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function allowDeletion(): bool
    {
        return false;
    }
}
