<?php

use App\Enums\RecordStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->string('profile_status', 20)->default(RecordStatus::Draft->value);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['venue_id', 'code']);
        });

        Schema::create('hubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('zone_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('hub_type', 64);
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['zone_id', 'code']);
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('campaign_type', 64)->default('pilot_visit');
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['venue_id', 'code']);
        });

        Schema::create('touchpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hub_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('label');
            $table->string('type', 64);
            $table->string('owner_type', 64)->default('venue');
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->text('install_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['hub_id', 'code']);
        });

        Schema::create('qr_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 128)->unique();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('touchpoint_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->restrictOnDelete();
            $table->text('destination_url');
            $table->string('label')->nullable();
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->unsignedInteger('max_scans_per_user_per_window')->default(1);
            $table->unsignedInteger('duplicate_window_seconds')->default(300);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('touchpoints');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('hubs');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('venues');
    }
};
