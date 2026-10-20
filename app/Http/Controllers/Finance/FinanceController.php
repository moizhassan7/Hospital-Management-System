<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Services\CashCollectionService;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function index(CashCollectionService $cashService)
    {
        $user = Auth::user();
        if (
            ! $user ||
            (
                ! $user->hasPermission('View Finance') &&
                ! $user->hasPermission('Create Vouchers') &&
                ! $user->hasPermission('Create Cash Handover') &&
                ! $user->hasPermission('Manage Doctor Payments') &&
                ! $user->hasPermission('Receive Cash Handover')
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $canHospitalFinance = $user->isSuperAdmin()
            || $user->hasPermission('View Finance')
            || $user->hasPermission('Create Vouchers')
            || $user->hasPermission('Manage Doctor Payments')
            || $user->hasPermission('Receive Cash Handover');
        $selfOnly = $user->hasPermission('Create Cash Handover') && ! $canHospitalFinance;
        $voucherQuery = Voucher::with(['category', 'createdBy'])->orderByDesc('voucher_date')->orderByDesc('id');
        if ($selfOnly) {
            $voucherQuery->where('party_type', 'user')->where('party_id', $user->id)
                ->whereHas('category', fn ($q) => $q->where('code', 'cash-handover'));
        }

        $periodQuery = (clone $voucherQuery)->reorder()->where('status', 'posted')
            ->whereBetween('voucher_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->whereDoesntHave('category', fn ($q) => $q->where('code', 'cash-handover'));
        $creditTotal = (float) (clone $periodQuery)->where('voucher_type', 'credit')
            ->selectRaw('COALESCE(SUM(COALESCE(actual_amount, amount)), 0) AS total')->value('total');
        $debitTotal = (float) (clone $periodQuery)->where('voucher_type', 'debit')
            ->selectRaw('COALESCE(SUM(COALESCE(actual_amount, amount)), 0) AS total')->value('total');
        $pendingHandovers = (clone $voucherQuery)->reorder()->where('status', 'pending')
            ->whereHas('category', fn ($q) => $q->where('code', 'cash-handover'))->count();

        return view('finance.index', [
            'recentVouchers' => $voucherQuery->limit(8)->get(),
            'creditTotal' => $creditTotal,
            'debitTotal' => $debitTotal,
            'pendingHandovers' => $pendingHandovers,
            'selfOnly' => $selfOnly,
            'cashSnapshot' => $user->hasPermission('Create Cash Handover') ? $cashService->snapshot($user->id) : null,
            'canCreateGeneral' => $user->isSuperAdmin() || $user->hasPermission('Create Vouchers') || $user->hasPermission('Manage Doctor Payments'),
            'canManageCategories' => $user->isSuperAdmin() || $user->hasPermission('Manage Voucher Categories'),
        ]);
    }
}
