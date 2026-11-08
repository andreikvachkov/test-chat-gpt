<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve today's effective price when removing the scheduling feature.
        $scheduled = DB::table('product_variants')->where(fn ($query) => $query->whereNotNull('sale_starts_at')->orWhereNotNull('sale_ends_at'));
        (clone $scheduled)->where(fn ($query) => $query->where('sale_starts_at', '>', now())->orWhere('sale_ends_at', '<', now()))->update(['sale_price_minor' => null]);
        $scheduled->update(['sale_starts_at' => null, 'sale_ends_at' => null]);
    }

    public function down(): void
    {
        // Removed schedules cannot be reconstructed. Columns remain for compatibility.
    }
};
