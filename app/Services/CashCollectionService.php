<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Voucher;

class CashCollectionService
{
    public function __construct(private readonly LedgerPostingService $ledger)
    {
    }

    /** @return array{modules: array<string, float>, total: float} */
    public function collectionsForUser(int $userId, ?string $from = null, ?string $to = null): array
    {
        $sourceLabels = [
            'opd_appointment' => 'OPD',
            'ipd_admission_advance' => 'IPD Advance',
            'ipd_advance_installment' => 'IPD Advance',
            'emergency_receipt' => 'Emergency',
            'day_care_receipt' => 'Day Care',
            'laboratory_receipt' => 'Laboratory',
            'ipd_discharge' => 'IPD Discharge',
        ];

        $query = LedgerEntry::query()
            ->where('account_id', $this->ledger->mappedAccount('staff_cash')->id)
            ->where('custodian_user_id', $userId)
            ->where('entry_type', 'debit')
            ->whereIn('source_type', array_keys($sourceLabels));
        if ($from) {
            $query->whereDate('transaction_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('transaction_date', '<=', $to);
        }

        $modules = array_fill_keys(array_values(array_unique($sourceLabels)), 0.0);
        foreach ($query->selectRaw('source_type, SUM(amount) AS total')->groupBy('source_type')->get() as $row) {
            $label = $sourceLabels[$row->source_type];
            $modules[$label] = round($modules[$label] + (float) $row->total, 2);
        }

        return ['modules' => $modules, 'total' => round(array_sum($modules), 2)];
    }

    /** Pending and posted Contra Vouchers reserve custody cash against another handover. */
    public function handedOver(int $userId): float
    {
        return (float) Voucher::query()
            ->where('party_type', 'user')
            ->where('party_id', $userId)
            ->whereIn('status', ['pending', 'posted'])
            ->whereHas('category', fn ($q) => $q->where('code', 'cash-handover'))
            ->selectRaw('COALESCE(SUM(COALESCE(actual_amount, amount)), 0) AS total')
            ->value('total');
    }

    public function cashInHand(int $userId): float
    {
        $staffCash = $this->ledger->mappedAccount('staff_cash')->id;
        $debit = (float) LedgerEntry::where('account_id', $staffCash)->where('custodian_user_id', $userId)->where('entry_type', 'debit')->sum('amount');
        $credit = (float) LedgerEntry::where('account_id', $staffCash)->where('custodian_user_id', $userId)->where('entry_type', 'credit')->sum('amount');
        $pending = (float) Voucher::query()
            ->where('party_type', 'user')->where('party_id', $userId)->where('status', 'pending')
            ->whereHas('category', fn ($q) => $q->where('code', 'cash-handover'))->sum('amount');

        return round(max(0, $debit - $credit - $pending), 2);
    }

    /** @return array{modules: array<string, float>, total_collected: float, previously_handed_over: float, cash_in_hand: float} */
    public function snapshot(int $userId): array
    {
        $collections = $this->collectionsForUser($userId);

        return [
            'modules' => $collections['modules'],
            'total_collected' => $collections['total'],
            'previously_handed_over' => $this->handedOver($userId),
            'cash_in_hand' => $this->cashInHand($userId),
        ];
    }
}
