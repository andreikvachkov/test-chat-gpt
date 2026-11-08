<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\AdminUser;
use App\Models\ContentEntry;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\Services\CatalogImporter;
use App\Services\Checkout;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, DemoCatalogSeeder::class]);
    }

    private function checkoutData(): array
    {
        return ['checkout_token' => (string) Str::uuid(), 'name' => 'Покупатель', 'email' => 'buyer@example.test', 'phone' => '+79991234567', 'address' => 'Казань, пункт СДЭК', 'delivery_method' => 'cdek', 'consent' => true, 'expected_subtotal_minor' => 62000];
    }

    public function test_marketing_pages_and_catalog_are_inertia_pages(): void
    {
        foreach (['/', '/about', '/history', '/contact', '/blog', '/article', '/document', '/document2', '/catalog', '/products/sultan', '/login'] as $url) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $p) => $p->component('Store'));
        }
    }

    public function test_catalog_search_category_sort_and_pagination(): void
    {
        $this->get('/catalog?q=Vanilla')->assertInertia(fn (Assert $p) => $p->has('products.data', 1)->where('products.data.0.slug', 'vanilla'));
        $this->get('/catalog?category=hair')->assertInertia(fn (Assert $p) => $p->has('products.data', 0));
        Product::where('slug', 'sultan')->first()->variants()->update(['price_minor' => 100]);
        $this->get('/catalog?sort=price_asc')->assertInertia(fn (Assert $p) => $p->where('products.data.0.slug', 'sultan'));
    }

    public function test_unpublished_pages_and_products_are_not_public(): void
    {
        Page::where('slug', 'about')->update(['published' => false]);
        Product::where('slug', 'sultan')->update(['published' => false]);
        $this->get('/about')->assertNotFound();
        $this->get('/products/sultan')->assertNotFound();
        $this->get('/lk-personal-info')->assertNotFound();
    }

    public function test_cms_edits_and_page_overrides_are_sent_to_vue_and_seeding_does_not_erase_them(): void
    {
        $entry = ContentEntry::where('group', 'index')->where('type', 'text')->first();
        $entry->update(['value' => 'Новый текст']);
        $this->seed(DatabaseSeeder::class);
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('content', fn ($content) => $content[$entry->key] === 'Новый текст'));
        Page::where('slug', 'index')->update(['content' => [$entry->key => 'Текст страницы']]);
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('content', fn ($content) => $content[$entry->key] === 'Текст страницы'));
    }

    public function test_registration_login_and_logout_preserve_no_admin_privileges(): void
    {
        $this->post('/register', ['name' => 'Тест', 'email' => 'BUYER@example.test', 'password' => 'Password1234', 'password_confirmation' => 'Password1234', 'consent' => true, 'is_admin' => true])->assertRedirect('/account');
        $user = User::where('email', 'buyer@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('Password1234', $user->password));
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'Password1234'])->assertRedirect('/account');
        $this->assertAuthenticated();
    }

    public function test_cart_requests_do_not_consume_registration_limit_and_login_still_has_its_own_limit(): void
    {
        $variant = Product::first()->variants()->first();
        for ($i = 0; $i < 7; $i++) {
            $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 1])->assertRedirect();
        }
        $this->post('/register', ['name' => 'Покупатель', 'email' => 'cart-registration@example.test', 'password' => 'Password1234', 'password_confirmation' => 'Password1234', 'consent' => true])->assertRedirect('/account');
        $this->post('/logout');
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['email' => 'cart-registration@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'cart-registration@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_password_reset_uses_a_single_use_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123'];
        $this->post('/reset-password', $data)->assertRedirect('/login');
        $this->assertTrue(Hash::check($data['password'], $user->fresh()->password));
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
    }

    public function test_guests_cannot_place_orders_or_read_accounts(): void
    {
        foreach (['/checkout', '/account', '/favourites'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/checkout', $this->checkoutData())->assertRedirect('/login');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cart_rejects_unavailable_stock_and_ignores_client_prices(): void
    {
        $variant = Product::first()->variants()->first();
        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 2, 'price_minor' => 1])->assertSessionHas('cart.'.$variant->id, 2);
        $this->get('/catalog')->assertInertia(fn (Assert $p) => $p->where('cart.subtotal_minor', 124000));
        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 99])->assertSessionHasErrors('cart');
        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 0])->assertSessionMissing('cart.'.$variant->id);
    }

    public function test_checkout_snapshots_prices_reserves_stock_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $variant = Product::first()->variants()->first();
        $data = $this->checkoutData();
        $this->actingAs($user)->withSession(['cart' => [$variant->id => 1], 'checkout_token' => $data['checkout_token']])->post('/checkout', $data + ['subtotal_minor' => 1])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame(62000, $order->subtotal_minor);
        $this->assertNull($order->shipping_minor);
        $this->assertSame('not_started', $order->payment_status);
        $this->assertSame(19, $variant->fresh()->stock);
        $this->assertSame($variant->sku, $order->items()->first()->sku);
        $this->post('/checkout', $data)->assertRedirect('/account/orders/'.$order->number);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(19, $variant->fresh()->stock);
    }

    public function test_changed_prices_or_stock_leave_no_partial_order(): void
    {
        $user = User::factory()->create();
        $variant = Product::first()->variants()->first();
        $data = $this->checkoutData();
        $variant->update(['price_minor' => 65000]);
        $this->actingAs($user)->withSession(['cart' => [$variant->id => 1], 'checkout_token' => $data['checkout_token']])->post('/checkout', $data)->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(20, $variant->fresh()->stock);
        $variant->update(['price_minor' => 62000, 'stock' => 0]);
        $this->post('/checkout', $data)->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_details_belong_only_to_the_customer(): void
    {
        $owner = User::factory()->create();
        $variant = Product::first()->variants()->first();
        $order = app(Checkout::class)->place($owner, $this->checkoutData(), [$variant->id => 1]);
        $this->actingAs(User::factory()->create())->get('/account/orders/'.$order->number)->assertNotFound();
        $this->actingAs($owner)->get('/account/orders/'.$order->number)->assertOk();
    }

    public function test_admin_is_forbidden_for_customers_and_all_resources_render_for_admins(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer, 'web')->get('/admin/products')->assertRedirect('/admin/login');
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');
        foreach (['staff', 'roles', 'products', 'product-collections', 'product-attributes', 'categories', 'articles', 'product-reviews', 'pages', 'media-assets', 'orders', 'users'] as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
        }
    }

    public function test_normalized_import_is_idempotent_and_keeps_editorial_fields(): void
    {
        $data = [['sku' => '1C-001', 'type' => 'simple', 'name' => 'Товар 1С', 'slug' => 'from-1c', 'variants' => [['sku' => '1C-001', 'name' => '100 мл', 'price_minor' => 55000, 'stock' => 4]]]];
        $importer = app(CatalogImporter::class);
        $importer->import($data);
        $p = Product::where('sku', '1C-001')->firstOrFail();
        $p->update(['usage' => 'Инструкция', 'image' => '/assets/images/product.jpg']);
        $data[0]['variants'][0]['stock'] = 7;
        $importer->import($data);
        $this->assertSame(1, Product::where('sku', '1C-001')->count());
        $this->assertSame(1, $p->variants()->count());
        $this->assertSame(7, $p->variants()->first()->stock);
        $this->assertSame('Инструкция', $p->fresh()->usage);
        $this->assertFalse($p->fresh()->published);
    }

    public function test_import_collision_rolls_back_entire_batch(): void
    {
        $before = Product::count();
        $data = [['sku' => 'COLLISION', 'type' => 'simple', 'slug' => 'new-item', 'name' => 'Ошибка', 'variants' => [['sku' => 'DEMO-VANILLA', 'name' => '100 мл', 'price_minor' => 1, 'stock' => 1]]]];
        try {
            app(CatalogImporter::class)->import($data);
            $this->fail('Expected validation error');
        } catch (ValidationException) {
            $this->assertSame($before, Product::count());
        }
    }

    public function test_unconfigured_integrations_cannot_report_success(): void
    {
        $this->expectException(\LogicException::class);
        app(PaymentGateway::class)->createPayment(new Order);
    }

    public function test_next_checkout_gets_a_new_token_and_creates_a_second_order(): void
    {
        $user = User::factory()->create();
        $variant = Product::first()->variants()->first();
        $data = $this->checkoutData();
        $this->actingAs($user)->withSession(['cart' => [$variant->id => 1], 'checkout_token' => $data['checkout_token']])->post('/checkout', $data)->assertRedirect();
        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 1]);
        $this->get('/checkout')->assertOk();
        $next = session('checkout_token');
        $this->assertNotSame($data['checkout_token'], $next);
        $data['checkout_token'] = $next;
        $this->post('/checkout', $data)->assertRedirect();
        $this->assertDatabaseCount('orders', 2);
        $this->assertSame(18, $variant->fresh()->stock);
    }

    public function test_profile_and_favourites_are_persisted(): void
    {
        $user = User::factory()->create();
        $product = Product::first();
        $this->actingAs($user);
        $this->patch('/account', ['name' => 'Анна', 'last_name' => 'Иванова', 'phone' => '+79990000000', 'birthdate' => '1990-01-01'])->assertSessionHasNoErrors();
        $this->assertSame('Иванова', $user->fresh()->last_name);
        $this->post('/favourites/'.$product->id)->assertRedirect();
        $this->get('/favourites')->assertInertia(fn (Assert $p) => $p->has('products.data', 1));
        $this->post('/favourites/'.$product->id)->assertRedirect();
        $this->assertSame(0, $user->favourites()->count());
    }

    public function test_removed_content_section_has_no_admin_route_and_keeps_storefront_data(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');
        $entry = ContentEntry::where('group', 'index')->where('type', 'text')->first();
        $this->get('/admin/content-entries')->assertNotFound();
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('content', fn ($content) => $content[$entry->key] === $entry->value));
    }

    public function test_a_new_cms_page_uses_its_own_metadata_and_content(): void
    {
        $entry = ContentEntry::where('group', 'article')->where('type', 'text')->first();
        Page::create(['slug' => 'new-article', 'title' => 'Новая статья', 'template' => 'article', 'published' => true, 'content' => [$entry->key => 'Уникальный текст']]);
        $this->get('/new-article')->assertInertia(fn (Assert $p) => $p->where('meta.title', 'Новая статья')->where('content', fn ($c) => $c[$entry->key] === 'Уникальный текст'));
        $this->get('/missing-page')->assertNotFound()->assertInertia(fn (Assert $p) => $p->where('template', '404'));
    }

    public function test_catalog_search_is_case_insensitive_for_latin_and_cyrillic(): void
    {
        $this->get('/catalog?q=vanilla')->assertInertia(fn (Assert $p) => $p->has('products.data', 1)->where('products.data.0.slug', 'vanilla'));
        $this->get('/catalog?q=ПАРФЮМИРОВАННЫЙ')->assertInertia(fn (Assert $p) => $p->has('products.data', 3));
    }

    #[DataProvider('negativeVariantValues')]
    public function test_postgresql_rejects_negative_prices_and_stock(string $column): void
    {
        $variant = Product::first()->variants()->first();
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('product_variants_nonnegative');
        DB::table('product_variants')->where('id', $variant->id)->update([$column => -1]);
    }

    public static function negativeVariantValues(): array
    {
        return [['price_minor'], ['stock']];
    }
}
