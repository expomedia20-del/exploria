<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('qr_code_id')->constrained('qr_codes')->restrictOnDelete();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('touchpoint_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_hash', 64)->nullable();
            $table->string('result', 32)->index();
            $table->boolean('risk_flag')->default(false);
            $table->string('risk_reason', 128)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->json('payload_json')->nullable();
            $table->timestamp('scanned_at')->index();

            $table->index(['qr_code_id', 'scanned_at']);
            $table->index(['venue_id', 'scanned_at']);
            $table->index(['user_id', 'qr_code_id', 'scanned_at'], 'scan_events_user_qr_time_index');
        });

        Schema::create('event_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type', 128);
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_hash', 64)->nullable();
            $table->foreignUuid('venue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('touchpoint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('object_type', 64)->nullable();
            $table->string('object_id', 128)->nullable();
            $table->json('payload_json')->nullable();
            $table->timestamp('occurred_at');

            $table->index(['event_type', 'occurred_at']);
            $table->index(['venue_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_log');
        Schema::dropIfExists('scan_events');
    }
};
