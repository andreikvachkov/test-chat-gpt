<?php

namespace App\Filament\Resources;

use App\Models\AdminRole;
use App\Models\AdminUser;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

class AdminUserResource extends ShopResource
{
    protected static ?string $model = AdminUser::class;

    protected static ?string $slug = 'staff';

    public static function getNavigationLabel(): string
    {
        return 'Сотрудники';
    }

    public static function getModelLabel(): string
    {
        return 'сотрудник';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Сотрудники';
    }

    public static function fields(): array
    {
        return [TextInput::make('name')->label('Имя')->required()->maxLength(120),
            TextInput::make('email')->label('Почта для входа')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
            Select::make('admin_role_id')->label('Роль')->options(fn () => AdminRole::orderBy('id')->pluck('name', 'id'))->required()->helperText('Права задаются в разделе «Роли и права».'),
            Toggle::make('active')->label('Доступ в админку включён')->default(true),
            TextInput::make('password')->label('Пароль')->password()->revealable()->autocomplete('new-password')->required(fn (string $operation) => $operation === 'create')
                ->rules([Password::min(12)->letters()->numbers()])->dehydrated(fn ($state) => filled($state))->helperText('Не менее 12 символов, буквы и цифры. При редактировании оставьте пустым, чтобы сохранить пароль.'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Имя')->searchable(), TextColumn::make('email')->label('Почта')->searchable(), TextColumn::make('role.name')->label('Роль')->badge(), IconColumn::make('active')->label('Доступ включён')->boolean()])
            ->recordActions([Action::make('edit')->label('Редактировать')->icon('heroicon-m-pencil-square')->authorize(fn ($record) => static::canEdit($record))->url(fn ($record) => static::getUrl('edit', ['record' => $record]))])
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))->defaultSort('id', 'desc');
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function allowDeletion(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAdminUsers::route('/'), 'create' => Pages\CreateAdminUser::route('/create'), 'edit' => Pages\EditAdminUser::route('/{record}/edit')];
    }
}
