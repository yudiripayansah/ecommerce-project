<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_job_audits', function (Blueprint $table) {
            $table->id();
            $table->string('job_class');
            $table->string('queue', 64)->nullable();
            $table->string('tenant_id')->nullable();
            $table->string('status', 32); // started|succeeded|failed
            $table->unsignedInteger('attempt')->default(1);
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['job_class', 'status', 'created_at']);
            $table->index(['queue', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_job_audits');
    }
};
