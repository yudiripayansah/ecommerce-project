<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Seed one inventory_item row per tracked variant
        DB::table('product_variants')
            ->where('track_stock', true)
            ->orderBy('id')
            ->each(function (object $variant) {
                DB::table('inventory_items')->insertOrIgnore([
                    'variant_id'        => $variant->id,
                    'product_id'        => null,
                    'quantity_reserved' => 0,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            });

        // Seed one inventory_item row per tracked product (product-level stock, no variants)
        DB::table('products')
            ->where('track_stock', true)
            ->orderBy('id')
            ->each(function (object $product) {
                DB::table('inventory_items')->insertOrIgnore([
                    'variant_id'        => null,
                    'product_id'        => $product->id,
                    'quantity_reserved' => 0,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('inventory_items')->delete();
    }
};
