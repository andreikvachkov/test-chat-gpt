<?php

use App\Support\AdminPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->json('permissions');
            $t->timestamps();
        });
        Schema::create('admin_users', function (Blueprint $t) {
            $t->id();
            $t->foreignId('admin_role_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->boolean('active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('admin_password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });
        $now = now();
        foreach (AdminPermissions::roles() as $code => $role) {
            DB::table('admin_roles')->insert(['code' => $code, 'name' => $role['name'], 'permissions' => json_encode($role['permissions']), 'created_at' => $now, 'updated_at' => $now]);
        }
        $owner = DB::table('admin_roles')->where('code', 'owner')->value('id');
        foreach (DB::table('users')->where('is_admin', true)->orderBy('id')->get() as $user) {
            DB::table('admin_users')->insert(['admin_role_id' => $owner, 'name' => $user->name, 'email' => $user->email, 'password' => $user->password, 'active' => true, 'remember_token' => null, 'created_at' => $user->created_at ?? $now, 'updated_at' => $now]);
            // Keep a separate customer identity only when it already owns shop data.
            if (DB::table('orders')->where('user_id', $user->id)->exists() || DB::table('favourites')->where('user_id', $user->id)->exists()
                || filled($user->phone) || filled($user->last_name) || filled($user->middle_name) || filled($user->birthdate)) {
                DB::table('users')->where('id', $user->id)->update(['is_admin' => false, 'remember_token' => null]);
            } else {
                DB::table('users')->where('id', $user->id)->delete();
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('admin_users')->get() as $admin) {
            if (! DB::table('users')->where('email', $admin->email)->exists()) {
                DB::table('users')->insert(['name' => $admin->name, 'email' => $admin->email, 'password' => $admin->password, 'is_admin' => true, 'created_at' => $admin->created_at, 'updated_at' => $admin->updated_at]);
            }
        }
        Schema::dropIfExists('admin_password_reset_tokens');
        Schema::dropIfExists('admin_users');
        Schema::dropIfExists('admin_roles');
    }
};
