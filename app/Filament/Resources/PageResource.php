<?php

namespace App\Filament\Resources;

use App\Models\Page;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PageResource extends ShopResource
{
    protected static ?string $model = Page::class;

    public static function getNavigationLabel(): string
    {
        return 'Страницы';
    }

    public static function getModelLabel(): string
    {
        return 'Страницы';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Страницы';
    }

    public static function fields(): array
    {
        return [TextInput::make('title')->label('Заголовок')->required(), TextInput::make('slug')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true), Select::make('template')->label('Шаблон')->options(array_combine(['index', 'about', 'history', 'contact', 'blog', 'document', 'document2', 'document-gift-card', 'gift', 'catalog', 'product-page', 'lk-login', 'order', 'lk-personal-info', 'lk-order-page', '404'], ['Главная', 'О бренде', 'История', 'Контакты', 'Блог', 'Документ', 'Политика', 'Условия карт', 'Подарочные карты', 'Каталог', 'Товар', 'Вход', 'Оформление', 'Кабинет', 'Заказ', '404']))->required(), Textarea::make('seo_description')->label('Описание для поисковых систем'), KeyValue::make('content')->label('Переопределения блоков для этой страницы')->columnSpanFull(), Toggle::make('published')->label('Опубликована')];
    }

    public static function columns(): array
    {
        return [TextColumn::make('title')->label('Заголовок')->searchable(), TextColumn::make('slug')->searchable(), TextColumn::make('published')->label('Опубликована')];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('template', '!=', 'article');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePage::route('/')];
    }
}
