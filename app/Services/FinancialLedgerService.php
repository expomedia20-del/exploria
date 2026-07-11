<?php

namespace App\Services;

use App\Models\ContractTemplate;
use App\Models\FinancialAccount;
use App\Models\FinancialLedgerEntry;
use App\Models\User;
use Illuminate\Support\Collection;

class FinancialLedgerService
{
    /** @return array<string, mixed> */
    public function overview(): array
    {
        $this->ensureDefaultSetup();

        $accounts = FinancialAccount::query()
            ->with('ledgerEntries')
            ->orderBy('account_type')
            ->orderBy('owner_name')
            ->get();
        $entries = FinancialLedgerEntry::query()
            ->with('financialAccount:id,account_key,account_type,owner_name', 'createdBy:id,name')
            ->latest()
            ->limit(40)
            ->get();
        $templates = ContractTemplate::query()
            ->orderBy('party_type')
            ->orderBy('base_amount')
            ->get();

        return [
            'summary' => $this->summary($accounts),
            'accounts' => $accounts->map(fn (FinancialAccount $account): array => $this->accountPayload($account))->values()->all(),
            'ledgerEntries' => $entries->map(fn (FinancialLedgerEntry $entry): array => $this->entryPayload($entry))->values()->all(),
            'contractTemplates' => $templates->map(fn (ContractTemplate $template): array => $this->contractTemplatePayload($template))->values()->all(),
            'formOptions' => [
                'accounts' => $accounts->map(fn (FinancialAccount $account): array => [
                    'id' => $account->id,
                    'label' => $account->owner_name.' / '.$this->accountTypeLabel($account->account_type),
                    'type' => $account->account_type,
                ])->values()->all(),
                'entryTypes' => $this->entryTypeOptions(),
                'contractTypes' => $templates->map(fn (ContractTemplate $template): array => [
                    'code' => $template->code,
                    'label' => $template->title,
                ])->values()->all(),
            ],
            'boundaries' => [
                'این دفترکل داخلی است و پرداخت بانکی واقعی، ذخیره کارت یا تسویه حقوقی پیچیده انجام نمی‌دهد.',
                'کیف پول کاربر در MVP پول نقد نیست؛ امتیاز، گنج، کوپن و پاداش قابل مصرف را نمایش می‌دهد.',
                'تسویه نهایی با فروشگاه، مکان یا اسپانسر باید با تایید مالی خارج از سیستم یا اتصال حسابداری بعدی انجام شود.',
            ],
        ];
    }

    /** @param array<string, mixed> $data */
    public function recordEntry(array $data, ?User $user): FinancialLedgerEntry
    {
        $account = FinancialAccount::query()->findOrFail($data['financial_account_id']);

        return FinancialLedgerEntry::query()->create([
            'financial_account_id' => $account->id,
            'entry_type' => $data['entry_type'],
            'direction' => $data['direction'],
            'amount' => (int) $data['amount'],
            'currency' => $account->currency,
            'status' => $data['status'] ?? 'posted',
            'contract_type' => $data['contract_type'] ?? null,
            'source_type' => 'manual_finance_operation',
            'source_id' => null,
            'description' => $data['description'] ?? null,
            'occurred_on' => $data['occurred_on'] ?? now()->toDateString(),
            'created_by' => $user?->id,
            'metadata' => [
                'mvp_boundary' => 'internal_ledger_only',
                'entered_from' => 'admin_finance_wallets',
            ],
        ]);
    }

    private function ensureDefaultSetup(): void
    {
        foreach ($this->defaultAccounts() as $account) {
            FinancialAccount::query()->firstOrCreate(
                ['account_key' => $account['account_key']],
                $account,
            );
        }

        foreach ($this->defaultContractTemplates() as $template) {
            ContractTemplate::query()->updateOrCreate(
                ['code' => $template['code']],
                $template,
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function defaultAccounts(): array
    {
        return [
            [
                'account_key' => 'exploria-platform-main',
                'account_type' => 'platform',
                'owner_name' => 'اکسپلوریا',
                'currency' => 'IRR',
                'status' => 'active',
                'metadata' => ['wallet_role' => 'platform_fee_and_operations'],
            ],
            [
                'account_key' => 'venue-ecopark-abbasabad',
                'account_type' => 'venue',
                'owner_name' => 'اکوپارک عباس آباد',
                'owner_reference_type' => 'venue_code',
                'owner_reference_id' => 'ecopark-abbasabad',
                'currency' => 'IRR',
                'status' => 'active',
                'metadata' => ['wallet_role' => 'pilot_revenue_share'],
            ],
            [
                'account_key' => 'sponsor-family-route',
                'account_type' => 'sponsor',
                'owner_name' => 'اسپانسر مسیر خانواده',
                'owner_reference_type' => 'sponsor_code',
                'owner_reference_id' => 'family-route-sponsor',
                'currency' => 'IRR',
                'status' => 'active',
                'metadata' => ['wallet_role' => 'sponsor_budget_and_rewards'],
            ],
            [
                'account_key' => 'partner-cafe-eco',
                'account_type' => 'partner',
                'owner_name' => 'کافه اکو',
                'owner_reference_type' => 'partner_code',
                'owner_reference_id' => 'cafe-eco',
                'currency' => 'IRR',
                'status' => 'active',
                'metadata' => ['wallet_role' => 'reward_redemption_settlement'],
            ],
            [
                'account_key' => 'participant-demo-wallet',
                'account_type' => 'participant',
                'owner_name' => 'کیف امتیاز و پاداش کاربر دمو',
                'currency' => 'IRR',
                'status' => 'active',
                'metadata' => ['wallet_role' => 'points_rewards_non_cash'],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function defaultContractTemplates(): array
    {
        return [
            [
                'code' => 'venue-pilot-contract',
                'title' => 'قرارداد پایلوت مکان',
                'party_type' => 'venue',
                'pricing_model' => 'setup_fee_plus_monthly',
                'base_amount' => 180000000,
                'platform_fee_percent' => 20,
                'settlement_terms' => 'پرداخت مرحله اول پیش از اجرا؛ تسویه درآمدهای کمپین پس از گزارش ROI.',
                'scope_summary' => 'راه اندازی کمپین، QR، ماموریت، پاداش، گزارش روز اجرا و گزارش فروش.',
                'status' => 'ready',
                'metadata' => ['recommended_for' => 'pilot_sales'],
            ],
            [
                'code' => 'sponsor-campaign-package',
                'title' => 'پکیج اسپانسر کمپین',
                'party_type' => 'sponsor',
                'pricing_model' => 'campaign_budget_plus_reward_pool',
                'base_amount' => 250000000,
                'platform_fee_percent' => 25,
                'settlement_terms' => 'بودجه اسپانسر در دفترکل تعهد می‌شود و مصرف پاداش/نمایش تبلیغ جدا گزارش می‌شود.',
                'scope_summary' => 'حضور برند در ماموریت، گنج، جایزه، نمایشگر و گزارش تعامل.',
                'status' => 'ready',
                'metadata' => ['recommended_for' => 'brand_activation'],
            ],
            [
                'code' => 'partner-reward-contract',
                'title' => 'قرارداد فروشگاه/شریک پاداش',
                'party_type' => 'partner',
                'pricing_model' => 'redemption_commission',
                'base_amount' => 0,
                'platform_fee_percent' => 12,
                'settlement_terms' => 'مصرف پاداش تاییدشده مبنای طلب/بدهی شریک است؛ تسویه نقدی در MVP خارج از سیستم انجام می‌شود.',
                'scope_summary' => 'پیشنهاد فروشگاه، سهم موجودی، مصرف کد، گزارش مراجعه و مشوق خرید بعدی.',
                'status' => 'ready',
                'metadata' => ['recommended_for' => 'merchant_growth'],
            ],
            [
                'code' => 'display-ad-package',
                'title' => 'پکیج تبلیغات و نمایشگر',
                'party_type' => 'advertiser',
                'pricing_model' => 'slot_or_campaign_display',
                'base_amount' => 90000000,
                'platform_fee_percent' => 30,
                'settlement_terms' => 'نمایش تاییدشده و گزارش evidence مبنای صورت حساب تبلیغات است.',
                'scope_summary' => 'زمان بندی نمایش، محتوای اسپانسر، evidence پخش و گزارش اثر.',
                'status' => 'draft',
                'metadata' => ['recommended_for' => 'media_revenue'],
            ],
        ];
    }

    /** @param Collection<int, FinancialAccount> $accounts @return array<string, mixed> */
    private function summary(Collection $accounts): array
    {
        $entries = FinancialLedgerEntry::query()->where('status', 'posted')->get();
        $credit = (int) $entries->where('direction', 'credit')->sum('amount');
        $debit = (int) $entries->where('direction', 'debit')->sum('amount');
        $committed = (int) $entries->where('entry_type', 'commitment')->sum('amount');

        return [
            'accountsCount' => $accounts->count(),
            'creditTotal' => $credit,
            'debitTotal' => $debit,
            'committedTotal' => $committed,
            'netBalance' => $credit - $debit,
            'contractsReady' => ContractTemplate::query()->where('status', 'ready')->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function accountPayload(FinancialAccount $account): array
    {
        $posted = $account->ledgerEntries->where('status', 'posted');
        $credit = (int) $posted->where('direction', 'credit')->sum('amount');
        $debit = (int) $posted->where('direction', 'debit')->sum('amount');

        return [
            'id' => $account->id,
            'key' => $account->account_key,
            'type' => $account->account_type,
            'typeLabel' => $this->accountTypeLabel($account->account_type),
            'ownerName' => $account->owner_name,
            'currency' => $account->currency,
            'status' => $account->status,
            'creditTotal' => $credit,
            'debitTotal' => $debit,
            'balance' => $credit - $debit,
            'entriesCount' => $posted->count(),
            'walletRole' => $account->metadata['wallet_role'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    private function entryPayload(FinancialLedgerEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'accountName' => $entry->financialAccount->owner_name,
            'accountType' => $entry->financialAccount->account_type,
            'entryType' => $entry->entry_type,
            'entryTypeLabel' => $this->entryTypeLabel($entry->entry_type),
            'direction' => $entry->direction,
            'amount' => $entry->amount,
            'currency' => $entry->currency,
            'status' => $entry->status,
            'contractType' => $entry->contract_type,
            'description' => $entry->description,
            'occurredOn' => $entry->occurred_on?->toDateString(),
            'createdBy' => $entry->createdBy?->name,
        ];
    }

    /** @return array<string, mixed> */
    private function contractTemplatePayload(ContractTemplate $template): array
    {
        return [
            'id' => $template->id,
            'code' => $template->code,
            'title' => $template->title,
            'partyType' => $template->party_type,
            'partyTypeLabel' => $this->accountTypeLabel($template->party_type),
            'pricingModel' => $template->pricing_model,
            'baseAmount' => $template->base_amount,
            'platformFeePercent' => $template->platform_fee_percent,
            'settlementTerms' => $template->settlement_terms,
            'scopeSummary' => $template->scope_summary,
            'status' => $template->status,
        ];
    }

    /** @return array<int, array<string, string>> */
    private function entryTypeOptions(): array
    {
        return collect([
            'commitment',
            'sponsor_budget',
            'reward_redemption',
            'platform_fee',
            'ad_display_revenue',
            'venue_share',
            'partner_settlement',
            'adjustment',
        ])->map(fn (string $type): array => [
            'value' => $type,
            'label' => $this->entryTypeLabel($type),
        ])->values()->all();
    }

    private function accountTypeLabel(string $type): string
    {
        return [
            'platform' => 'پلتفرم',
            'venue' => 'مکان',
            'sponsor' => 'اسپانسر',
            'partner' => 'فروشگاه/شریک',
            'participant' => 'کاربر',
            'advertiser' => 'تبلیغ‌دهنده',
        ][$type] ?? $type;
    }

    private function entryTypeLabel(string $type): string
    {
        return [
            'commitment' => 'تعهد قراردادی',
            'sponsor_budget' => 'بودجه اسپانسر',
            'reward_redemption' => 'مصرف پاداش',
            'platform_fee' => 'کارمزد پلتفرم',
            'ad_display_revenue' => 'درآمد تبلیغات/نمایشگر',
            'venue_share' => 'سهم مکان',
            'partner_settlement' => 'تسویه شریک',
            'adjustment' => 'اصلاح دستی',
        ][$type] ?? $type;
    }
}
