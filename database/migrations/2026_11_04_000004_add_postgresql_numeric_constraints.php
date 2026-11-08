<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // PostgreSQL has no unsigned integer type. Preserve the shop's numeric invariants in the database.
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement('ALTER TABLE product_variants ADD CONSTRAINT product_variants_nonnegative CHECK (price_minor >= 0 AND stock >= 0)');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_nonnegative CHECK (subtotal_minor >= 0 AND (shipping_minor IS NULL OR shipping_minor >= 0))');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_valid_amounts CHECK (price_minor >= 0 AND quantity > 0)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement('ALTER TABLE order_items DROP CONSTRAINT order_items_valid_amounts');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_nonnegative');
        DB::statement('ALTER TABLE product_variants DROP CONSTRAINT product_variants_nonnegative');
    }
};
