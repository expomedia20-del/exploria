<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_proposal_activations', function (Blueprint $table) {
            $table->json('treasure_ids')->nullable()->after('reward_definition_ids');
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_proposal_activations', function (Blueprint $table) {
            $table->dropColumn('treasure_ids');
        });
    }
};
