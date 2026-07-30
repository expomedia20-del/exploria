<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('audience_type', 64)->index();
            $table->string('organization_name')->nullable();
            $table->string('contact_name');
            $table->string('mobile', 32);
            $table->string('city', 120)->nullable();
            $table->string('project_hint')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('new')->index();
            $table->string('source_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['audience_type', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_leads');
    }
};
