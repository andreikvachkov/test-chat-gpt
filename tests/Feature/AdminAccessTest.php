<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\Pages\CreateAdminRole;
use App\Filament\Resources\Pages\CreateAdminUser;
use App\Filament\Resources\Pages\EditAdminUser;
use App\Filament\Resources\Pages\EditProduct;
use App\Filament\Resources\Pages\ListProducts;
use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\Product;
use App\Models\User;
use App\Services\AdminAccountManager;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, DemoCatalogSeeder::class]);
    }

    private function staff(string $code): AdminUser
    {
        return AdminUser::factory()->create(['admin_role_id' => AdminRole::where('code', $code)->firstOrFail()->id]);
    }

    public function test_customers_cannot_enter_admin_even_with_legacy_flag_or_injected_registration_fields(): void
    {
        $this->post('/register', ['name' => 'Покупатель', 'email' => 'customer@example.test', 'password' => 'CustomerPass123', 'password_confirmation' => 'CustomerPass123', 'consent' => true, 'is_admin' => true, 'admin_role_id' => AdminRole::where('code', 'owner')->value('id'), 'active' => true])->assertRedirect('/account');
        $customer = User::where('email', 'customer@example.test')->firstOrFail();
        $this->assertDatabaseCount('admin_users', 0);
        $this->assertFalse($customer->is_admin);
        $this->get('/admin/products')->assertRedirect('/admin/login');
        $customer->forceFill(['is_admin' => true])->save();
        $this->assertFalse($customer->canAccessPanel(Filament::getPanel('admin')));
        Livewire::test(Login::class)->fillForm(['email' => $customer->email, 'password' => 'CustomerPass123'])->call('authenticate')->assertHasFormErrors(['email']);
        $this->assertGuest('admin');
    }

    public function test_same_email_can_have_independent_customer_and_staff_credentials_and_guards(): void
    {
        $customer = User::factory()->create(['email' => 'shared@example.test', 'password' => 'CustomerPass123']);
        $staff = $this->staff('catalog_manager');
        $staff->update(['email' => $customer->email, 'password' => 'StaffPassword123']);
        $this->post('/login', ['email' => $customer->email, 'password' => 'CustomerPass123'])->assertRedirect('/account');
        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertGuest('admin');
        Livewire::test(Login::class)->fillForm(['email' => 'SHARED@example.test', 'password' => 'StaffPassword123'])->call('authenticate')->assertHasNoFormErrors();
        $this->assertAuthenticatedAs($staff, 'admin');
        $this->assertAuthenticatedAs($customer, 'web');
        $this->get('/account')->assertOk()->assertInertia(fn ($p) => $p->where('auth.user.name', $customer->name));
        $this->assertTrue(Hash::check('CustomerPass123', $customer->fresh()->password));
        $this->assertTrue(Hash::check('StaffPassword123', $staff->fresh()->password));
    }

    public function test_staff_login_does_not_authenticate_as_a_customer_or_allow_checkout(): void
    {
        $staff = $this->staff('owner');
        $this->actingAs($staff, 'admin');
        $this->get('/account')->assertRedirect('/login');
        $this->post('/checkout')->assertRedirect('/login');
        $this->assertGuest('web');
        $this->assertDatabaseCount('users', 0);
        $this->post('/login', ['email' => $staff->email, 'password' => 'AdminPassword123'])->assertSessionHasErrors('email');
    }

    public static function roleCases(): array
    {
        return [
            ['sales_manager', ['orders', 'users']],
            ['catalog_manager', ['products', 'categories', 'product-attributes', 'product-collections', 'product-reviews', 'media-assets']],
            ['content_editor', ['articles', 'pages', 'media-assets']],
            ['shop_manager', ['orders', 'users', 'products', 'categories', 'product-attributes', 'product-collections', 'media-assets', 'articles', 'pages', 'product-reviews']],
        ];
    }

    #[DataProvider('roleCases')]
    public function test_default_roles_enforce_direct_routes_and_only_owners_manage_staff(string $code, array $allowed): void
    {
        $this->actingAs($this->staff($code), 'admin');
        foreach (['orders', 'users', 'products', 'categories', 'product-attributes', 'product-collections', 'media-assets', 'articles', 'pages', 'product-reviews', 'staff', 'roles'] as $resource) {
            $response = $this->get('/admin/'.$resource);
            in_array($resource, $allowed) ? $response->assertOk() : $response->assertForbidden();
        }
    }

    public function test_read_only_role_can_open_product_but_cannot_create_edit_delete_or_restore(): void
    {
        $role = AdminRole::create(['name' => 'Читатель', 'permissions' => ['products' => ['view']]]);
        $staff = AdminUser::factory()->create(['admin_role_id' => $role->id]);
        $this->actingAs($staff, 'admin');
        $product = Product::first();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/products/'.$product->id)->assertOk();
        $this->get('/admin/products/create')->assertForbidden();
        $this->get('/admin/products/'.$product->id.'/edit')->assertForbidden();
        Livewire::test(ListProducts::class)->assertActionHidden(TestAction::make('delete')->table($product));
        Livewire::test(ListProducts::class)->assertActionHidden('create')
            ->call('mountAction', 'delete', [], ['table' => true, 'recordKey' => $product->id])
            ->call('callMountedAction');
        $this->assertFalse(Gate::forUser($staff)->allows('delete', $product));
        $this->assertFalse(Gate::forUser($staff)->allows('restore', $product));
        $this->assertFalse(Gate::forUser($staff)->allows('forceDelete', $product));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_permission_revocation_is_checked_when_saving_an_already_open_product_form(): void
    {
        $role = AdminRole::create(['name' => 'Редактор', 'permissions' => ['products' => ['view', 'update']]]);
        $staff = AdminUser::factory()->create(['admin_role_id' => $role->id]);
        $this->actingAs($staff, 'admin');
        $product = Product::first();
        $name = $product->name;
        $component = Livewire::test(EditProduct::class, ['record' => $product->id])->fillForm(['name' => 'Запрещённое изменение']);
        $role->update(['permissions' => ['products' => ['view']]]);
        Auth::guard('admin')->setUser($staff->fresh());
        $component->call('save')->assertForbidden();
        $this->assertSame($name, $product->fresh()->name);
    }

    public function test_owner_creates_separate_staff_and_changes_role_without_touching_customer_password(): void
    {
        $owner = $this->staff('owner');
        $this->actingAs($owner, 'admin');
        $customer = User::factory()->create(['email' => 'shared-staff@example.test']);
        $oldHash = $customer->password;
        Livewire::test(CreateAdminUser::class)->fillForm(['name' => 'Сотрудник', 'email' => $customer->email, 'password' => 'StaffPassword123', 'admin_role_id' => AdminRole::where('code', 'sales_manager')->value('id'), 'active' => true])->call('create')->assertHasNoFormErrors();
        $staff = AdminUser::where('email', $customer->email)->firstOrFail();
        $this->assertSame('sales_manager', $staff->role->code);
        $this->assertTrue(Hash::check('StaffPassword123', $staff->password));
        Livewire::test(EditAdminUser::class, ['record' => $staff->id])->fillForm(['active' => false, 'password' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertFalse($staff->fresh()->active);
        $this->assertSame($oldHash, $customer->fresh()->password);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_owner_creates_custom_role_but_cannot_inject_staff_administration_permissions(): void
    {
        $this->actingAs($this->staff('owner'), 'admin');
        Livewire::test(CreateAdminRole::class)->fillForm(['name' => 'Только заказы', 'permissions' => ['orders' => ['view']]])->call('create')->assertHasNoFormErrors();
        $role = AdminRole::where('name', 'Только заказы')->firstOrFail();
        $staff = AdminUser::factory()->create(['admin_role_id' => $role->id]);
        $this->assertTrue($staff->hasPermission('orders', 'view'));
        $this->assertFalse($staff->hasPermission('orders', 'update'));
        $this->assertFalse($staff->hasPermission('staff', 'create'));
        $this->expectException(ValidationException::class);
        $role->update(['permissions' => ['staff' => ['view', 'create']]]);
    }

    public function test_last_active_owner_cannot_be_blocked_or_demoted(): void
    {
        $owner = $this->staff('owner');
        try {
            app(AdminAccountManager::class)->save($owner, $owner, ['name' => $owner->name, 'email' => $owner->email, 'admin_role_id' => AdminRole::where('code', 'sales_manager')->value('id'), 'active' => false]);
            $this->fail('Expected last-owner protection');
        } catch (ValidationException) {
            $this->assertTrue($owner->fresh()->isOwner());
        }
        $this->expectException(ValidationException::class);
        $owner->role->update(['permissions' => []]);
    }

    public function test_role_form_requires_view_permission_for_other_actions(): void
    {
        $this->actingAs($this->staff('owner'), 'admin');
        Livewire::test(CreateAdminRole::class)->fillForm(['name' => 'Неверная роль', 'permissions' => ['products' => ['update']]])
            ->call('create')->assertHasFormErrors(['permissions.products']);
        $this->assertDatabaseMissing('admin_roles', ['name' => 'Неверная роль']);
    }

    public function test_blocked_staff_and_non_owners_cannot_manage_accounts(): void
    {
        $staff = $this->staff('sales_manager');
        $owner = $this->staff('owner');
        try {
            app(AdminAccountManager::class)->save($staff, $owner, ['name' => $owner->name, 'email' => $owner->email, 'admin_role_id' => $staff->admin_role_id, 'active' => false]);
            $this->fail('Expected access denial');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $staff->update(['active' => false]);
        $this->actingAs($staff->fresh(), 'admin')->get('/admin/orders')->assertForbidden();
        Auth::guard('admin')->logout();
        Livewire::test(Login::class)->fillForm(['email' => $staff->email, 'password' => 'AdminPassword123'])->call('authenticate')->assertHasFormErrors(['email']);
        $this->assertGuest('admin');
    }

    public function test_cli_creates_an_independent_owner_account_without_promoting_the_customer(): void
    {
        $customer = User::factory()->create();
        $hash = $customer->password;
        $this->artisan('shop:admin', ['email' => $customer->email])->expectsQuestion('Имя администратора', 'Владелец магазина')
            ->expectsQuestion('Пароль (не менее 12 символов, буквы и цифры)', 'OwnerPassword123')->assertSuccessful();
        $staff = AdminUser::where('email', $customer->email)->firstOrFail();
        $this->assertTrue($staff->isOwner());
        $this->assertFalse($customer->fresh()->is_admin);
        $this->assertSame($hash, $customer->fresh()->password);
    }
}
