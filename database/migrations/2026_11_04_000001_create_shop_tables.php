<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_admin')->default(false);
            $t->string('phone')->nullable();
        });
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('template');
            $t->text('seo_description')->nullable();
            $t->boolean('published')->default(true);
            $t->timestamps();
        });
        Schema::create('content_entries', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('group')->index();
            $t->string('label');
            $t->string('type')->default('text');
            $t->text('value')->nullable();
            $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('external_id')->nullable()->unique();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('external_id')->nullable()->unique();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->text('ingredients')->nullable();
            $t->text('usage')->nullable();
            $t->string('image')->nullable();
            $t->json('gallery')->nullable();
            $t->string('badge')->nullable();
            $t->boolean('published')->default(false);
            $t->timestamps();
        });
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('external_id')->nullable()->unique();
            $t->string('sku')->unique();
            $t->string('name');
            $t->unsignedBigInteger('price_minor');
            $t->unsignedInteger('stock')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('favourites', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['user_id', 'product_id']);
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->uuid('number')->unique();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->uuid('checkout_token');
            $t->string('status')->default('new');
            $t->string('payment_status')->default('not_started');
            $t->string('delivery_method');
            $t->string('delivery_status')->default('quote_required');
            $t->unsignedBigInteger('subtotal_minor');
            $t->unsignedBigInteger('shipping_minor')->nullable();
            $t->string('name');
            $t->string('email');
            $t->string('phone');
            $t->text('address');
            $t->text('comment')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'checkout_token']);
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('sku');
            $t->unsignedBigInteger('price_minor');
            $t->unsignedInteger('quantity');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['order_items', 'orders', 'favourites', 'product_variants', 'products', 'categories', 'content_entries', 'pages'] as $table) {
            Schema::dropIfExists($table);
        }Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['is_admin', 'phone']));
    }
};
