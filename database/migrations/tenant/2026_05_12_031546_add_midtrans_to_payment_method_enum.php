<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN or ENUM — the TEXT column already accepts any value
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cod','bank_transfer','midtrans') NOT NULL DEFAULT 'cod'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod'");
    }
};
