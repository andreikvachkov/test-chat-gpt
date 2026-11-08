<?php

namespace App\Support;

class AdminPermissions
{
    public const GROUPS = [
        'Каталог' => ['products' => 'Товары', 'categories' => 'Категории', 'attributes' => 'Атрибуты', 'collections' => 'Подборки'],
        'Продажи' => ['orders' => 'Заказы', 'customers' => 'Покупатели'],
        'Контент' => ['pages' => 'Страницы и SEO', 'content' => 'Тексты, ссылки и изображения', 'media' => 'Медиатека'],
    ];

    public static function actions(string $section): array
    {
        $actions = ['view' => 'Просмотр', 'create' => 'Создание', 'update' => 'Изменение', 'delete' => 'Удаление'];
        if (in_array($section, ['orders', 'customers'])) {
            return array_intersect_key($actions, array_flip(['view', 'update']));
        }
        if ($section === 'products') {
            $actions['restore'] = 'Восстановление из корзины';
        }

        return $actions;
    }

    public static function all(): array
    {
        $result = [];
        foreach (self::GROUPS as $sections) {
            foreach ($sections as $key => $label) {
                $result[$key] = array_keys(self::actions($key));
            }
        }

        return $result;
    }

    public static function roles(): array
    {
        $all = self::all();

        return [
            'owner' => ['name' => 'Владелец', 'permissions' => $all],
            'shop_manager' => ['name' => 'Управляющий магазином', 'permissions' => $all],
            'catalog_manager' => ['name' => 'Менеджер каталога', 'permissions' => array_intersect_key($all, array_flip(['products', 'categories', 'attributes', 'collections', 'media']))],
            'sales_manager' => ['name' => 'Менеджер заказов', 'permissions' => array_intersect_key($all, array_flip(['orders', 'customers']))],
            'content_editor' => ['name' => 'Редактор контента', 'permissions' => array_intersect_key($all, array_flip(['pages', 'content', 'media']))],
        ];
    }
}
