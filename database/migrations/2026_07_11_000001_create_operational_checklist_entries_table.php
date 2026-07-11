<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_checklist_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_key', 96)->unique();
            $table->string('status', 32)->default('needs_action')->index();
            $table->string('owner_name', 160)->nullable();
            $table->text('note')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_checklist_entries');
    }
};
