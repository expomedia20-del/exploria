<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_events', function (Blueprint $table): void {
            $table->uuid('external_event_id')->nullable()->after('display_device_id');
            $table->unique('external_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('ad_events', function (Blueprint $table): void {
            $table->dropUnique(['external_event_id']);
            $table->dropColumn('external_event_id');
        });
    }
};
