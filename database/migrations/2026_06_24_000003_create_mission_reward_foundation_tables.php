<?php

use App\Enums\RecordStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 64)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('mission_type', 64);
            $table->string('trigger_type', 64)->default('manual');
            $table->unsignedInteger('point_value')->default(0);
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('mission_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mission_template_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('hub_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('touchpoint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 96);
            $table->string('title_override')->nullable();
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('unlock_rule')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'code']);
            $table->index(['venue_id', 'hub_id', 'touchpoint_id']);
        });

        Schema::create('treasures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('mission_instance_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 96);
            $table->string('name');
            $table->string('treasure_type', 64);
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('reveal_rule')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'code']);
        });

        Schema::create('reward_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('partner_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 96);
            $table->string('name');
            $table->string('reward_type', 64);
            $table->unsignedInteger('point_cost')->nullable();
            $table->unsignedInteger('stock_quantity')->nullable();
            $table->string('status', 20)->default(RecordStatus::Draft->value)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'code']);
        });

        Schema::create('user_mission_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('mission_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->default('started')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'mission_instance_id']);
        });

        Schema::create('user_rewards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('reward_definition_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('campaign_id')->constrained()->restrictOnDelete();
            $table->string('status', 32)->default('awarded')->index();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_reward_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('partner_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('redemption_code', 64)->unique();
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('redeemed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('user_rewards');
        Schema::dropIfExists('user_mission_progress');
        Schema::dropIfExists('reward_definitions');
        Schema::dropIfExists('treasures');
        Schema::dropIfExists('mission_instances');
        Schema::dropIfExists('mission_templates');
    }
};
