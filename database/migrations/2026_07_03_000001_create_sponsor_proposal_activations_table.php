<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_proposal_activations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sponsor_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_sponsorship_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->default('ready_for_campaign_design')->index();
            $table->json('reward_definition_ids')->nullable();
            $table->json('partner_assignment_ids')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('sponsor_proposal_id');
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_proposal_activations');
    }
};
