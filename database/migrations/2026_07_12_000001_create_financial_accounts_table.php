<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('account_key', 96)->unique();
            $table->string('account_type', 32)->index();
            $table->string('owner_name', 160);
            $table->string('owner_reference_type', 80)->nullable();
            $table->string('owner_reference_id', 80)->nullable();
            $table->string('currency', 8)->default('IRR');
            $table->string('status', 32)->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['account_type', 'status']);
            $table->index(['owner_reference_type', 'owner_reference_id'], 'financial_account_owner_reference_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
