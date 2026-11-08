<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

/** Compatibility stub: ZIP updates overwrite the old standalone variants resource. */
class ProductVariantResource extends Resource
{
    protected static bool $isDiscovered = false;

    protected static bool $shouldRegisterNavigation = false;

    public static function getPages(): array
    {
        return [];
    }
}
