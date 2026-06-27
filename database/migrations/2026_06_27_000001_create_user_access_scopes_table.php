<?php

use App\Enums\RecordStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_access_scopes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_key', 64)->index();
            $table->string('scope_type', 64)->index();
            $table->string('scope_id', 64)->nullable()->index();
            $table->string('status', 20)->default(RecordStatus::Active->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'role_key', 'scope_type', 'scope_id'], 'user_access_scopes_unique');
            $table->index(['scope_type', 'scope_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_access_scopes');
    }
};
