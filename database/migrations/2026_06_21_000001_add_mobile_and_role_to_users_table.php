<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('mobile')->nullable()->after('name');
            $table->char('mobile_hash', 64)->nullable()->unique()->after('mobile');
            $table->string('role', 20)->default(UserRole::Visitor->value)->index()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['mobile_hash']);
            $table->dropIndex(['role']);
            $table->dropColumn(['mobile', 'mobile_hash', 'role']);
        });
    }
};
