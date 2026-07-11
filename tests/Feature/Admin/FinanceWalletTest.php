<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\FinancialAccount;
use App\Models\FinancialLedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinanceWalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_open_finance_wallet_page_in_read_only_mode(): void
    {
        $this->withoutVite();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.finance-wallets.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/finance-wallets/index')
                ->where('canManageFinanceLedger', false)
                ->where('summary.accountsCount', 5)
                ->has('contractTemplates', 4));
    }

    public function test_operator_can_record_financial_ledger_entry(): void
    {
        $this->withoutVite();
        $operator = User::factory()->create(['role' => UserRole::Operator]);

        $this->actingAs($operator)
            ->get(route('admin.finance-wallets.page'))
            ->assertOk();

        $account = FinancialAccount::query()->where('account_key', 'sponsor-family-route')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.finance-wallets.ledger.store'), [
                'financial_account_id' => $account->id,
                'entry_type' => 'sponsor_budget',
                'direction' => 'credit',
                'amount' => 25000000,
                'contract_type' => 'sponsor-campaign-package',
                'description' => 'بودجه آزمایشی اسپانسر برای مسیر خانواده',
                'occurred_on' => '2026-07-12',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_ledger_entries', [
            'financial_account_id' => $account->id,
            'entry_type' => 'sponsor_budget',
            'direction' => 'credit',
            'amount' => 25000000,
            'created_by' => $operator->id,
        ]);

        $entry = FinancialLedgerEntry::query()->firstOrFail();

        $this->assertSame('2026-07-12', $entry->occurred_on?->toDateString());
        $this->assertSame('IRR', $entry->currency);
    }

    public function test_viewer_cannot_record_financial_ledger_entry(): void
    {
        $this->withoutVite();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.finance-wallets.page'))
            ->assertOk();

        $account = FinancialAccount::query()->firstOrFail();

        $this->actingAs($viewer)
            ->post(route('admin.finance-wallets.ledger.store'), [
                'financial_account_id' => $account->id,
                'entry_type' => 'commitment',
                'direction' => 'credit',
                'amount' => 1000,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('financial_ledger_entries', 0);
    }
}
