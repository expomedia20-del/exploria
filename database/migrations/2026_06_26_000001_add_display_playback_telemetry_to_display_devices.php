<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('display_devices', function (Blueprint $table): void {
            $table->timestamp('last_heartbeat_at')->nullable()->after('supported_media_formats')->index();
            $table->string('playback_status', 32)->nullable()->after('last_heartbeat_at')->index();
            $table->string('current_slot', 128)->nullable()->after('playback_status');
            $table->string('last_playback_result', 32)->nullable()->after('current_slot');
            $table->text('last_playback_error')->nullable()->after('last_playback_result');
        });
    }

    public function down(): void
    {
        Schema::table('display_devices', function (Blueprint $table): void {
            $table->dropColumn([
                'last_heartbeat_at',
                'playback_status',
                'current_slot',
                'last_playback_result',
                'last_playback_error',
            ]);
        });
    }
};
