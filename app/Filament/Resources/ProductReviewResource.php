<?php

namespace App\Filament\Resources;

use App\Models\ProductReview;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductReviewResource extends ShopResource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $slug = 'product-reviews';

    public static function getNavigationLabel(): string
    {
        return 'Отзывы';
    }

    public static function getModelLabel(): string
    {
        return 'отзыв';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Отзывы';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function fields(): array
    {
        return [
            Placeholder::make('product_name')->label('Товар')->content(fn ($record) => $record?->product?->name),
            Placeholder::make('customer_name')->label('Клиент')->content(fn ($record) => $record?->user?->name ?? 'Аккаунт удалён'),
            Placeholder::make('rating')->label('Оценка')->content(fn ($record) => $record?->rating.' / 5'),
            Textarea::make('body')->label('Текст отзыва')->disabled()->dehydrated(false)->columnSpanFull(),
            Select::make('status')->label('Статус')->options(ProductReview::STATUSES)->required(),
        ];
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('product.name')->label('Товар')->searchable(),
            TextColumn::make('user.name')->label('Клиент')->searchable(),
            TextColumn::make('rating')->label('Оценка')->sortable(),
            TextColumn::make('body')->label('Отзыв')->limit(100),
            TextColumn::make('status')->label('Статус')->formatStateUsing(fn ($state) => ProductReview::STATUSES[$state] ?? $state)->badge(),
            TextColumn::make('created_at')->label('Добавлен')->dateTime('d.m.Y H:i')->sortable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([SelectFilter::make('status')->label('Статус')->options(ProductReview::STATUSES)]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProductReview::route('/')];
    }
}
