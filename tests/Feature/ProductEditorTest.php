<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\CreateProduct;
use App\Filament\Resources\Pages\EditProduct;
use App\Filament\Resources\Pages\ManageProductAttribute;
use App\Filament\Resources\Pages\ManageProductCollection;
use App\Models\AdminUser;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CatalogImporter;
use App\Services\Checkout;
use App\Services\ProductEditor;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class ProductEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, DemoCatalogSeeder::class]);
    }

    private function data(array $changes = []): array
    {
        return array_replace(['name' => 'Новый крем', 'slug' => 'new-cream', 'sku' => 'CREAM-001', 'article' => 'ART-100', 'type' => 'simple',
            'description' => '<p>Полное <strong>описание</strong></p>', 'short_description' => 'Краткое описание', 'status' => 'draft',
            'simple' => ['price' => '620,50', 'stock' => 7, 'name' => '250 мл'], 'category_ids' => [], 'collection_ids' => [],
            'attribute_config' => [], 'image' => '/assets/images/product.jpg', 'gallery' => ['/assets/images/catalog-product.png']], $changes);
    }

    private function attributeConfig(): array
    {
        $volume = ProductAttribute::create(['name' => 'Размер', 'slug' => 'size']);
        $scent = ProductAttribute::create(['name' => 'Аромат', 'slug' => 'scent']);
        foreach (['100', '250'] as $name) {
            $volume->values()->create(['name' => $name.' мл', 'slug' => $name]);
        }
        foreach (['rose', 'vanilla'] as $name) {
            $scent->values()->create(['name' => $name, 'slug' => $name]);
        }

        return array_map(fn ($a) => ['attribute_id' => $a->id, 'value_ids' => $a->values()->pluck('id')->all(), 'for_variations' => true, 'visible' => true], [$volume, $scent]);
    }

    public function test_product_pages_use_separate_editor_and_hide_legacy_fields(): void
    {
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');
        $product = Product::first();
        $this->get('/admin/products/create')->assertOk()->assertSee('Краткое описание')->assertSee('Изображение товара')->assertDontSee('ID в 1С')->assertDontSee('Состав')->assertDontSee('Применение');
        $this->get('/admin/products/'.$product->id.'/edit')->assertOk()->assertSee('Категории')->assertSee('Подборки')->assertSee('Галерея товара');
        $this->get('/admin/product-variants')->assertNotFound();
        $form = $this->data();
        $form['image'] = ['selected' => $form['image']];
        Livewire::test(CreateProduct::class)->fillForm($form)->call('create')->assertHasNoFormErrors();
        $saved = Product::where('sku', 'CREAM-001')->firstOrFail();
        $this->assertSame(62050, $saved->variants()->first()->price_minor);
        $this->assertSame('ART-100', $saved->article);
        Livewire::test(EditProduct::class, ['record' => $saved->id])->fillForm(['short_description' => 'Сохранено на отдельной странице'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Сохранено на отдельной странице', $saved->fresh()->short_description);
        $this->assertSame('/assets/images/product.jpg', $saved->fresh()->image);
        $this->assertSame(['/assets/images/catalog-product.png'], $saved->fresh()->gallery);
    }

    public function test_simple_product_saves_multiple_taxonomies_prices_and_media_without_replacing_inventory_id(): void
    {
        $categories = Category::limit(2)->pluck('id')->all();
        $collections = [ProductCollection::create(['name' => 'Лето', 'slug' => 'summer'])->id, ProductCollection::create(['name' => 'Новинки', 'slug' => 'new'])->id];
        $editor = app(ProductEditor::class);
        $product = $editor->save(null, $this->data(['category_ids' => $categories, 'collection_ids' => $collections, 'status' => 'published']));
        $variant = $product->variants()->first();
        $this->assertSame(62050, $variant->price_minor);
        $this->assertSame($product->sku, $variant->sku);
        $this->assertEquals($categories, $product->categories()->orderBy('id')->pluck('categories.id')->all());
        $this->assertCount(2, $product->collections);
        $state = $editor->state($product);
        $state['simple']['price'] = '750.25';
        $editor->save($product, $state);
        $this->assertSame($variant->id, $product->variants()->first()->id);
        $this->assertSame(75025, $variant->fresh()->price_minor);
        $this->assertSame('/assets/images/product.jpg', $product->fresh()->image);
        $this->get('/catalog?collection=summer')->assertInertia(fn (Assert $p) => $p->has('products.data', 1));
        foreach ($categories as $id) {
            $this->get('/catalog?category='.Category::find($id)->slug)->assertInertia(fn (Assert $p) => $p->where('products.data', fn ($items) => collect($items)->contains('sku', 'CREAM-001')));
        }
    }

    public function test_variations_are_generated_from_attribute_combinations_and_regeneration_preserves_inventory(): void
    {
        $editor = app(ProductEditor::class);
        $config = $this->attributeConfig();
        $rows = $editor->generate($config, [], 'VARIABLE');
        $this->assertCount(4, $rows);
        foreach ($rows as $i => &$row) {
            $row['price'] = '100.25';
            $row['stock'] = $i + 1;
        }
        unset($row);
        $product = $editor->save(null, $this->data(['sku' => 'VARIABLE', 'type' => 'variable', 'attribute_config' => $config, 'variants' => $rows]));
        $before = $product->variants()->get()->keyBy('combination_key');
        $state = $editor->state($product);
        $state['variants'] = $editor->generate(array_reverse($config), $state['variants'], $product->sku, $product);
        $product = $editor->save($product, $state);
        foreach ($product->variants as $variant) {
            $this->assertSame($before[$variant->combination_key]->id, $variant->id);
            $this->assertSame($before[$variant->combination_key]->stock, $variant->stock);
            $this->assertSame(10025, $variant->price_minor);
        }
        $removed = array_pop($state['variants']);
        $editor->save($product, $state);
        $this->assertSame(3, $product->variants()->count());
        $this->assertFalse(ProductVariant::find($removed['id'])->active);
        $this->assertNotNull(ProductVariant::find($removed['id'])->retired_at);
        $restored = $editor->generate($config, $state['variants'], $product->sku, $product);
        $this->assertSame($removed['id'], collect($restored)->firstWhere('combination_key', $removed['combination_key'])['id']);
    }

    public function test_duplicate_skus_invalid_attribute_values_and_foreign_variation_ids_are_rejected_atomically(): void
    {
        $editor = app(ProductEditor::class);
        try {
            $editor->save(null, $this->data(['sku' => 'demo-vanilla']));
            $this->fail('Expected SKU collision');
        } catch (ValidationException) {
            $this->assertDatabaseMissing('products', ['slug' => 'new-cream']);
        }
        $config = $this->attributeConfig();
        $rows = $editor->generate($config, [], 'VARIABLE');
        $rows[0]['id'] = Product::first()->variants()->first()->id;
        try {
            $editor->save(null, $this->data(['sku' => 'VARIABLE', 'type' => 'variable', 'attribute_config' => $config, 'variants' => $rows]));
            $this->fail('Expected foreign inventory rejection');
        } catch (ValidationException) {
            $this->assertDatabaseMissing('products', ['sku' => 'VARIABLE']);
        }
        $config[0]['value_ids'] = $config[1]['value_ids'];
        $this->expectException(ValidationException::class);
        $editor->generate($config, [], 'INVALID');
    }

    public function test_publication_schedule_private_draft_and_trash_are_respected_by_catalog_cart_and_favourites(): void
    {
        $editor = app(ProductEditor::class);
        $product = $editor->save(null, $this->data(['status' => 'scheduled', 'published_at' => now()->addHour()->toDateTimeString()]));
        $variant = $product->variants()->first();
        $this->get('/products/new-cream')->assertNotFound();
        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 1])->assertSessionHasErrors('cart');
        $this->travel(61)->minutes();
        $this->artisan('shop:publish-scheduled')->assertSuccessful();
        $this->assertSame('published', $product->fresh()->status);
        $this->get('/products/new-cream')->assertOk();
        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 1])->assertSessionHasNoErrors();
        $product->delete();
        $this->get('/catalog')->assertOk();
        $this->get('/products/new-cream')->assertNotFound();
        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 2])->assertSessionHasErrors('cart');
        $product->restore();
        foreach (['draft', 'pending', 'private'] as $status) {
            $product->update(['status' => $status]);
            $this->get('/products/new-cream')->assertNotFound();
        }
    }

    public function test_rich_description_is_sanitized_and_sku_import_keeps_editorial_content_and_variation_ids(): void
    {
        $editor = app(ProductEditor::class);
        $product = $editor->save(null, $this->data(['description' => '<p>Описание</p><script>alert(1)</script><img src=x onerror=alert(1)>']));
        $this->assertStringNotContainsString('<script', $product->description_html);
        $this->assertStringNotContainsString('onerror', $product->description_html);
        $id = $product->variants()->first()->id;
        $data = [['sku' => $product->sku, 'type' => 'simple', 'name' => 'Товар из 1С', 'slug' => 'from-1c', 'variants' => [['sku' => $product->sku, 'name' => '250 мл', 'price_minor' => 70000, 'stock' => 12]]]];
        app(CatalogImporter::class)->import($data);
        app(CatalogImporter::class)->import($data);
        $this->assertSame($id, $product->variants()->first()->id);
        $this->assertSame(12, $product->variants()->first()->stock);
        $this->assertSame('Краткое описание', $product->fresh()->short_description);
        $this->assertSame('/assets/images/product.jpg', $product->fresh()->image);
    }

    public function test_used_attribute_values_cannot_be_deleted_and_import_updates_variable_skus_without_losing_combinations(): void
    {
        $editor = app(ProductEditor::class);
        $config = $this->attributeConfig();
        $product = $editor->save(null, $this->data(['sku' => 'VARIABLE', 'type' => 'variable', 'attribute_config' => $config, 'variants' => $editor->generate($config, [], 'VARIABLE')]));
        $variant = $product->variants()->first();
        app(CatalogImporter::class)->import([['sku' => 'VARIABLE', 'type' => 'variable', 'name' => 'Товар', 'slug' => 'new-cream', 'variants' => [['sku' => $variant->sku, 'name' => $variant->name, 'price_minor' => 99999, 'stock' => 15]]]]);
        $this->assertSame(4, $product->variants()->count());
        $this->assertSame(99999, $variant->fresh()->price_minor);
        $this->assertSame(15, $variant->fresh()->stock);
        $this->assertSame($variant->combination_key, $variant->fresh()->combination_key);
        $this->expectException(ValidationException::class);
        AttributeValue::find($config[0]['value_ids'][0])->delete();
    }

    public function test_scheduled_date_roundtrips_through_admin_and_duplicate_sku_errors_are_shown_on_form(): void
    {
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');
        $editor = app(ProductEditor::class);
        $date = now()->addDays(2)->startOfMinute();
        $product = $editor->save(null, $this->data(['status' => 'scheduled', 'published_at' => $date->toDateTimeString()]));
        Livewire::test(EditProduct::class, ['record' => $product->id])->fillForm(['short_description' => 'Изменено'])->call('save')->assertHasNoFormErrors();
        $this->assertSame($date->timestamp, $product->fresh()->published_at->timestamp);
        $data = $this->data(['sku' => 'demo-vanilla', 'slug' => 'duplicate-sku']);
        $data['image'] = ['selected' => $data['image']];
        Livewire::test(CreateProduct::class)->fillForm($data)->call('create')->assertHasFormErrors(['sku']);
        $this->assertDatabaseMissing('products', ['slug' => 'duplicate-sku']);
    }

    public function test_new_variable_product_can_be_imported_by_skus_and_known_attribute_codes(): void
    {
        $config = $this->attributeConfig();
        $variants = [];
        foreach (['100', '250'] as $size) {
            foreach (['rose', 'vanilla'] as $scent) {
                $variants[] = ['sku' => 'IMPORTED-'.$size.'-'.$scent, 'name' => $size.' / '.$scent, 'attributes' => ['size' => $size, 'scent' => $scent], 'price_minor' => 12345, 'stock' => 4];
            }
        }
        $data = [['sku' => 'IMPORTED', 'type' => 'variable', 'name' => 'Импортированный товар', 'slug' => 'imported-variable', 'variants' => $variants]];
        app(CatalogImporter::class)->import($data);
        $product = Product::where('sku', 'IMPORTED')->firstOrFail();
        $ids = $product->variants()->orderBy('id')->pluck('id')->all();
        app(CatalogImporter::class)->import($data);
        $this->assertSame($ids, $product->variants()->orderBy('id')->pluck('id')->all());
        $this->assertSame('draft', $product->status);
        $this->assertCount(2, $product->attribute_config);
        $this->assertSame(4, $product->variants()->count());
    }

    public function test_attributes_values_and_collections_can_be_created_through_admin(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');
        Livewire::test(ManageProductAttribute::class)->callAction('create', data: ['name' => 'Цвет', 'slug' => 'color', 'values' => [
            ['name' => 'Красный', 'slug' => 'red'], ['name' => 'Белый', 'slug' => 'white'],
        ]])->assertHasNoActionErrors();
        $attribute = ProductAttribute::where('slug', 'color')->firstOrFail();
        $this->assertSame(['red', 'white'], $attribute->values()->orderBy('id')->pluck('slug')->all());
        Livewire::test(ManageProductCollection::class)->callAction('create', data: ['name' => 'Для подарка', 'slug' => 'for-gift'])->assertHasNoActionErrors();
        $this->assertDatabaseHas('product_collections', ['slug' => 'for-gift']);
    }

    public function test_checkout_snapshots_the_selected_variation_sku_and_price(): void
    {
        $editor = app(ProductEditor::class);
        $config = $this->attributeConfig();
        $rows = $editor->generate($config, [], 'VARIABLE');
        foreach ($rows as &$row) {
            $row['price'] = '100.25';
            $row['stock'] = 5;
        }
        unset($row);
        $product = $editor->save(null, $this->data(['sku' => 'VARIABLE', 'type' => 'variable', 'status' => 'published', 'attribute_config' => $config, 'variants' => $rows]));
        $variant = $product->variants()->orderBy('id')->skip(3)->firstOrFail();
        $order = app(Checkout::class)->place(User::factory()->create(), ['checkout_token' => (string) Str::uuid(), 'name' => 'Покупатель', 'email' => 'buyer@example.test', 'phone' => '+79999999999', 'address' => 'Адрес', 'delivery_method' => 'cdek', 'expected_subtotal_minor' => 20050], [$variant->id => 2]);
        $this->assertSame($variant->sku, $order->items()->first()->sku);
        $this->assertSame(10025, $order->items()->first()->price_minor);
        $this->assertSame(20050, $order->subtotal_minor);
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertSame(5, $product->variants()->first()->stock);
    }
}
