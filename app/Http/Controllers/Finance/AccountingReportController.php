<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AccountMapping;
use App\Models\FinancialAccount;
use App\Models\User;
use App\Services\FinancialLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountingReportController extends Controller
{
    public function chartOfAccounts()
    {
        $this->authorizeFinance(Auth::user());

        $user = Auth::user();

        return view('finance.accounts.index', [
            'roots' => FinancialAccount::with('children.children')->whereNull('parent_id')->orderBy('code')->get(),
            'mappings' => AccountMapping::with('account')->orderBy('name')->get(),
            'postableAccounts' => FinancialAccount::where('is_postable', true)->where('is_active', true)->orderBy('code')->get(),
            'canManageMappings' => $user->isSuperAdmin() || $user->hasPermission('Manage Voucher Categories'),
        ]);
    }

    public function updateMapping(Request $request, AccountMapping $mapping)
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperAdmin() && ! $user->hasPermission('Manage Voucher Categories'))) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'account_id' => ['required', Rule::exists('financial_accounts', 'id')->where('is_active', true)->where('is_postable', true)],
        ]);
        $mapping->update($validated);

        return back()->with('success', 'Account mapping updated. New postings will use the selected account.');
    }

    public function trialBalance(Request $request, FinancialLedgerService $ledger)
    {
        $this->authorizeFinance(Auth::user());
        $toDate = $request->input('to_date', now()->toDateString());

        return view('finance.reports.trial-balance', array_merge(
            $ledger->trialBalance($toDate),
            ['toDate' => $toDate],
        ));
    }

    private function authorizeFinance(?User $user): void
    {
        if (! $user || (! $user->isSuperAdmin()
            && ! $user->hasPermission('View Finance')
            && ! $user->hasPermission('Create Vouchers')
            && ! $user->hasPermission('Manage Doctor Payments')
            && ! $user->hasPermission('Receive Cash Handover'))) {
            abort(403, 'Unauthorized action.');
        }
    }
}
