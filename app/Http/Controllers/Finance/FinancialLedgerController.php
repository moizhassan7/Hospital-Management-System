<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\FinancialAccount;
use App\Models\User;
use App\Services\FinancialLedgerService;
use App\Services\LedgerPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialLedgerController extends Controller
{
    public function index(Request $request, FinancialLedgerService $ledgerService, LedgerPostingService $posting)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        $canViewHospital = $user->isSuperAdmin()
            || $user->hasPermission('View Finance')
            || $user->hasPermission('Create Vouchers')
            || $user->hasPermission('Manage Doctor Payments')
            || $user->hasPermission('Receive Cash Handover');
        $canViewOwn = $user->hasPermission('Create Cash Handover');
        if (! $canViewHospital && ! $canViewOwn) {
            abort(403, 'Unauthorized action.');
        }

        $selfOnly = $canViewOwn && ! $canViewHospital;
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $entryType = $request->input('entry_type');
        $accountId = $request->input('account_id');

        if ($selfOnly) {
            $userId = $user->id;
            $doctorId = null;
            $accountId = $posting->mappedAccount('staff_cash')->id;
        } else {
            $userId = $request->input('user_id');
            $doctorId = $request->input('doctor_id');
        }

        $result = $ledgerService->ledger([
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'account_id' => $accountId,
            'user_id' => $userId,
            'doctor_id' => $doctorId,
            'entry_type' => $entryType ?: null,
        ]);

        $accounts = FinancialAccount::where('is_postable', true)->where('is_active', true)->orderBy('code')->get();
        $users = $selfOnly ? collect([$user]) : User::orderBy('name')->get(['id', 'name', 'username']);
        $doctors = $selfOnly ? collect() : Doctor::query()
            ->where(fn ($q) => $q->whereNull('employee_group')->orWhereNotIn('employee_group', ['Procedure', 'Service']))
            ->orderBy('name')->get(['id', 'name', 'code']);
        $selectedAccount = $accounts->firstWhere('id', (int) $accountId);

        $scopeLabel = $selectedAccount ? $selectedAccount->code.' · '.$selectedAccount->name : 'All posting accounts';
        if ($selfOnly) {
            $scopeLabel = 'My Staff Cash Custody';
        } elseif ($userId) {
            $scopeLabel .= ' · '.($users->firstWhere('id', (int) $userId)->name ?? 'Staff');
        } elseif ($doctorId) {
            $scopeLabel .= ' · '.($doctors->firstWhere('id', (int) $doctorId)->name ?? 'Doctor');
        }

        return view('finance.ledger.index', array_merge($result, [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'accountId' => $accountId,
            'userId' => $userId,
            'doctorId' => $doctorId,
            'entryType' => $entryType,
            'accounts' => $accounts,
            'users' => $users,
            'doctors' => $doctors,
            'selectedAccount' => $selectedAccount,
            'scopeLabel' => $scopeLabel,
            'selfOnly' => $selfOnly,
        ]));
    }
}
