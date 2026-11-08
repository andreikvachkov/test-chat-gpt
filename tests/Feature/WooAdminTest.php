<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\CreateProduct;
use App\Filament\Resources\Pages\ListProducts;
use App\Filament\Resources\Pages\ManageCategory;
use App\Filament\Resources\Pages\ManageMediaAsset;
use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Services\CategoryTree;
use App\Services\Checkout;
use App\Services\ProductEditor;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class WooAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, DemoCatalogSeeder::class]);
        $this->actingAs(AdminUser::factory()->create(), 'admin');
    }

    private function data(array $changes = []): array
    {
        return array_replace(['name' => 'Новый товар', 'slug' => 'woo-product', 'sku' => 'WOO-SKU', 'status' => 'published', 'type' => 'simple', 'simple' => ['price' => '1000', 'stock' => 5], 'category_ids' => [], 'collection_ids' => [], 'attribute_config' => [], 'image' => null, 'gallery' => []], $changes);
    }

    private function orderData(int $total): array
    {
        return ['checkout_token' => (string) Str::uuid(), 'name' => 'Клиент', 'email' => 'client@example.test', 'phone' => '+79991234567', 'address' => 'Адрес', 'delivery_method' => 'cdek', 'expected_subtotal_minor' => $total];
    }

    public function test_product_table_has_three_columns_and_create_defaults_to_simple_without_placeholder(): void
    {
        $table = Livewire::test(ListProducts::class)->instance()->getTable();
        $this->assertSame(['name', 'status', 'published_at'], array_keys($table->getColumns()));
        Livewire::test(CreateProduct::class)->assertSet('data.type', 'simple');
        $this->get('/admin/products/create')->assertOk()->assertSee('Запасы')->assertSee('Доставка')->assertSee('Дополнительно')->assertDontSee('Сопутствующие');
    }

    public function test_categories_stay_in_tree_order_and_descendants_are_not_available_as_parents(): void
    {
        $parent = Category::create(['name' => 'А Родитель', 'slug' => 'tree-parent']);
        $second = Category::create(['name' => 'Б Другой', 'slug' => 'tree-other']);
        $child = Category::create(['name' => 'Я Дочерняя', 'slug' => 'tree-child', 'parent_id' => $parent->id]);
        $grandchild = Category::create(['name' => 'А Внучка', 'slug' => 'tree-grandchild', 'parent_id' => $child->id]);
        $nodes = collect(CategoryTree::nodes())->keyBy('id');
        $ids = $nodes->keys()->all();
        $this->assertSame(array_search($parent->id, $ids) + 1, array_search($child->id, $ids));
        $this->assertSame(array_search($child->id, $ids) + 1, array_search($grandchild->id, $ids));
        $this->assertGreaterThan(array_search($grandchild->id, $ids), array_search($second->id, $ids));
        $this->assertSame(2, $nodes[$grandchild->id]['depth']);
        $this->assertArrayNotHasKey($grandchild->id, CategoryTree::options($parent));
        Livewire::test(ManageCategory::class)->assertCanSeeTableRecords([$parent, $child, $grandchild, $second], inOrder: true);
    }

    public function test_sale_is_immediate_and_preserves_regular_price_and_shipping_data(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 10)->setTime(12, 0));
        $editor = app(ProductEditor::class);
        $product = $editor->save(null, $this->data(['simple' => ['price' => '1000', 'stock' => 5, 'sale_price' => '800', 'sale_starts_at' => '2026-11-10', 'sale_ends_at' => '2026-11-11'], 'details' => ['weight' => '0.25', 'length' => '10', 'width' => '5', 'height' => '15', 'gtin' => '4600000000001', 'purchase_note' => 'Спасибо за покупку', 'menu_order' => 3]]));
        $variant = $product->variants()->first();
        $this->assertSame(80000, $variant->price_minor);
        $this->assertSame('1000.00', $editor->state($product)['simple']['price']);
        $this->assertSame('0.25', $product->details['weight']);
        $order = app(Checkout::class)->place(User::factory()->create(), $this->orderData(80000), [$variant->id => 1]);
        $this->assertSame(80000, $order->items->first()->price_minor);
        $this->assertNull($order->items->first()->purchase_note);
        $this->assertArrayNotHasKey('gtin', $product->details);
        $this->assertArrayNotHasKey('purchase_note', $product->details);
        $this->travelTo(now()->addDays(3));
        $this->assertSame(80000, $variant->fresh()->price_minor);
        $state = $editor->state($product);
        $state['simple']['sale_price'] = '1001';
        $this->expectException(ValidationException::class);
        $editor->save($product, $state);
    }

    public function test_unmanaged_stock_and_backorders_are_enforced_and_removed_individual_limit_is_ignored(): void
    {
        $editor = app(ProductEditor::class);
        $product = $editor->save(null, $this->data(['simple' => ['price' => '1000', 'stock' => 0], 'details' => ['manage_stock' => false, 'stock_status' => 'instock']]));
        $variant = $product->variants->first();
        $this->get('/catalog?sort=stock')->assertOk()->assertInertia(fn ($page) => $page->where('products.data', fn ($items) => collect($items)->contains('sku', 'WOO-SKU')));
        app(Checkout::class)->place(User::factory()->create(), $this->orderData(200000), [$variant->id => 2]);
        $this->assertSame(0, $variant->fresh()->stock);
        $state = $editor->state($product);
        $state['details']['manage_stock'] = true;
        $state['details']['backorders'] = 'notify';
        $editor->save($product, $state);
        $order = app(Checkout::class)->place(User::factory()->create(), $this->orderData(200000), [$variant->id => 2]);
        $this->assertSame(-2, $variant->fresh()->stock);
        $this->assertTrue($order->items->first()->backordered);
        $state = $editor->state($product);
        $state['details']['sold_individually'] = true;
        $editor->save($product, $state);
        app(Checkout::class)->place(User::factory()->create(), $this->orderData(200000), [$variant->id => 2]);
        $this->assertSame(-4, $variant->fresh()->stock);
    }

    public function test_two_variations_cannot_exceed_shared_parent_stock(): void
    {
        $attribute = ProductAttribute::create(['name' => 'Объём', 'slug' => 'woo-volume']);
        $attribute->values()->createMany([['name' => '100 мл', 'slug' => '100'], ['name' => '250 мл', 'slug' => '250']]);
        $config = [['attribute_id' => $attribute->id, 'value_ids' => $attribute->values->pluck('id')->all(), 'visible' => true, 'for_variations' => true]];
        $editor = app(ProductEditor::class);
        $rows = $editor->generate($config, [], 'WOO-SKU');
        foreach ($rows as &$row) {
            $row['price'] = '1000';
            $row['details'] = ['manage_stock' => false];
        }
        unset($row);
        $product = $editor->save(null, $this->data(['type' => 'variable', 'attribute_config' => $config, 'variants' => $rows, 'details' => ['manage_stock' => true], 'inventory_stock' => 1]));
        try {
            app(Checkout::class)->place(User::factory()->create(), $this->orderData(200000), $product->variants->mapWithKeys(fn ($v) => [$v->id => 1])->all());
            $this->fail('Shared stock must be checked for the entire order');
        } catch (ValidationException) {
            $this->assertSame(1, $product->fresh()->inventory_stock);
            $this->assertDatabaseCount('orders', 0);
        }
    }

    public function test_media_grid_uploads_images_and_saves_metadata_without_exposing_upload_to_readers(): void
    {
        Storage::fake('public');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN1QAAAAASUVORK5CYII=');
        Livewire::test(ManageMediaAsset::class)->callAction('uploadFiles', data: ['files' => [UploadedFile::fake()->createWithContent('first.png', $png), UploadedFile::fake()->createWithContent('second.png', $png)]])->assertHasNoActionErrors();
        $asset = MediaAsset::where('name', 'first.png')->firstOrFail();
        $this->assertSame(1, $asset->width);
        $this->assertSame('image/png', $asset->mime_type);
        Storage::disk('public')->assertExists($asset->path);
        Livewire::test(ManageMediaAsset::class)->callAction(TestAction::make('edit')->table($asset), data: ['name' => 'Новое название', 'alt_text' => 'Описание изображения', 'caption' => 'Подпись'])->assertHasNoActionErrors();
        $this->assertSame('Описание изображения', $asset->fresh()->alt_text);
        $role = AdminRole::create(['name' => 'Просмотр медиа', 'permissions' => ['media' => ['view']]]);
        $staff = AdminUser::factory()->create(['admin_role_id' => $role->id]);
        $this->actingAs($staff, 'admin');
        Livewire::test(ManageMediaAsset::class)->assertActionHidden('uploadFiles')->assertActionHidden(TestAction::make('edit')->table($asset))->assertActionVisible(TestAction::make('view')->table($asset));
    }
}
