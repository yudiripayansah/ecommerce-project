<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_idempotencies', function (Blueprint $table) {
            $table->string('error_code', 64)->nullable()->after('status');
            $table->text('error_message')->nullable()->after('error_code');
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::table('checkout_idempotencies', function (Blueprint $table) {
            $table->dropIndex(['status', 'updated_at']);
            $table->dropColumn(['error_code', 'error_message']);
        });
    }
};
