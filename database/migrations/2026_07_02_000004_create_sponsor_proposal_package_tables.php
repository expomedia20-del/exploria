<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_proposal_partner_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sponsor_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['sponsor_proposal_id', 'partner_account_id']);
        });

        Schema::create('sponsor_proposal_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sponsor_proposal_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 64)->index();
            $table->string('title');
            $table->unsignedInteger('quantity')->nullable();
            $table->unsignedInteger('estimated_unit_value_amount')->nullable();
            $table->json('target_partner_account_ids')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['sponsor_proposal_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_proposal_items');
        Schema::dropIfExists('sponsor_proposal_partner_accounts');
    }
};
