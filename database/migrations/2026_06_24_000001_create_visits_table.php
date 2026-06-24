<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('qr_code_id')->constrained('qr_codes')->restrictOnDelete();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('touchpoint_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('consent_log_id')->nullable()->constrained('consent_logs')->nullOnDelete();
            $table->string('source', 64)->default('qr_landing');
            $table->string('status', 32)->default('confirmed')->index();
            $table->string('session_hash', 64)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'qr_code_id']);
            $table->index(['venue_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
