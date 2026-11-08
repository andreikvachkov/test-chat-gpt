<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\ProductEditor;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_legacy_catalog_migrates_without_losing_images_stock_categories_or_order_links(): void
    {
        while (Schema::hasColumn('products', 'sku')) {
            $this->artisan('migrate:rollback', ['--step' => 1, '--force' => true])->assertSuccessful();
        }
        $now = now();
        $category = DB::table('categories')->insertGetId(['name' => 'Старая категория', 'slug' => 'legacy-category', 'created_at' => $now, 'updated_at' => $now]);
        $id = DB::table('products')->insertGetId(['name' => 'Старый товар', 'slug' => 'legacy-product', 'category_id' => $category, 'external_id' => 'legacy-1c-id',
            'description' => 'Старое описание', 'image' => '/storage/content/old.png', 'gallery' => json_encode(['/storage/content/one.png', '/storage/content/two.png']),
            'badge' => 'Новинки', 'published' => true, 'created_at' => $now, 'updated_at' => $now]);
        $variants = [];
        foreach (['100 мл', '250 мл', '100 мл'] as $i => $name) {
            $variants[] = DB::table('product_variants')->insertGetId(['product_id' => $id, 'sku' => 'OLD-'.$i, 'name' => $name, 'price_minor' => 12345 + $i, 'stock' => 7 + $i, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        $user = User::factory()->create();
        $order = DB::table('orders')->insertGetId(['user_id' => $user->id, 'number' => (string) Str::uuid(), 'checkout_token' => (string) Str::uuid(),
            'delivery_method' => 'cdek', 'subtotal_minor' => 12345, 'name' => 'Получатель', 'email' => 'legacy@example.test', 'phone' => '+79999999999', 'address' => 'Адрес', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('order_items')->insert(['order_id' => $order, 'product_variant_id' => $variants[0], 'name' => 'Старый товар', 'sku' => 'OLD-0', 'price_minor' => 12345, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now]);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $product = Product::findOrFail($id);
        $this->assertSame('variable', $product->type);
        $this->assertSame('PRODUCT-'.$id, $product->sku);
        $this->assertSame('Старое описание', $product->short_description);
        $this->assertSame('/storage/content/old.png', $product->image);
        $this->assertSame(['/storage/content/one.png', '/storage/content/two.png'], $product->gallery);
        $this->assertSame('published', $product->status);
        $this->assertTrue($product->isVisible());
        $this->assertSame([$category], $product->categories()->pluck('categories.id')->all());
        $this->assertSame('Новинки', $product->collections()->first()->name);
        $this->assertEquals($variants, $product->variants()->orderBy('id')->pluck('id')->all());
        $this->assertEquals([7, 8, 9], $product->variants()->orderBy('id')->pluck('stock')->all());
        $this->assertSame($variants[0], DB::table('order_items')->where('order_id', $order)->value('product_variant_id'));
        $this->assertCount(3, $product->attribute_config[0]['value_ids']);
        $this->assertCount(3, app(ProductEditor::class)->generate($product->attribute_config, [], $product->sku, $product));
    }
}
