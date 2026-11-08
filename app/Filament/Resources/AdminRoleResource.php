<?php

namespace App\Filament\Resources;

use App\Models\AdminRole;
use App\Support\AdminPermissions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminRoleResource extends ShopResource
{
    protected static ?string $model = AdminRole::class;

    protected static ?string $slug = 'roles';

    public static function getNavigationLabel(): string
    {
        return 'Роли и права';
    }

    public static function getModelLabel(): string
    {
        return 'роль';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Роли и права';
    }

    public static function fields(): array
    {
        $fields = [TextInput::make('name')->label('Название роли')->required()->maxLength(120)->columnSpanFull(),
            Placeholder::make('owner_permissions')->label('Полный доступ')->content('Владелец управляет всем магазином, сотрудниками и ролями. Эту системную роль изменять нельзя.')->visible(fn (?AdminRole $record) => $record?->isOwner())->columnSpanFull()];
        foreach (AdminPermissions::GROUPS as $group => $sections) {
            $fields[] = Section::make($group)->description('Для любых действий в разделе включите «Просмотр».')
                ->schema(array_map(fn ($label, $section) => CheckboxList::make('permissions.'.$section)->label($label)->options(AdminPermissions::actions($section))->default([])->columns(3)->rules([fn () => function ($attribute, $value, $fail) {
                    if ($value && ! in_array('view', $value)) {
                        $fail('Для действий в разделе включите «Просмотр».');
                    }
                }]), array_values($sections), array_keys($sections)))
                ->visible(fn (?AdminRole $record) => ! $record?->isOwner())->columnSpanFull();
        }

        return $fields;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Роль')->searchable(), TextColumn::make('users_count')->label('Сотрудников')->counts('users')])
            ->recordActions([ViewAction::make(), EditAction::make()->url(fn ($record) => static::getUrl('edit', ['record' => $record])), DeleteAction::make()])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAdminRoles::route('/'), 'create' => Pages\CreateAdminRole::route('/create'), 'edit' => Pages\EditAdminRole::route('/{record}/edit')];
    }
}
