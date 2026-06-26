<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('consent_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->char('session_hash', 64);
            $table->string('source', 30)->default('pwa');
            $table->uuid('venue_id')->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();
            $table->unique(['consent_version_id', 'user_id']);
            $table->index(['user_id', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
