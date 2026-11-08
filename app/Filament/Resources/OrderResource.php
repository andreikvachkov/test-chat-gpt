<?php

namespace App\Filament\Resources;

use App\Models\Order;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class OrderResource extends ShopResource
{
    protected static ?string $model = Order::class;

    public static function getNavigationLabel(): string
    {
        return 'Заказы';
    }

    public static function getModelLabel(): string
    {
        return 'Заказы';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Заказы';
    }

    public static function fields(): array
    {
        return [TextInput::make('number')->label('Номер')->disabled(), TextInput::make('name')->label('Получатель')->disabled(), TextInput::make('email')->disabled(), TextInput::make('phone')->label('Телефон')->disabled(), Textarea::make('address')->label('Адрес')->disabled(), Textarea::make('comment')->label('Комментарий')->disabled(), TextInput::make('subtotal_minor')->label('Товары, коп.')->disabled(), TextInput::make('shipping_minor')->label('Доставка, коп.')->numeric()->integer()->minValue(0), Select::make('status')->label('Статус')->options(['new' => 'Новый', 'processing' => 'В работе', 'shipped' => 'Отправлен', 'completed' => 'Выполнен'])->required(), TextInput::make('payment_status')->label('Оплата')->disabled(), Select::make('delivery_status')->label('Согласование доставки')->options(['quote_required' => 'Ожидает расчёта', 'confirmed' => 'Согласована'])->required(), Placeholder::make('items_summary')->label('Состав заказа')->content(fn ($record) => $record?->items->map(fn ($i) => $i->name.' / '.$i->sku.' × '.$i->quantity.' / '.$i->price_minor.' коп.')->implode('; '))];
    }

    public static function columns(): array
    {
        return [TextColumn::make('number')->label('Номер')->searchable()->limit(12), TextColumn::make('name')->label('Покупатель')->searchable(), TextColumn::make('subtotal_minor')->label('Товары, коп.'), TextColumn::make('status')->label('Статус'), TextColumn::make('created_at')->label('Создан')->dateTime()];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageOrder::route('/')];
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
