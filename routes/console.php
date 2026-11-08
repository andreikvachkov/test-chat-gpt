<?php

use App\Models\Product;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shop:publish-scheduled', function () {
    $count = Product::where('status', 'scheduled')->where('published_at', '<=', now())->update(['status' => 'published', 'published' => true]);
    $this->info('Опубликовано товаров: '.$count);
})->purpose('Опубликовать товары, для которых наступила назначенная дата');

Schedule::command('shop:publish-scheduled')->everyMinute()->withoutOverlapping();
