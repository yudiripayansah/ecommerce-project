<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('midtrans_webhook_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 191)->unique();
            $table->string('order_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_status', 32)->nullable();
            $table->string('payload_hash', 64);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'transaction_status']);
            $table->index(['transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('midtrans_webhook_receipts');
    }
};
