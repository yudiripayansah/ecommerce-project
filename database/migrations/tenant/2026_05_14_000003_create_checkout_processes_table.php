<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_processes', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key', 128)->unique();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('state', 32)->default('initiated'); // initiated|order_created|stock_reserved|payment_initiated|completed|failed
            $table->json('context')->nullable();
            $table->string('last_error_code', 64)->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamp('last_transition_at')->nullable();
            $table->timestamps();

            $table->index(['state', 'updated_at']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_processes');
    }
};
