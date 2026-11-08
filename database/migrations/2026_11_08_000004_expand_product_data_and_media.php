<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->json('details')->default('{}');
            $table->integer('inventory_stock')->default(0);
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('details')->default('{}');
            $table->bigInteger('sale_price_minor')->nullable();
            $table->timestampTz('sale_starts_at')->nullable();
            $table->timestampTz('sale_ends_at')->nullable();
        });
        DB::statement('ALTER TABLE product_variants DROP CONSTRAINT product_variants_nonnegative');
        DB::statement("ALTER TABLE product_variants ADD CONSTRAINT product_variants_nonnegative CHECK (price_minor >= 0 AND (stock >= 0 OR COALESCE(details->>'backorders' IN ('yes', 'notify'), false))), ADD CONSTRAINT product_variants_sale_valid CHECK (sale_price_minor IS NULL OR (sale_price_minor >= 0 AND sale_price_minor < price_minor))");
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_inventory_valid CHECK (inventory_stock >= 0 OR COALESCE(details->>'backorders' IN ('yes', 'notify'), false))");
        Schema::table('media_assets', function (Blueprint $table) {
            $table->text('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->text('description')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->text('purchase_note')->nullable();
            $table->boolean('backordered')->default(false);
        });
        foreach (glob(public_path('assets/images/*')) as $file) {
            if (! is_file($file) || ! in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'mp4', 'webm'])) {
                continue;
            }
            $image = @getimagesize($file);
            if (DB::table('media_assets')->where('path', '/assets/images/'.basename($file))->exists()) {
                DB::table('media_assets')->where('path', '/assets/images/'.basename($file))->update(['mime_type' => mime_content_type($file), 'size' => filesize($file), 'width' => $image[0] ?? null, 'height' => $image[1] ?? null]);

                continue;
            }
            DB::table('media_assets')->insertOrIgnore(['path' => '/assets/images/'.basename($file), 'name' => basename($file), 'mime_type' => mime_content_type($file), 'size' => filesize($file), 'width' => $image[0] ?? null, 'height' => $image[1] ?? null, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // A rollback must not silently discard backorders or turn them into positive stock.
        if (DB::table('product_variants')->where('stock', '<', 0)->exists()) {
            throw new RuntimeException('Resolve negative stock/backorders before rolling back product data.');
        }
        DB::statement('ALTER TABLE products DROP CONSTRAINT products_inventory_valid');
        DB::statement('ALTER TABLE product_variants DROP CONSTRAINT product_variants_nonnegative, DROP CONSTRAINT product_variants_sale_valid');
        DB::statement('ALTER TABLE product_variants ADD CONSTRAINT product_variants_nonnegative CHECK (price_minor >= 0 AND stock >= 0)');
        Schema::table('product_variants', fn (Blueprint $table) => $table->dropColumn(['details', 'sale_price_minor', 'sale_starts_at', 'sale_ends_at']));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['details', 'inventory_stock']));
        Schema::table('media_assets', fn (Blueprint $table) => $table->dropColumn(['alt_text', 'caption', 'description', 'mime_type', 'size', 'width', 'height']));
        if (Schema::hasColumn('order_items', 'purchase_note')) {
            Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn(['purchase_note', 'backordered']));
        }
        Schema::dropIfExists('shipping_classes');
    }
};
