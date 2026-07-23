<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_parties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('mode', 16);
            $table->string('name')->nullable();
            $table->string('invite_code', 12)->nullable()->unique();
            $table->string('cycle_key', 64);
            $table->string('route_key', 32)->nullable();
            $table->string('status', 32)->default('active')->index();
            $table->unsignedInteger('score')->default(0);
            $table->boolean('collaboration_bonus_awarded')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['owner_user_id', 'campaign_id', 'cycle_key']);
            $table->index(['campaign_id', 'cycle_key', 'status']);
        });

        Schema::create('game_party_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('game_party_id')->constrained('game_parties')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('display_name', 80);
            $table->string('member_type', 16)->default('registered');
            $table->string('role', 16)->default('member');
            $table->string('status', 16)->default('active')->index();
            $table->timestamp('joined_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['game_party_id', 'user_id']);
        });

        Schema::create('game_challenge_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('game_party_id')->constrained('game_parties')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_index');
            $table->string('status', 16)->default('locked')->index();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['game_party_id', 'step_index']);
        });

        Schema::create('game_entry_passes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('game_party_id')->unique()->constrained('game_parties')->cascadeOnDelete();
            $table->foreignId('issued_to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 24)->unique();
            $table->string('token_hash', 64)->unique();
            $table->string('status', 16)->default('active')->index();
            $table->timestamp('expires_at');
            $table->timestamp('redeemed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('game_bonus_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('game_party_id')->constrained('game_parties')->cascadeOnDelete();
            $table->foreignUuid('ad_request_id')->constrained('ad_requests')->cascadeOnDelete();
            $table->foreignId('started_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 16)->default('started')->index();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['game_party_id', 'ad_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_bonus_claims');
        Schema::dropIfExists('game_entry_passes');
        Schema::dropIfExists('game_challenge_progress');
        Schema::dropIfExists('game_party_members');
        Schema::dropIfExists('game_parties');
    }
};
