<?php

namespace App\Filament\Resources;

use App\Models\MediaAsset;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\View;
use Filament\Tables\Columns\Layout\View as MediaCard;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaAssetResource extends ShopResource
{
    protected static ?string $model = MediaAsset::class;

    public static function getNavigationLabel(): string
    {
        return 'Медиафайлы';
    }

    public static function getModelLabel(): string
    {
        return 'Медиафайлы';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Медиафайлы';
    }

    public static function fields(): array
    {
        return [Grid::make(['default' => 1, 'lg' => 3])->schema([
            View::make('filament.media-preview')->columnSpan(['lg' => 2]),
            Group::make()->schema([
                TextInput::make('name')->label('Название')->required()->maxLength(255),
                Textarea::make('alt_text')->label('Альтернативный текст')->maxLength(2000),
                Textarea::make('caption')->label('Подпись')->maxLength(5000),
                Textarea::make('description')->label('Описание')->maxLength(10000),
                Placeholder::make('file_info')->label('Сведения о файле')->content(fn ($record) => $record ? basename($record->path).' · '.$record->mime_type.' · '.number_format(($record->size ?? 0) / 1024, 1, ',', ' ').' КБ'.($record->width ? ' · '.$record->width.' × '.$record->height : '') : ''),
                TextInput::make('file_url')->label('URL файла')->afterStateHydrated(fn ($component, $record) => $component->state($record ? url($record->url) : ''))->readOnly()->dehydrated(false),
            ]),
        ])->columnSpanFull()];
    }

    public static function table(Table $table): Table
    {
        return $table->columns([MediaCard::make('filament.media-card')])->searchable(['name', 'alt_text', 'caption'])
            ->contentGrid(['default' => 2, 'md' => 4, 'xl' => 6])->paginated([24, 48, 96])->defaultPaginationPageOption(24)
            ->filters([SelectFilter::make('kind')->label('Тип файлов')->options(['image' => 'Изображения', 'video' => 'Видео', 'application/pdf' => 'PDF'])->query(fn ($query, array $data) => $query->when($data['value'] ?? null, fn ($q, $value) => $q->where('mime_type', 'like', $value.'%')))])
            ->recordActions([ViewAction::make()->iconButton()->label('Сведения')->modalHeading('Сведения о файле')->modalWidth('7xl'), EditAction::make()->iconButton()->modalHeading('Сведения о файле')->modalWidth('7xl'), DeleteAction::make()->iconButton()])
            ->recordAction(fn ($record) => static::canEdit($record) ? 'edit' : 'view')->defaultSort('id', 'desc')->emptyStateHeading('Медиафайлов пока нет')->emptyStateDescription('Загрузите изображения или другие файлы.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMediaAsset::route('/')];
    }
}
