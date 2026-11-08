<?php

namespace App\Filament\Forms;

use App\Filament\Resources\ProductResource;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Services\ProductEditor;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\ValidationException;

class ProductDataFields
{
    public static function make(): Section
    {
        return Section::make('Данные товара')->extraAttributes(['class' => 'kamali-product-data'])->schema([
            Select::make('type')->label('Тип товара')->options(['simple' => 'Простой товар', 'variable' => 'Вариативный товар'])->default('simple')->selectablePlaceholder(false)->required()->live()
                ->afterStateHydrated(fn ($component, $state) => $component->state(in_array($state, ['simple', 'variable']) ? $state : 'simple'))
                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                    self::cacheVariations($get, $set);
                    $set('variants', []);
                    $set('details.manage_stock', $state === 'simple');
                }),
            Hidden::make('variation_cache')->default([])->dehydrated(false),
            Tabs::make('Данные')->vertical()->tabs([
                Tab::make('Основные')->icon('heroicon-o-adjustments-horizontal')->visible(fn (Get $get) => $get('type') === 'simple')->schema([
                    Grid::make(2)->schema(self::pricing('simple.'))->visible(fn (Get $get) => $get('type') === 'simple'),
                ]),
                Tab::make('Запасы')->icon('heroicon-o-archive-box')->schema([
                    TextInput::make('sku')->label('SKU')->required()->maxLength(255),
                    TextInput::make('article')->label('Артикул')->maxLength(255),
                    Toggle::make('details.manage_stock')->label(fn (Get $get) => $get('type') === 'variable' ? 'Управлять общим остатком товара?' : 'Управлять запасами?')->helperText(fn (Get $get) => $get('type') === 'variable' ? 'Общий запас используют только вариации, у которых выключен собственный учёт.' : null)->default(true)->live(),
                    TextInput::make('simple.stock')->label('Количество на складе')->numeric()->integer()->default(0)->required()->dehydratedWhenHidden()->visible(fn (Get $get) => $get('type') === 'simple' && $get('details.manage_stock')),
                    TextInput::make('inventory_stock')->label('Общий остаток товара')->numeric()->integer()->default(0)->dehydratedWhenHidden()->visible(fn (Get $get) => $get('type') === 'variable' && $get('details.manage_stock')),
                    self::backorders('details.backorders')->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                    TextInput::make('details.low_stock')->label('Порог малого запаса')->numeric()->integer()->minValue(0)->default(2)->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                    self::stockStatus('details.stock_status')->visible(fn (Get $get) => $get('type') === 'simple' && ! $get('details.manage_stock'))->dehydratedWhenHidden(),
                ]),
                Tab::make('Доставка')->icon('heroicon-o-truck')->schema(self::shipping('details.')),
                Tab::make('Атрибуты')->icon('heroicon-o-list-bullet')->schema([
                    Repeater::make('attribute_config')->label('Атрибуты товара')->defaultItems(0)->live()->afterStateUpdated(fn (Get $get, Set $set) => self::reconcileVariations($get, $set))->addActionLabel('Добавить атрибут')->schema([
                        Select::make('attribute_id')->label('Атрибут')->options(fn () => ProductAttribute::orderBy('name')->pluck('name', 'id'))->required()->live()->afterStateUpdated(fn (Set $set) => $set('value_ids', [])),
                        CheckboxList::make('value_ids')->label('Значения')->live()->options(fn (Get $get) => AttributeValue::where('product_attribute_id', $get('attribute_id'))->orderBy('id')->pluck('name', 'id'))->required()->columns(3),
                        Toggle::make('visible')->label('Показывать в характеристиках')->default(true),
                        Toggle::make('for_variations')->label('Использовать для вариаций')->default(false)->live(),
                    ])->itemLabel(fn (array $state) => ProductAttribute::find($state['attribute_id'] ?? null)?->name ?? 'Новый атрибут')->collapsible(),
                ]),
                Tab::make('Вариации')->icon('heroicon-o-squares-2x2')->visible(fn (Get $get) => $get('type') === 'variable')->schema([
                    Placeholder::make('variation_hint')->hiddenLabel()->content('Отметьте атрибуты для вариаций и нажмите «Сформировать вариации из атрибутов». SKU, цены и остатки существующих комбинаций сохранятся.')->visible(fn (Get $get) => empty($get('variants'))),
                    Repeater::make('variants')->label('Вариации')->defaultItems(0)->addable(false)->reorderable(false)->collapsible()->schema([
                        Hidden::make('id'), Hidden::make('combination_key'), Hidden::make('attribute_value_ids'),
                        TextInput::make('name')->label('Комбинация атрибутов')->readOnly()->columnSpanFull(),
                        TextInput::make('sku')->label('SKU вариации')->required()->maxLength(255), TextInput::make('article')->label('Артикул вариации')->maxLength(255),
                        Toggle::make('active')->label('Включена')->default(true), Toggle::make('details.manage_stock')->label('Собственный остаток вариации')->default(true)->live(),
                        Placeholder::make('stock_source')->label('Источник остатка')->content(fn (Get $get) => $get('details.manage_stock') ? 'Остаток учитывается отдельно для этой вариации. Общий остаток родителя не списывается.' : ($get('../../details.manage_stock') ? 'Используется общий остаток родителя: '.($get('../../inventory_stock') ?? 0).' шт. Предзаказы и порог малого запаса задаются у родителя.' : 'Количество не отслеживается. Доступность определяется статусом наличия ниже.'))->columnSpanFull(),
                        TextInput::make('stock')->label('Количество на складе')->numeric()->integer()->default(0)->required()->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                        self::backorders('details.backorders')->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                        self::stockStatus('details.stock_status')->visible(fn (Get $get) => ! $get('details.manage_stock') && ! $get('../../details.manage_stock'))->dehydratedWhenHidden(),
                        TextInput::make('details.low_stock')->label('Порог малого запаса')->numeric()->integer()->minValue(0)->default(2)->visible(fn (Get $get) => (bool) $get('details.manage_stock'))->dehydratedWhenHidden(),
                        ...self::pricing(''),
                        ...self::shipping('details.'),
                        ProductResource::imageField('details.image', false)->label('Изображение вариации')->hiddenLabel(false),
                    ])->columns(2)->itemLabel(fn (array $state) => $state['name'] ?? 'Вариация'),
                    Action::make('generateVariations')->label('Сформировать вариации из атрибутов')->disabled(fn (Get $get) => ! collect($get('attribute_config') ?? [])->contains(fn ($row) => ! empty($row['for_variations']) && ! empty($row['value_ids'])))->authorize(fn (?Product $record) => $record ? ProductResource::canEdit($record) : ProductResource::canCreate())->action(function (Get $get, Set $set, ?Product $record) {
                        try {
                            $set('variants', app(ProductEditor::class)->generate($get('attribute_config') ?? [], [...array_values($get('variation_cache') ?? []), ...array_values($get('variants') ?? [])], $get('sku') ?? '', $record));
                        } catch (ValidationException $e) {
                            ProductResource::formErrors($e);
                        }
                    }),
                ]),
                Tab::make('Дополнительно')->icon('heroicon-o-cog-6-tooth')->schema([
                    TextInput::make('details.menu_order')->label('Порядок меню')->numeric()->integer()->minValue(0)->default(0),
                ]),
            ]),
        ]);
    }

    private static function cacheVariations(Get $get, Set $set): void
    {
        $cache = $get('variation_cache') ?? [];
        foreach ($get('variants') ?? [] as $row) {
            if (filled($row['combination_key'] ?? null)) {
                $cache[$row['combination_key']] = $row;
            }
        }
        $set('variation_cache', $cache);
    }

    private static function reconcileVariations(Get $get, Set $set): void
    {
        self::cacheVariations($get, $set);
        $set('variants', app(ProductEditor::class)->compatibleVariations($get('attribute_config') ?? [], $get('variants') ?? []));
    }

    private static function pricing(string $prefix): array
    {
        return [TextInput::make($prefix.'price')->label('Обычная цена, ₽')->required()->default('0.00')->regex('/^\d{1,8}([.,]\d{1,2})?$/'),
            TextInput::make($prefix.'sale_price')->label('Цена распродажи, ₽')->nullable()->regex('/^\d{1,8}([.,]\d{1,2})?$/')];
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

        return $fields;
    }
}
