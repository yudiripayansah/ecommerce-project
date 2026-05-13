<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();

            // Signed: negative = stock out, positive = stock in
            $table->integer('quantity');

            $table->enum('type', [
                'sale',                 // COD confirmed / payment released
                'return',               // Customer return
                'adjustment',           // Manual admin adjustment
                'reserve',              // Pending payment: quantity blocked
                'reserve_released',     // Payment confirmed: reservation lifted
                'reserve_cancelled',    // Payment cancelled/expired: reservation freed
            ]);

            // Polymorphic reference (Order, StockReservation, etc.)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('note')->nullable();

            // Immutable: only created_at, no updated_at
            $table->timestamp('created_at');

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
