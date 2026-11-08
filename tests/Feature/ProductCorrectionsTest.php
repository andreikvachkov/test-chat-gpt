<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\EditProduct;
use App\Models\AdminUser;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Services\ProductEditor;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCorrectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, DemoCatalogSeeder::class]);
        $this->actingAs(AdminUser::factory()->create(), 'admin');
    }

    private function variable(): Product
    {
        $attribute = ProductAttribute::create(['name' => 'Аромат', 'slug' => 'correction-scent']);
        $attribute->values()->createMany([['name' => 'Sultan', 'slug' => 'sultan'], ['name' => 'Vanilla', 'slug' => 'vanilla']]);
        $config = [['attribute_id' => $attribute->id, 'value_ids' => $attribute->values->pluck('id')->all(), 'visible' => true, 'for_variations' => true]];
        $editor = app(ProductEditor::class);
        $rows = $editor->generate($config, [], 'CORRECTION');
        foreach ($rows as &$row) {
            $row['price'] = '700';
            $row['stock'] = 12;
        }
        unset($row);

        return $editor->save(null, ['name' => 'Тест вариаций', 'slug' => 'correction-variable', 'sku' => 'CORRECTION', 'type' => 'variable', 'status' => 'published', 'attribute_config' => $config, 'variants' => $rows, 'category_ids' => [], 'collection_ids' => [], 'image' => null, 'gallery' => []]);
    }

    public function test_removed_fields_are_absent_for_parent_and_variations(): void
    {
        $product = $this->variable();
        foreach (['/admin/products/create', '/admin/products/'.$product->id.'/edit'] as $url) {
            $response = $this->get($url)->assertOk();
            foreach (['Распродажа с', 'Распродажа по', 'Налоговый статус', 'Налоговый класс', 'GTIN', 'MPN', 'Продавать индивидуально', 'Название варианта / объём', 'Класс доставки', 'Включить отзывы', 'Примечание к покупке', 'Описание вариации'] as $label) {
                $response->assertDontSee($label);
            }
        }
    }

    public function test_switching_simple_to_variable_does_not_create_a_phantom_variation_and_keeps_legacy_name(): void
    {
        $product = Product::where('type', 'simple')->firstOrFail();
        $oldVariant = $product->variants->first();
        $name = $oldVariant->name;
        $config = $product->attribute_config;
        $oldVariant->update(['combination_key' => $config[0]['attribute_id'].':'.$config[0]['value_ids'][0], 'attribute_value_ids' => $config[0]['value_ids']]);
        Livewire::test(EditProduct::class, ['record' => $product->id])->assertSet('data.variants', [])
            ->assertSet('data.variation_cache', [])->set('data.type', 'variable')->assertSet('data.variants', []);
        $config[0]['for_variations'] = true;
        $newRows = app(ProductEditor::class)->generate($config, [], $product->sku, $product);
        $this->assertNotSame($product->sku, $newRows[0]['sku']);
        $this->assertArrayNotHasKey('id', $newRows[0]);
        $state = app(ProductEditor::class)->state($product);
        unset($state['simple']['name']);
        app(ProductEditor::class)->save($product, $state);
        $this->assertSame($name, $oldVariant->fresh()->name);
    }

    public function test_disabling_variation_attribute_clears_form_and_regeneration_recovers_saved_values(): void
    {
        $product = $this->variable();
        $before = $product->variants->map->only(['id', 'sku', 'stock', 'price_minor'])->all();
        $form = Livewire::test(EditProduct::class, ['record' => $product->id]);
        $key = array_key_first($form->get('data.attribute_config'));
        $form->set('data.attribute_config.'.$key.'.for_variations', false)->assertSet('data.variants', []);
        $this->assertSame($before, $product->fresh()->variants->map->only(['id', 'sku', 'stock', 'price_minor'])->all());
        $form->set('data.attribute_config.'.$key.'.for_variations', true);
        $action = collect($form->instance()->getSchema('form')->getFlatComponents())->first(fn ($component) => $component instanceof Action && $component->getName() === 'generateVariations');
        $form->call('mountAction', 'generateVariations', [], ['schemaComponent' => $action->getSchemaContainer()->getKey()])->call('callMountedAction')->assertHasNoActionErrors();
        $this->assertCount(2, $form->get('data.variants'));
        foreach ($form->get('data.variants') as $row) {
            $this->assertSame('700.00', $row['price']);
            $this->assertSame(12, $row['stock']);
        }
        $form->call('save')->assertHasNoFormErrors();
        $this->assertSame($before, $product->fresh()->variants->map->only(['id', 'sku', 'stock', 'price_minor'])->all());
    }

    public function test_variations_without_selected_attributes_are_hidden_and_stock_source_is_explicit(): void
    {
        $product = $this->variable();
        $state = app(ProductEditor::class)->state($product);
        $state['details']['manage_stock'] = true;
        $state['inventory_stock'] = 25;
        foreach ($state['variants'] as &$row) {
            $row['details']['manage_stock'] = false;
        }
        unset($row);
        $product = app(ProductEditor::class)->save($product, $state);
        $this->get('/admin/products/'.$product->id.'/edit')->assertOk()->assertSee('Общий остаток товара')->assertSee('Используется общий остаток родителя: 25 шт.')->assertDontSee('Статус наличия');
        $config = $product->attribute_config;
        $config[0]['for_variations'] = false;
        $product->update(['attribute_config' => $config]);
        $this->assertSame([], app(ProductEditor::class)->state($product)['variants']);
        $this->assertSame(2, $product->variants()->count());
    }

    public function test_removing_schedule_keeps_current_prices_and_removes_hidden_legacy_behaviour(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 10)->setTime(12, 0));
        $variants = Product::with('variants')->get()->pluck('variants')->flatten()->values();
        foreach (['active', 'future', 'expired'] as $index => $kind) {
            DB::table('product_variants')->where('id', $variants[$index]->id)->update(['sale_price_minor' => 50000, 'sale_starts_at' => $kind === 'future' ? now()->addDay() : now()->subDays(3), 'sale_ends_at' => $kind === 'expired' ? now()->subDay() : now()->addDays(3)]);
        }
        $migration = require database_path('migrations/2026_11_08_000005_remove_sale_schedules.php');
        $migration->up();
        $this->assertSame(50000, $variants[0]->fresh()->price_minor);
        foreach ([1, 2] as $index) {
            $this->assertNull($variants[$index]->fresh()->sale_price_minor);
            $this->assertNull($variants[$index]->fresh()->sale_starts_at);
            $this->assertNull($variants[$index]->fresh()->sale_ends_at);
        }
        $product = $variants[0]->product;
        $product->update(['details' => ['sold_individually' => true, 'reviews_enabled' => false, 'purchase_note' => 'Старое примечание']]);
        $this->assertTrue($product->reviews_enabled);
        $this->assertTrue($product->variants->first()->canSupply(2));
        $this->assertArrayNotHasKey('purchase_note', $product->settings());
    }
}
