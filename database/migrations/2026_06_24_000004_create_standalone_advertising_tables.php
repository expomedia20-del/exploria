<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('partner_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('hub_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('touchpoint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code', 96)->unique();
            $table->string('title');
            $table->text('body_copy')->nullable();
            $table->string('cta_text', 80)->nullable();
            $table->string('target_url')->nullable();
            $table->string('advertiser_type', 64)->default('member_partner')->index();
            $table->string('ad_type', 64)->default('standalone')->index();
            $table->string('status', 32)->default('pending_review')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('budget_amount')->nullable();
            $table->unsignedInteger('impression_cap')->nullable();
            $table->unsignedInteger('click_cap')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['venue_id', 'status']);
            $table->index(['partner_account_id', 'status']);
        });

        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_request_id')->constrained()->cascadeOnDelete();
            $table->string('creative_type', 64);
            $table->string('asset_url')->nullable();
            $table->string('headline')->nullable();
            $table->text('body_copy')->nullable();
            $table->string('cta_text', 80)->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('display_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('hub_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('touchpoint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 96)->unique();
            $table->string('name');
            $table->string('device_type', 64);
            $table->string('status', 20)->default('active')->index();
            $table->json('supported_media_formats')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_placements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_request_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('display_device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('placement_type', 64);
            $table->string('status', 32)->default('pending_review')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('priority')->default(5);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['placement_type', 'status']);
        });

        Schema::create('ad_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32)->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_request_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('display_device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 64)->index();
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_events');
        Schema::dropIfExists('ad_approvals');
        Schema::dropIfExists('ad_placements');
        Schema::dropIfExists('display_devices');
        Schema::dropIfExists('ad_creatives');
        Schema::dropIfExists('ad_requests');
    }
};
