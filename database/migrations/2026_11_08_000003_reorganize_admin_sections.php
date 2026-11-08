<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('rating');
            $table->text('body');
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE product_reviews ADD CONSTRAINT product_reviews_rating_check CHECK (rating BETWEEN 1 AND 5), ADD CONSTRAINT product_reviews_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
        foreach (DB::table('admin_roles')->get() as $role) {
            $permissions = json_decode($role->permissions, true);
            $permissions['articles'] ??= $permissions['pages'] ?? [];
            $permissions['reviews'] ??= array_values(array_intersect($permissions['products'] ?? [], ['view', 'update', 'delete']));
            unset($permissions['content']);
            DB::table('admin_roles')->where('id', $role->id)->update(['permissions' => json_encode($permissions)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
        foreach (DB::table('admin_roles')->get() as $role) {
            $permissions = json_decode($role->permissions, true);
            unset($permissions['articles'], $permissions['reviews']);
            DB::table('admin_roles')->where('id', $role->id)->update(['permissions' => json_encode($permissions)]);
        }
    }
};
