<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->integer('quantity');
            $table->enum('status', ['active', 'released', 'cancelled'])->default('active');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['status', 'expires_at']); // for expired reservation sweep job
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
