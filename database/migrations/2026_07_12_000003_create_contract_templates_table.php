<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 96)->unique();
            $table->string('title', 180);
            $table->string('party_type', 48)->index();
            $table->string('pricing_model', 80);
            $table->unsignedBigInteger('base_amount')->default(0);
            $table->unsignedTinyInteger('platform_fee_percent')->nullable();
            $table->text('settlement_terms')->nullable();
            $table->text('scope_summary')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
