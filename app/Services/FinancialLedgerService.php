<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\LedgerEntry;
use Illuminate\Database\Eloquent\Builder;

class FinancialLedgerService
{
    /**
     * Build the accounting ledger from the single journal source.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function ledger(array $filters = []): array
    {
        $from = $filters['from_date'] ?? null;
        $to = $filters['to_date'] ?? null;
        $accountId = $filters['account_id'] ?? null;
        $userId = $filters['user_id'] ?? null;
        $doctorId = $filters['doctor_id'] ?? null;
        $entryType = $filters['entry_type'] ?? null;

        $query = LedgerEntry::query()->with(['account', 'voucher.category']);
        $this->applyFilters($query, $filters);

        $entries = $query
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $account = $accountId ? FinancialAccount::find($accountId) : null;
        $opening = 0.0;
        if ($account && $from) {
            $opening = $this->signedBalance(
                (float) LedgerEntry::where('account_id', $account->id)->whereDate('transaction_date', '<', $from)->where('entry_type', 'debit')->sum('amount'),
                (float) LedgerEntry::where('account_id', $account->id)->whereDate('transaction_date', '<', $from)->where('entry_type', 'credit')->sum('amount'),
                $account->normal_balance,
            );
        }

        $running = $opening;
        $rows = $entries->map(function (LedgerEntry $entry) use (&$running, $account) {
            if ($account) {
                $movement = $entry->entry_type === $account->normal_balance ? (float) $entry->amount : -(float) $entry->amount;
                $running = round($running + $movement, 2);
            }

            return [
                'id' => $entry->id,
                'date' => $entry->transaction_date->toDateString(),
                'document_type' => $entry->document_type,
                'module' => $this->moduleName($entry),
                'description' => $entry->narration,
                'person' => $entry->party_name ?: '—',
                'reference' => $entry->reference_no ?: '—',
                'account_code' => $entry->account->code,
                'account_name' => $entry->account->name,
                'debit' => $entry->entry_type === 'debit' ? (float) $entry->amount : 0.0,
                'credit' => $entry->entry_type === 'credit' ? (float) $entry->amount : 0.0,
                'running_balance' => $account ? $running : null,
                'is_reversal' => $entry->is_reversal,
            ];
        })->all();

        $totalDebit = round((float) $entries->where('entry_type', 'debit')->sum('amount'), 2);
        $totalCredit = round((float) $entries->where('entry_type', 'credit')->sum('amount'), 2);

        return [
            'entries' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'net' => round($totalDebit - $totalCredit, 2),
            'opening_balance' => $opening,
            'closing_balance' => $account ? $running : null,
        ];
    }

    /** @return array{rows: array<int, array<string, mixed>>, total_debit: float, total_credit: float} */
    public function trialBalance(?string $toDate = null): array
    {
        $toDate = $toDate ?: now()->toDateString();
        $totals = LedgerEntry::query()
            ->whereDate('transaction_date', '<=', $toDate)
            ->selectRaw("account_id, SUM(CASE WHEN entry_type = 'debit' THEN amount ELSE 0 END) AS debit_total, SUM(CASE WHEN entry_type = 'credit' THEN amount ELSE 0 END) AS credit_total")
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $rows = FinancialAccount::query()
            ->where('is_postable', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function (FinancialAccount $account) use ($totals) {
                $total = $totals->get($account->id);
                $net = round((float) ($total->debit_total ?? 0) - (float) ($total->credit_total ?? 0), 2);

                return [
                    'account' => $account,
                    'debit' => $net > 0 ? $net : 0.0,
                    'credit' => $net < 0 ? abs($net) : 0.0,
                ];
            })
            ->filter(fn (array $row) => $row['debit'] > 0 || $row['credit'] > 0)
            ->values()
            ->all();

        return [
            'rows' => $rows,
            'total_debit' => round((float) collect($rows)->sum('debit'), 2),
            'total_credit' => round((float) collect($rows)->sum('credit'), 2),
        ];
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['from_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['to_date']);
        }
        if (! empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('custodian_user_id', $filters['user_id']);
        }
        if (! empty($filters['doctor_id'])) {
            $query->where('party_type', 'doctor')->where('party_id', $filters['doctor_id']);
        }
        if (! empty($filters['entry_type'])) {
            $query->where('entry_type', $filters['entry_type']);
        }
    }

    private function signedBalance(float $debit, float $credit, ?string $normalBalance): float
    {
        return round($normalBalance === 'credit' ? $credit - $debit : $debit - $credit, 2);
    }

    private function moduleName(LedgerEntry $entry): string
    {
        if ($entry->source_type === 'voucher') {
            return $entry->voucher?->category?->name ?: 'General Voucher';
        }

        return match ($entry->source_type) {
            'opd_appointment' => 'OPD Receipt',
            'ipd_admission_advance' => 'IPD Admission Advance',
            'ipd_advance_installment' => 'IPD Advance Receipt',
            'emergency_receipt' => 'Emergency Receipt',
            'day_care_receipt' => 'Day Care Receipt',
            'laboratory_receipt' => 'Laboratory Receipt',
            'ipd_discharge' => 'IPD Discharge Settlement',
            'doctor_share_opd' => 'OPD Doctor Share Accrual',
            'doctor_share_emergency' => 'Emergency Doctor Share Accrual',
            'doctor_share_day_care' => 'Day Care Doctor Share Accrual',
            'doctor_share_ipd' => 'IPD Doctor Share Accrual',
            default => ucfirst(str_replace('_', ' ', $entry->source_type)),
        };
    }
}
