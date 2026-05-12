<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop legacy varchar image column on collections — superseded by store_file_id FK
        if (Schema::hasColumn('collections', 'image')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }

        // Drop product_images table — never used in practice; all media managed via
        // product_store_file pivot + StoreFile model
        Schema::dropIfExists('product_images');
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('src');
            $table->string('alt')->nullable();
            $table->integer('position')->default(1);
            $table->timestamps();
        });
    }
};
