<?php

namespace App\Filament\Resources;

use App\Models\ContentEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentEntryResource extends ShopResource
{
    protected static ?string $model = ContentEntry::class;

    // Compatibility class for installations updated by extracting a ZIP.
    protected static bool $isDiscovered = false;

    public static function getNavigationLabel(): string
    {
        return 'Тексты, ссылки и изображения';
    }

    public static function getModelLabel(): string
    {
        return 'Тексты, ссылки и изображения';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Тексты, ссылки и изображения';
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([SelectFilter::make('group')->label('Страница')->options(fn () => ContentEntry::query()->distinct()->pluck('group', 'group')->all())]);
    }

    public static function fields(): array
    {
        return [TextInput::make('group')->label('Страница / shared для общих блоков')->required(), TextInput::make('key')->label('Ключ блока')->disabled()->dehydrated(false), TextInput::make('label')->label('Название')->required(), Select::make('type')->label('Тип')->options(['text' => 'Текст', 'image' => 'Изображение', 'link' => 'Ссылка'])->required()->live(), Textarea::make('value')->label('Текст или ссылка')->visible(fn (Get $get) => $get('type') !== 'image')->columnSpanFull()->rules([fn () => function ($attribute, $value, $fail) {
            if (is_string($value) && preg_match('/^\s*(javascript|data|vbscript):/i', $value)) {
                $fail('Недопустимая ссылка.');
            }
        }]), Select::make('value')->label('Изображение из медиатеки')->options(fn () => static::imageOptions())->searchable()->visible(fn (Get $get) => $get('type') === 'image')->columnSpanFull()];
    }

    public static function columns(): array
    {
        return [TextColumn::make('group')->label('Страница')->searchable()->sortable(), TextColumn::make('label')->label('Блок')->searchable()->limit(70), TextColumn::make('value')->label('Содержание')->limit(70)];
    }

    public static function getPages(): array
    {
        return [];
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
