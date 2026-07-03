<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_inventory_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reward_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('treasure_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('sponsor_proposal_activation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('partner_account_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('mission_instance_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('allocated_quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('redeemed_quantity')->default(0);
            $table->string('status', 32)->default('planned')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['reward_definition_id', 'partner_account_id'], 'reward_inventory_partner_unique');
            $table->index(['campaign_id', 'status']);
            $table->index(['partner_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_inventory_allocations');
    }
};
