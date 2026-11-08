<?php

namespace App\Filament\Resources;

use App\Models\Category;
use App\Services\CategoryTree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

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
        return [TextInput::make('name')->label('Название')->required()->maxLength(255), TextInput::make('slug')->label('Адрес')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true), Select::make('parent_id')->label('Родительская категория')->placeholder('Нет — родительская категория')->options(fn (?Category $record) => CategoryTree::options($record))->searchable()];
    }

    public static function columns(): array
    {
        return [TextColumn::make('name')->label('Название')->searchable()->formatStateUsing(fn ($state, Category $record) => new HtmlString('<span style="display:inline-block;padding-inline-start:'.((int) $record->depth * 20).'px">'.str_repeat('— ', (int) $record->depth).e($state).'</span>')), TextColumn::make('parent.name')->label('Родительская категория')->placeholder('Родительская категория')];
    }

    public static function getEloquentQuery(): Builder
    {
        return CategoryTree::query();
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->defaultSort('hierarchy.sort_path')->paginated(false);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCategory::route('/')];
    }
}
