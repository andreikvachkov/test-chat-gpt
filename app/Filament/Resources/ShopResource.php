<?php

namespace App\Filament\Resources;

use App\Models\MediaAsset;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

abstract class ShopResource extends Resource
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components(static::fields());
    }

    public static function table(Table $table): Table
    {
        return $table->columns(static::columns())->recordActions([EditAction::make(), ...(static::allowDeletion() ? [DeleteAction::make()] : [])])->defaultSort('id', 'desc');
    }

    public static function fields(): array
    {
        return [];
    }

    public static function columns(): array
    {
        return [TextColumn::make('id')->sortable()];
    }

    public static function allowDeletion(): bool
    {
        return true;
    }

    public static function imageOptions(): array
    {
        return collect(glob(public_path('assets/images/*')))->filter(fn ($p) => is_file($p))->mapWithKeys(fn ($p) => ['/assets/images/'.basename($p) => basename($p)])->merge(MediaAsset::all()->mapWithKeys(fn ($m) => ['/storage/'.$m->path => $m->name]))->all();
    }
}
