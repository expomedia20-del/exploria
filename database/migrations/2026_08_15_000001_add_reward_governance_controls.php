<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateUserRewards = DB::table('user_rewards')
            ->select('user_id', 'reward_definition_id', 'campaign_id')
            ->groupBy('user_id', 'reward_definition_id', 'campaign_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateUserRewards) {
            throw new RuntimeException('Duplicate user rewards must be resolved before reward governance constraints are applied.');
        }

        $duplicateRedemptions = DB::table('reward_redemptions')
            ->select('user_reward_id')
            ->groupBy('user_reward_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateRedemptions) {
            throw new RuntimeException('Duplicate reward redemptions must be resolved before reward governance constraints are applied.');
        }

        Schema::table('reward_definitions', function (Blueprint $table) {
            $table->foreignUuid('cost_owner_financial_account_id')
                ->nullable()
                ->after('partner_account_id')
                ->constrained('financial_accounts')
                ->restrictOnDelete();
            $table->string('inventory_mode', 32)->nullable()->after('reward_type')->index();
            $table->timestamp('available_from')->nullable()->after('stock_quantity');
            $table->timestamp('available_until')->nullable()->after('available_from');
            $table->unsignedInteger('expires_after_minutes')->nullable()->after('available_until');
            $table->unsignedInteger('per_user_award_limit')->nullable()->after('expires_after_minutes');
            $table->index(['status', 'available_from', 'available_until'], 'reward_governance_window_index');
        });

        Schema::table('user_rewards', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'reward_definition_id', 'campaign_id'],
                'user_reward_user_definition_campaign_unique',
            );
        });

        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->unique('user_reward_id', 'reward_redemption_user_reward_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->dropUnique('reward_redemption_user_reward_unique');
        });

        Schema::table('user_rewards', function (Blueprint $table) {
            $table->dropUnique('user_reward_user_definition_campaign_unique');
        });

        Schema::table('reward_definitions', function (Blueprint $table) {
            $table->dropIndex('reward_governance_window_index');
            $table->dropIndex(['inventory_mode']);
            $table->dropConstrainedForeignId('cost_owner_financial_account_id');
            $table->dropColumn([
                'inventory_mode',
                'available_from',
                'available_until',
                'expires_after_minutes',
                'per_user_award_limit',
            ]);
        });
    }
};
