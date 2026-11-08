<?php

namespace App\Filament\Forms;

use App\Filament\Resources\ProductResource;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ShippingClass;
use App\Services\ProductEditor;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductDataFields
{
    public static function make(): Section
    {
        return Section::make('Данные товара')->extraAttributes(['class' => 'kamali-product-data'])->schema([
            Select::make('type')->label('Тип товара')->options(['simple' => 'Простой товар', 'variable' => 'Вариативный товар'])->default('simple')->selectablePlaceholder(false)->required()->live()
                ->afterStateHydrated(fn ($component, $state) => $component->state(in_array($state, ['simple', 'variable']) ? $state : 'simple'))
                ->afterStateUpdated(fn (Set $set, $state) => $set('details.manage_stock', $state === 'simple')),
            Tabs::make('Данные')->vertical()->tabs([
                Tab::make('Основные')->icon('heroicon-o-adjustments-horizontal')->schema([
                    Grid::make(2)->schema(self::pricing('simple.'))->visible(fn (Get $get) => $get('type') === 'simple'),
                    Select::make('details.tax_status')->label('Налоговый статус')->options(['taxable' => 'Налогооблагаемый', 'shipping' => 'Только доставка', 'none' => 'Не облагается'])->default('taxable')->selectablePlaceholder(false),
                    Select::make('details.tax_class')->label('Налоговый класс')->options(['standard' => 'Стандартный', 'reduced' => 'Пониженная ставка', 'zero' => 'Нулевая ставка'])->default('standard')->selectablePlaceholder(false),
                ]),
                Tab::make('Запасы')->icon('heroicon-o-archive-box')->schema([
                    TextInput::make('sku')->label('SKU')->required()->maxLength(255),
                    TextInput::make('article')->label('Артикул')->maxLength(255),
                    TextInput::make('details.gtin')->label('GTIN / UPC / EAN / ISBN')->maxLength(100),
                    TextInput::make('details.mpn')->label('MPN — код производителя')->maxLength(100),
                    Toggle::make('details.manage_stock')->label('Управлять запасами?')->default(true)->live(),
                    TextInput::make('simple.stock')->label('Количество на складе')->numeric()->integer()->default(0)->required()->dehydratedWhenHidden()->visible(fn (Get $get) => $get('type') === 'simple' && $get('details.manage_stock')),
                    TextInput::make('inventory_stock')->label('Количество на складе')->numeric()->integer()->default(0)->dehydratedWhenHidden()->visible(fn (Get $get) => $get('type') === 'variable' && $get('details.manage_stock')),
                    self::backorders('details.backorders')->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                    TextInput::make('details.low_stock')->label('Порог малого запаса')->numeric()->integer()->minValue(0)->default(2)->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                    self::stockStatus('details.stock_status')->visible(fn (Get $get) => ! $get('details.manage_stock'))->dehydratedWhenHidden(),
                    Toggle::make('details.sold_individually')->label('Продавать индивидуально')->helperText('Одна штука этого товара в одном заказе.')->default(false),
                    TextInput::make('simple.name')->label('Название варианта / объём')->visible(fn (Get $get) => $get('type') === 'simple')->maxLength(255),
                ]),
                Tab::make('Доставка')->icon('heroicon-o-truck')->schema(self::shipping('details.')),
                Tab::make('Атрибуты')->icon('heroicon-o-list-bullet')->schema([
                    Repeater::make('attribute_config')->label('Атрибуты товара')->defaultItems(0)->addActionLabel('Добавить атрибут')->schema([
                        Select::make('attribute_id')->label('Атрибут')->options(fn () => ProductAttribute::orderBy('name')->pluck('name', 'id'))->required()->live()->afterStateUpdated(fn (Set $set) => $set('value_ids', [])),
                        CheckboxList::make('value_ids')->label('Значения')->options(fn (Get $get) => AttributeValue::where('product_attribute_id', $get('attribute_id'))->orderBy('id')->pluck('name', 'id'))->required()->columns(3),
                        Toggle::make('visible')->label('Показывать в характеристиках')->default(true),
                        Toggle::make('for_variations')->label('Использовать для вариаций')->default(false),
                    ])->itemLabel(fn (array $state) => ProductAttribute::find($state['attribute_id'] ?? null)?->name ?? 'Новый атрибут')->collapsible(),
                ]),
                Tab::make('Вариации')->icon('heroicon-o-squares-2x2')->visible(fn (Get $get) => $get('type') === 'variable')->schema([
                    Repeater::make('variants')->label('Вариации')->defaultItems(0)->addable(false)->reorderable(false)->collapsible()->schema([
                        Hidden::make('id'), Hidden::make('combination_key'), Hidden::make('attribute_value_ids'),
                        TextInput::make('name')->label('Комбинация атрибутов')->readOnly()->columnSpanFull(),
                        TextInput::make('sku')->label('SKU вариации')->required()->maxLength(255), TextInput::make('article')->label('Артикул вариации')->maxLength(255),
                        TextInput::make('details.gtin')->label('GTIN / UPC / EAN / ISBN')->maxLength(100), TextInput::make('details.mpn')->label('MPN')->maxLength(100),
                        Toggle::make('active')->label('Включена')->default(true), Toggle::make('details.manage_stock')->label('Управлять запасами?')->default(true)->live(),
                        TextInput::make('stock')->label('Количество на складе')->numeric()->integer()->default(0)->required()->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                        self::backorders('details.backorders')->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                        self::stockStatus('details.stock_status')->visible(fn (Get $get) => ! $get('details.manage_stock'))->dehydratedWhenHidden(),
                        TextInput::make('details.low_stock')->label('Порог малого запаса')->numeric()->integer()->minValue(0)->default(2),
                        ...self::pricing(''),
                        ...self::shipping('details.'),
                        Select::make('details.tax_class')->label('Налоговый класс')->options(['parent' => 'Как у родительского товара', 'standard' => 'Стандартный', 'reduced' => 'Пониженная ставка', 'zero' => 'Нулевая ставка'])->default('parent')->selectablePlaceholder(false),
                        ProductResource::imageField('details.image', false)->label('Изображение вариации')->hiddenLabel(false),
                        Textarea::make('details.description')->label('Описание вариации')->maxLength(10000)->columnSpanFull(),
                    ])->columns(2)->itemLabel(fn (array $state) => $state['name'] ?? 'Вариация'),
                    Action::make('generateVariations')->label('Сформировать вариации из атрибутов')->authorize(fn (?Product $record) => $record ? ProductResource::canEdit($record) : ProductResource::canCreate())->action(function (Get $get, Set $set, ?Product $record) {
                        try {
                            $set('variants', app(ProductEditor::class)->generate($get('attribute_config') ?? [], $get('variants') ?? [], $get('sku') ?? '', $record));
                        } catch (ValidationException $e) {
                            ProductResource::formErrors($e);
                        }
                    }),
                ]),
                Tab::make('Дополнительно')->icon('heroicon-o-cog-6-tooth')->schema([
                    Textarea::make('details.purchase_note')->label('Примечание к покупке')->helperText('Примечание покупателю после оформления заказа.')->maxLength(10000),
                    TextInput::make('details.menu_order')->label('Порядок меню')->numeric()->integer()->minValue(0)->default(0),
                    Toggle::make('details.reviews_enabled')->label('Включить отзывы')->default(true),
                ]),
            ]),
        ]);
    }

    private static function pricing(string $prefix): array
    {
        return [TextInput::make($prefix.'price')->label('Обычная цена, ₽')->required()->default('0.00')->regex('/^\d{1,8}([.,]\d{1,2})?$/'),
            TextInput::make($prefix.'sale_price')->label('Цена распродажи, ₽')->nullable()->regex('/^\d{1,8}([.,]\d{1,2})?$/'),
            DatePicker::make($prefix.'sale_starts_at')->label('Распродажа с')->native(true), DatePicker::make($prefix.'sale_ends_at')->label('Распродажа по')->native(true)];
    }

    private static function backorders(string $name): Select
    {
        return Select::make($name)->label('Разрешить предзаказы?')->options(['no' => 'Не разрешать', 'notify' => 'Разрешить с уведомлением клиента', 'yes' => 'Разрешить'])->default('no')->selectablePlaceholder(false);
    }

    private static function stockStatus(string $name): Select
    {
        return Select::make($name)->label('Статус наличия')->options(['instock' => 'В наличии', 'outofstock' => 'Нет в наличии', 'onbackorder' => 'Под заказ'])->default('instock')->selectablePlaceholder(false);
    }

    private static function shipping(string $prefix): array
    {
        $fields = [TextInput::make($prefix.'weight')->label('Вес, кг')->numeric()->minValue(0)];
        foreach (['length' => 'Длина, см', 'width' => 'Ширина, см', 'height' => 'Высота, см'] as $name => $label) {
            $fields[] = TextInput::make($prefix.$name)->label($label)->numeric()->minValue(0);
        }
        $fields[] = Select::make($prefix.'shipping_class')->label('Класс доставки')->placeholder('Нет класса доставки')
            ->options(fn () => ShippingClass::orderBy('name')->pluck('name', 'slug'))
            ->createOptionAction(fn (Action $action) => $action->label('Добавить класс доставки')->modalHeading('Новый класс доставки'))
            ->createOptionForm([TextInput::make('name')->label('Название класса')->required()->maxLength(120)])
            ->createOptionUsing(function (array $data) {
                $user = Filament::auth()->user();
                abort_unless($user?->hasPermission('products', 'create') || $user?->hasPermission('products', 'update'), 403);
                $slug = Str::slug($data['name']) ?: (string) Str::ulid();

                return ShippingClass::firstOrCreate(['slug' => $slug], ['name' => $data['name']])->slug;
            });

        return $fields;
    }
}
