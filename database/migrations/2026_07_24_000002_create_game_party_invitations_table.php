<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_party_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('game_party_id')->constrained('game_parties')->cascadeOnDelete();
            $table->foreignId('inviter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('mobile_hash', 64);
            $table->string('status', 16)->default('pending')->index();
            $table->timestamp('invited_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['game_party_id', 'mobile_hash']);
            $table->index(['invitee_user_id', 'status']);
            $table->index(['mobile_hash', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_party_invitations');
    }
};
