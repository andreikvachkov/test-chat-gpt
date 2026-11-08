<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['hair' => 'Волосы', 'body' => 'Тело', 'face' => 'Лицо', 'sets' => 'Наборы'] as $slug => $name) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
        $body = Category::where('slug', 'body')->first();
        foreach ([['vanilla', 'Vanilla', 'Парфюмированный крем для тела', 'catalog-product.png'], ['blanche', 'Blanche', 'Парфюмированный крем для тела', 'catalog-product2.png'], ['sultan', 'Парфюмированный мист для тела и волос Sultan', 'Мощный, глубокий и таинственный аромат', 'product-page-slider1.png']] as [$slug,$name,$description,$image]) {
            $p = Product::firstOrCreate(['slug' => $slug], ['category_id' => $body->id, 'name' => $name, 'description' => $description, 'image' => '/assets/images/'.$image, 'gallery' => ['/assets/images/'.$image], 'published' => true, 'badge' => 'Хит продаж']);
            $p->variants()->firstOrCreate(['sku' => 'DEMO-'.strtoupper($slug)], ['name' => '250 мл', 'price_minor' => 62000, 'stock' => 20, 'active' => true]);
        }
    }
}
