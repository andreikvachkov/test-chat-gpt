<?php

namespace App\Filament\Resources;

use App\Models\Page;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;

class ArticleResource extends ShopResource
{
    protected static ?string $model = Page::class;

    protected static ?string $slug = 'articles';

    public static function getNavigationLabel(): string
    {
        return 'Статьи';
    }

    public static function getModelLabel(): string
    {
        return 'статья';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Статьи';
    }

    public static function getViewAnyAuthorizationResponse(): Response
    {
        return Filament::auth()->user()?->hasPermission('articles', 'view') ? Response::allow() : Response::deny();
    }

    public static function getCreateAuthorizationResponse(): Response
    {
        return Filament::auth()->user()?->hasPermission('articles', 'create') ? Response::allow() : Response::deny();
    }

    public static function fields(): array
    {
        return array_map(fn ($field) => $field->getName() === 'template'
            ? Hidden::make('template')->default('article')->dehydrateStateUsing(fn () => 'article')
            : $field, PageResource::fields());
    }

    public static function columns(): array
    {
        return PageResource::columns();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('template', 'article');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageArticle::route('/')];
    }
}
