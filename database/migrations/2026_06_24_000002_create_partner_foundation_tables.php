<?php

use App\Enums\RecordStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('partner_type', 64);
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_mobile')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['venue_id', 'code']);
            $table->index(['venue_id', 'partner_type']);
        });

        Schema::create('partner_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('hub_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('touchpoint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location_role', 64)->default('primary');
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['venue_id', 'hub_id']);
        });

        Schema::create('partner_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 64)->default('manager');
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['partner_account_id', 'user_id']);
        });

        Schema::create('hub_management_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hub_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('assignment_role', 64)->default('hub_manager');
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['hub_id', 'user_id', 'assignment_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_management_assignments');
        Schema::dropIfExists('partner_users');
        Schema::dropIfExists('partner_locations');
        Schema::dropIfExists('partner_accounts');
    }
};
