<?php

namespace App\Filament\Resources;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use App\Services\ProductEditor;
use App\Services\ProductMedia;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationLabel(): string
    {
        return 'Товары';
    }

    public static function getModelLabel(): string
    {
        return 'товар';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Товары';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make()->columnSpan(['lg' => 2])->schema([
                    Section::make('Товар')->schema([
                        TextInput::make('name')->label('Название товара')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get, $state) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')->label('Адрес товара')->prefix('/products/')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true),
                        RichEditor::make('description')->label('Описание')->toolbarButtons(['bold', 'italic', 'underline', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'undo', 'redo']),
                        Textarea::make('short_description')->label('Краткое описание')->rows(3),
                    ]),
                    Section::make('Данные товара')->schema([
                        Select::make('type')->label('Тип товара')->options(['simple' => 'Простой товар', 'variable' => 'Вариативный товар'])->default('simple')->required()->live(),
                        Tabs::make('Данные')->tabs([
                            Tab::make('Основные')->schema([
                                TextInput::make('sku')->label('SKU')->required()->maxLength(255)->helperText('Уникальный ключ для обмена с 1С. У вариаций свои SKU.'),
                                TextInput::make('article')->label('Артикул')->maxLength(255)->helperText('Показывается покупателю отдельно от SKU.'),
                                TextInput::make('simple.price')->label('Цена, ₽')->default('0.00')->required()->regex('/^\d{1,8}([.,]\d{1,2})?$/')->visible(fn (Get $get) => $get('type') === 'simple'),
                                TextInput::make('simple.stock')->label('Остаток')->default(0)->numeric()->integer()->minValue(0)->required()->visible(fn (Get $get) => $get('type') === 'simple'),
                                TextInput::make('simple.name')->label('Название варианта / объём')->maxLength(255)->helperText('Например, 250 мл. Необязательно.')->visible(fn (Get $get) => $get('type') === 'simple'),
                            ])->columns(2),
                            Tab::make('Атрибуты')->schema([
                                Repeater::make('attribute_config')->label('Атрибуты товара')->defaultItems(0)->addActionLabel('Добавить атрибут')->schema([
                                    Select::make('attribute_id')->label('Атрибут')->options(fn () => ProductAttribute::orderBy('name')->pluck('name', 'id'))->required()->live()->afterStateUpdated(fn (Set $set) => $set('value_ids', [])),
                                    CheckboxList::make('value_ids')->label('Значения')->options(fn (Get $get) => AttributeValue::where('product_attribute_id', $get('attribute_id'))->orderBy('id')->pluck('name', 'id'))->required()->columns(3),
                                    Toggle::make('visible')->label('Показывать в характеристиках')->default(true),
                                    Toggle::make('for_variations')->label('Использовать для вариаций')->default(false),
                                ])->itemLabel(fn (array $state) => ProductAttribute::find($state['attribute_id'] ?? null)?->name ?? 'Новый атрибут')->collapsible(),
                            ]),
                            Tab::make('Вариации')->visible(fn (Get $get) => $get('type') === 'variable')->schema([
                                Section::make('Вариации из атрибутов')->description('Сначала выберите значения на вкладке «Атрибуты». Повторная генерация сохраняет SKU, цены и остатки существующих комбинаций.')->schema([
                                    Repeater::make('variants')->label('Вариации')->defaultItems(0)->addable(false)->reorderable(false)->collapsible()->schema([
                                        Hidden::make('id'), Hidden::make('combination_key'), Hidden::make('attribute_value_ids'),
                                        TextInput::make('name')->label('Комбинация атрибутов')->readOnly()->columnSpanFull(),
                                        TextInput::make('sku')->label('SKU вариации')->required()->maxLength(255), TextInput::make('article')->label('Артикул вариации')->maxLength(255),
                                        TextInput::make('price')->label('Цена, ₽')->required()->regex('/^\d{1,8}([.,]\d{1,2})?$/'),
                                        TextInput::make('stock')->label('Остаток')->numeric()->integer()->minValue(0)->required(), Toggle::make('active')->label('Включена')->default(true),
                                    ])->columns(2)->itemLabel(fn (array $state) => $state['name'] ?? 'Вариация'),
                                ])->footerActions([
                                    Action::make('generateVariations')->label('Сформировать вариации из атрибутов')->action(function (Get $get, Set $set, ?Product $record) {
                                        try {
                                            $set('variants', app(ProductEditor::class)->generate($get('attribute_config') ?? [], $get('variants') ?? [], $get('sku') ?? '', $record));
                                        } catch (ValidationException $e) {
                                            static::formErrors($e);
                                        }
                                    }),
                                ]),
                            ]),
                        ]),
                    ]),
                ]),
                Group::make()->columnSpan(['lg' => 1])->schema([
                    Section::make('Публикация')->schema([
                        Select::make('status')->label('Статус')->options(Product::STATUSES)->default('draft')->required()->live(),
                        DateTimePicker::make('published_at')->label(fn (Get $get) => $get('status') === 'scheduled' ? 'Отложенная публикация' : 'Дата публикации')->timezone('Europe/Moscow')->seconds(false)->native(false)->helperText('Московское время. Будущая дата автоматически планирует публикацию.'),
                    ]),
                    Section::make('Категории')->schema([CheckboxList::make('category_ids')->hiddenLabel()->options(fn () => static::categoryOptions())->default([])->searchable()]),
                    Section::make('Подборки')->schema([CheckboxList::make('collection_ids')->hiddenLabel()->options(fn () => ProductCollection::orderBy('name')->pluck('name', 'id'))->default([])->searchable()]),
                    Section::make('Изображение товара')->schema([static::imageField('image', false)]),
                    Section::make('Галерея товара')->schema([static::imageField('gallery', true)]),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    private static function imageField(string $name, bool $multiple): FileUpload
    {
        return FileUpload::make($name)->hiddenLabel()->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'])
            ->disk('public')->directory('products')->visibility('public')->fetchFileInformation(false)->maxSize(10240)->multiple($multiple)->reorderable($multiple)->appendFiles()->panelLayout($multiple ? 'grid' : null)
            ->getUploadedFileUsing(fn (string $file) => ProductMedia::info($file))
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => ProductMedia::upload($file))
            ->hintAction(Action::make('library_'.$name)->label('Медиатека')->modalHeading($multiple ? 'Изображения галереи' : 'Изображение товара')->modalWidth('4xl')->stickyModalHeader()->stickyModalFooter()->modalSubmitActionLabel('Выбрать')->schema([
                ($multiple ? CheckboxList::make('paths') : Radio::make('path'))->label('Выберите изображение')->options(fn () => collect(ProductMedia::options())->map(fn ($html) => new HtmlString($html))->all())->columns(3)->required(),
            ])->action(function (array $data, Set $set, Get $get) use ($name, $multiple) {
                $paths = $multiple ? array_values(array_unique([...array_values($get($name) ?? []), ...$data['paths']])) : [$data['path']];
                $set($name, array_combine(array_map(fn () => (string) Str::uuid(), $paths), $paths));
            }));
    }

    public static function categoryOptions(): array
    {
        $categories = Category::orderBy('name')->get()->groupBy('parent_id');
        $options = [];
        $walk = function ($parent, string $prefix = '') use (&$walk, &$options, $categories) {
            foreach ($categories->get($parent, collect()) as $category) {
                if (isset($options[$category->id])) {
                    continue;
                }
                $options[$category->id] = $prefix.$category->name;
                $walk($category->id, $prefix.'— ');
            }
        };
        $walk('');

        return $options;
    }

    public static function formErrors(ValidationException $e, array $state = []): never
    {
        $errors = [];
        foreach ($e->errors() as $field => $messages) {
            foreach (['attribute_config', 'variants'] as $repeater) {
                if (preg_match('/^'.$repeater.'\.(\d+)(\.|$)/', $field, $match)) {
                    $key = array_keys($state[$repeater] ?? [])[(int) $match[1]] ?? $match[1];
                    $field = preg_replace('/^'.$repeater.'\.\d+/', $repeater.'.'.$key, $field);
                }
            }
            $errors['data.'.$field] = $messages;
        }
        throw ValidationException::withMessages($errors);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')->label('Фото')->getStateUsing(fn (Product $record) => $record->image ? url($record->image) : null), TextColumn::make('name')->label('Название')->searchable()->sortable(),
            TextColumn::make('sku')->label('SKU')->searchable(), TextColumn::make('article')->label('Артикул')->searchable(),
            TextColumn::make('type')->label('Тип')->formatStateUsing(fn ($state) => $state === 'variable' ? 'Вариативный' : 'Простой'),
            TextColumn::make('categories.name')->label('Категории')->badge(), TextColumn::make('collections.name')->label('Подборки')->badge(),
            TextColumn::make('status')->label('Статус')->formatStateUsing(fn ($state, Product $record) => $record->trashed() ? 'В корзине' : (Product::STATUSES[$state] ?? $state))->badge(),
            TextColumn::make('published_at')->label('Публикация')->dateTime('d.m.Y H:i', 'Europe/Moscow')->sortable(),
        ])->filters([SelectFilter::make('status')->label('Статус')->options(Product::STATUSES), SelectFilter::make('type')->label('Тип')->options(['simple' => 'Простой', 'variable' => 'Вариативный']), TrashedFilter::make()])
            ->recordActions([EditAction::make()->url(fn (Product $record) => static::getUrl('edit', ['record' => $record])), DeleteAction::make(), RestoreAction::make()])
            ->recordUrl(fn (Product $record) => static::getUrl('edit', ['record' => $record]))->defaultSort('id', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProducts::route('/'), 'create' => Pages\CreateProduct::route('/create'), 'edit' => Pages\EditProduct::route('/{record}/edit')];
    }
}
