<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 96)->unique();
            $table->string('name');
            $table->string('sponsor_type', 64)->default('brand')->index();
            $table->string('status', 32)->default('draft')->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_mobile', 32)->nullable();
            $table->string('website_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['venue_id', 'status']);
        });

        Schema::create('campaign_sponsorships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('sponsor_account_id')->constrained()->cascadeOnDelete();
            $table->string('sponsorship_goal', 64)->index();
            $table->string('package_type', 64)->default('pilot_activation')->index();
            $table->string('status', 32)->default('draft')->index();
            $table->unsignedInteger('budget_amount')->nullable();
            $table->unsignedInteger('contract_value')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'sponsor_account_id']);
            $table->index(['campaign_id', 'status']);
            $table->index(['sponsor_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_sponsorships');
        Schema::dropIfExists('sponsor_accounts');
    }
};
