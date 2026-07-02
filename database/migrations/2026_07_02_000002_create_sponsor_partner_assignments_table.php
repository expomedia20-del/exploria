<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_partner_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sponsor_account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activation_role', 64)->default('sales_point')->index();
            $table->string('status', 32)->default('draft')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['sponsor_account_id', 'partner_account_id', 'campaign_id', 'activation_role']);
            $table->index(['partner_account_id', 'status']);
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_partner_assignments');
    }
};
