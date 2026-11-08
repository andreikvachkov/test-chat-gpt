<?php

namespace Database\Seeders;

use App\Models\ContentEntry;
use App\Models\Page;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (json_decode(file_get_contents(__DIR__.'/data/content.json'), true) as $entry) {
            ContentEntry::firstOrCreate(['key' => $entry['key']], $entry);
        }
        $titles = ['index' => 'Главная', 'catalog' => 'Каталог', 'product-page' => 'Товар', 'about' => 'О бренде', 'history' => 'История бренда', 'contact' => 'Контакты', 'blog' => 'Блог', 'article' => 'Статья', 'document' => 'Покупателям', 'document2' => 'Политика конфиденциальности', 'document-gift-card' => 'Условия подарочных карт', 'gift' => 'Подарочные карты', 'lk-login' => 'Вход', 'order' => 'Оформление заказа', 'lk-personal-info' => 'Личный кабинет', 'lk-order-page' => 'Заказ', '404' => 'Страница не найдена'];
        foreach ($titles as $template => $title) {
            Page::firstOrCreate(['slug' => $template], ['title' => $title, 'template' => $template, 'published' => true]);
        }
    }
}
