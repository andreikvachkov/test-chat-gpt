<?php

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductMedia
{
    public static function fieldPath(?string $path): ?string
    {
        return $path ? preg_replace('#^/storage/#', '', $path) : null;
    }

    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }
        $path = self::fieldPath($path);
        if (str_contains($path, '..') || (! str_starts_with($path, '/assets/images/') && ! preg_match('#^(products|content)/[a-zA-Z0-9_./ -]+$#', $path))) {
            throw ValidationException::withMessages(['image' => 'Выберите изображение из медиатеки или загрузите файл.']);
        }

        return str_starts_with($path, '/assets/') ? $path : '/storage/'.$path;
    }

    public static function info(string $file): ?array
    {
        $url = self::url($file);
        $local = str_starts_with($file, '/assets/') ? public_path(ltrim($file, '/')) : Storage::disk('public')->path($file);

        return is_file($local) ? ['name' => basename($file), 'size' => filesize($local), 'type' => mime_content_type($local), 'url' => $url] : null;
    }

    public static function upload(TemporaryUploadedFile $file): string
    {
        $path = $file->storePublicly('products', 'public');
        MediaAsset::firstOrCreate(['path' => $path], ['name' => $file->getClientOriginalName()]);

        return $path;
    }

    public static function options(): array
    {
        $paths = collect(glob(public_path('assets/images/*')))->filter(fn ($p) => is_file($p) && in_array(strtolower(pathinfo($p, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']))->mapWithKeys(fn ($p) => ['/assets/images/'.basename($p) => basename($p)])
            ->merge(MediaAsset::where(fn ($q) => $q->where('mime_type', 'like', 'image/%')->orWhereNull('mime_type'))->get()->mapWithKeys(fn ($m) => [$m->path => $m->name]));

        return $paths->mapWithKeys(fn ($name, $file) => [$file => '<img src="'.e(self::url($file)).'" alt="" loading="lazy" style="width:100%;height:100px;object-fit:contain"><span>'.e($name).'</span>'])->all();
    }
}
