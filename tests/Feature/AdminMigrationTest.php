<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_old_admin_credentials_are_moved_and_customer_order_links_are_preserved(): void
    {
        while (Schema::hasTable('admin_users')) {
            $this->artisan('migrate:rollback', ['--step' => 1, '--force' => true])->assertSuccessful();
        }
        $plain = User::factory()->create(['is_admin' => true, 'password' => 'LegacyPassword123']);
        $buyerAdmin = User::factory()->create(['is_admin' => true, 'password' => 'LegacyBuyerPass123']);
        $profileAdmin = User::factory()->create(['is_admin' => true, 'phone' => '+79999999999']);
        $customer = User::factory()->create();
        $this->seed(DemoCatalogSeeder::class);
        $variant = Product::first()->variants()->first();
        $order = $buyerAdmin->orders()->create(['number' => (string) Str::uuid(), 'checkout_token' => (string) Str::uuid(), 'name' => 'Покупатель', 'email' => $buyerAdmin->email, 'phone' => '+79991234567', 'address' => 'Адрес', 'delivery_method' => 'cdek', 'subtotal_minor' => 62000]);
        $order->items()->create(['product_variant_id' => $variant->id, 'sku' => $variant->sku, 'name' => 'Существующий товар', 'quantity' => 1, 'price_minor' => 62000]);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertNull(User::find($plain->id));
        $this->assertTrue(Hash::check('LegacyPassword123', AdminUser::where('email', $plain->email)->firstOrFail()->password));
        $this->assertSame('owner', AdminUser::where('email', $plain->email)->firstOrFail()->role->code);
        $this->assertSame($buyerAdmin->id, $order->fresh()->user_id);
        $this->assertFalse(User::findOrFail($buyerAdmin->id)->is_admin);
        $this->assertSame($order->id, User::findOrFail($buyerAdmin->id)->orders()->first()->id);
        $this->assertSame('+79999999999', User::findOrFail($profileAdmin->id)->phone);
        $this->assertTrue(Hash::check('LegacyBuyerPass123', AdminUser::where('email', $buyerAdmin->email)->firstOrFail()->password));
        $this->assertNotNull(User::find($customer->id));
        $this->assertFalse(AdminUser::where('email', $customer->email)->exists());
        $this->assertDatabaseCount('admin_users', 3);
    }
}
