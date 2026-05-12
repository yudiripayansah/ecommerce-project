<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->foreignId('store_file_id')
                ->nullable()
                ->after('image')
                ->constrained('store_files')
                ->nullOnDelete();

            $table->string('meta_title', 70)->nullable()->after('store_file_id');
            $table->text('meta_description')->nullable()->after('meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\StoreFile::class);
            $table->dropColumn(['store_file_id', 'meta_title', 'meta_description']);
        });
    }
};
