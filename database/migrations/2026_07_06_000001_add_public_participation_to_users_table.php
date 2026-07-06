<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('public_participation_status', 32)
                ->default('registered')
                ->after('role')
                ->index();
            $table->string('public_participation_mode', 32)
                ->default('individual')
                ->after('public_participation_status')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'public_participation_status',
                'public_participation_mode',
            ]);
        });
    }
};
