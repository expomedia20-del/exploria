<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_proposal_items', function (Blueprint $table) {
            $table->json('partner_allocations')->nullable()->after('target_partner_account_ids');
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_proposal_items', function (Blueprint $table) {
            $table->dropColumn('partner_allocations');
        });
    }
};
