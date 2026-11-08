<?php

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaLibrary
{
    public static function metadata(string $path): array
    {
        ProductMedia::url($path);
        $file = str_starts_with($path, '/assets/') ? public_path(ltrim($path, '/')) : Storage::disk('public')->path(ProductMedia::fieldPath($path));
        if (! is_file($file)) {
            return [];
        }
        $dimensions = @getimagesize($file);

        return ['mime_type' => mime_content_type($file), 'size' => filesize($file), 'width' => $dimensions[0] ?? null, 'height' => $dimensions[1] ?? null];
    }

    public static function upload(TemporaryUploadedFile $file): MediaAsset
    {
        $path = $file->storePublicly('content', 'public');

        return MediaAsset::create(['path' => $path, 'name' => $file->getClientOriginalName()]);
    }
}
