<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('financial_account_id')->constrained()->cascadeOnDelete();
            $table->string('entry_type', 48)->index();
            $table->string('direction', 16)->index();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 8)->default('IRR');
            $table->string('status', 32)->default('posted')->index();
            $table->string('contract_type', 64)->nullable()->index();
            $table->string('source_type', 80)->nullable();
            $table->string('source_id', 80)->nullable();
            $table->text('description')->nullable();
            $table->date('occurred_on')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['financial_account_id', 'status']);
            $table->index(['source_type', 'source_id'], 'financial_ledger_source_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_ledger_entries');
    }
};
