<?php

use App\Enums\RecordStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('hub_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('partner_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('participant_type', 64);
            $table->string('participation_role', 64);
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->string('onboarding_status', 40)->default('invited')->index();
            $table->timestamp('joined_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'partner_account_id']);
            $table->index(['campaign_id', 'hub_id']);
            $table->index(['venue_id', 'participant_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_participants');
    }
};