<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['hair' => 'Волосы', 'body' => 'Тело', 'face' => 'Лицо', 'sets' => 'Наборы'] as $slug => $name) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
        $body = Category::where('slug', 'body')->first();
        $volume = ProductAttribute::firstOrCreate(['slug' => 'volume'], ['name' => 'Объём']);
        $value = $volume->values()->firstOrCreate(['slug' => '250-ml'], ['name' => '250 мл']);
        $collection = ProductCollection::firstOrCreate(['slug' => 'hit-prodazh'], ['name' => 'Хит продаж']);
        foreach ([['vanilla', 'Vanilla', 'Парфюмированный крем для тела', 'catalog-product.png'], ['blanche', 'Blanche', 'Парфюмированный крем для тела', 'catalog-product2.png'], ['sultan', 'Парфюмированный мист для тела и волос Sultan', 'Мощный, глубокий и таинственный аромат', 'product-page-slider1.png']] as [$slug,$name,$description,$image]) {
            $sku = 'DEMO-'.strtoupper($slug);
            $p = Product::firstOrCreate(['slug' => $slug], ['name' => $name, 'sku' => $sku, 'article' => $sku, 'type' => 'simple', 'short_description' => $description, 'description' => $description, 'image' => '/assets/images/'.$image, 'gallery' => ['/assets/images/'.$image], 'published' => true]);
            if ($p->wasRecentlyCreated) {
                $p->categories()->sync([$body->id]);
                $p->collections()->sync([$collection->id]);
                $p->update(['attribute_config' => [['attribute_id' => $volume->id, 'value_ids' => [$value->id], 'visible' => true, 'for_variations' => false]]]);
                $p->variants()->create(['sku' => $sku, 'article' => $sku, 'name' => '250 мл', 'price_minor' => 62000, 'stock' => 20, 'active' => true, 'attribute_value_ids' => []]);
            }
        }
    }
}
