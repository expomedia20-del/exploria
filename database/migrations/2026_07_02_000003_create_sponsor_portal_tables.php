<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sponsor_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 64)->default('manager');
            $table->string('status', 32)->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['sponsor_account_id', 'user_id']);
        });

        Schema::create('sponsor_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sponsor_account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('preferred_partner_account_id')->nullable()->constrained('partner_accounts')->nullOnDelete();
            $table->string('code', 96)->unique();
            $table->string('title');
            $table->string('proposal_type', 64)->index();
            $table->string('objective', 64)->index();
            $table->string('status', 32)->default('pending_review')->index();
            $table->unsignedInteger('proposed_budget_amount')->nullable();
            $table->unsignedInteger('estimated_value_amount')->nullable();
            $table->text('reward_offer')->nullable();
            $table->text('discount_offer')->nullable();
            $table->string('asset_url')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['sponsor_account_id', 'status']);
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_proposals');
        Schema::dropIfExists('sponsor_users');
    }
};
