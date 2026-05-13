<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Per-variant image: nullable, falls back to product featured image in UI
            $table->foreignId('store_file_id')
                ->nullable()
                ->after('barcode')
                ->constrained('store_files')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\StoreFile::class);
            $table->dropColumn('store_file_id');
        });
    }
};
