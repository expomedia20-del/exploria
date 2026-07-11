<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialLedgerEntryRequest;
use App\Services\FinancialLedgerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FinanceWalletController extends Controller
{
    public function page(FinancialLedgerService $ledger): Response
    {
        return Inertia::render('admin/finance-wallets/index', [
            ...$ledger->overview(),
            'canManageFinanceLedger' => in_array(request()->user()?->role, [UserRole::Admin, UserRole::Operator], true),
        ]);
    }

    public function store(StoreFinancialLedgerEntryRequest $request, FinancialLedgerService $ledger): RedirectResponse
    {
        $ledger->recordEntry($request->validated(), $request->user());

        return back()->with('success', 'تراکنش دفترکل مالی ثبت شد.');
    }
}
