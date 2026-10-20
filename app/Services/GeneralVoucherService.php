<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeneralVoucherService
{
    public function __construct(private readonly LedgerPostingService $ledger)
    {
    }

    public function create(array $data, User $user): Voucher
    {
        return DB::transaction(function () use ($data, $user) {
            $voucher = Voucher::create(array_merge($data, [
                'voucher_no' => null,
                'created_by' => $user->id,
                'posted_by' => ($data['status'] ?? 'posted') === 'posted' ? $user->id : null,
                'posted_at' => ($data['status'] ?? 'posted') === 'posted' ? now() : null,
            ]));

            $voucher->loadMissing('category');
            $prefix = $voucher->accounting_type_code;
            $voucher->update([
                'voucher_no' => $prefix.'-'.$voucher->voucher_date->format('Ymd').'-'.str_pad((string) $voucher->id, 5, '0', STR_PAD_LEFT),
            ]);

            if ($voucher->status === 'posted') {
                $this->postEntries($voucher);
            }

            return $voucher->fresh(['category', 'paymentAccount', 'entries.account']);
        });
    }

    public function receiveCashHandover(Voucher $voucher, float $receivedAmount, User $receiver, ?string $remarks): Voucher
    {
        return DB::transaction(function () use ($voucher, $receivedAmount, $receiver, $remarks) {
            $locked = Voucher::with('category')->lockForUpdate()->findOrFail($voucher->id);

            if (! $locked->isCashHandover() || $locked->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only a pending Contra Voucher can be received.']);
            }

            $metadata = $locked->metadata ?? [];
            $metadata['received_amount'] = round($receivedAmount, 2);
            $metadata['difference'] = round($receivedAmount - (float) $locked->amount, 2);
            $metadata['received_by'] = $receiver->id;
            $metadata['received_at'] = now()->toIso8601String();
            $metadata['receiver_remarks'] = $remarks;

            $locked->update([
                'actual_amount' => $receivedAmount,
                'status' => 'posted',
                'metadata' => $metadata,
                'posted_by' => $receiver->id,
                'posted_at' => now(),
            ]);

            $this->postEntries($locked);

            return $locked->fresh(['category', 'entries.account', 'postedBy']);
        });
    }

    public function rejectCashHandover(Voucher $voucher, User $receiver, ?string $remarks): Voucher
    {
        return DB::transaction(function () use ($voucher, $receiver, $remarks) {
            $locked = Voucher::with('category')->lockForUpdate()->findOrFail($voucher->id);

            if (! $locked->isCashHandover() || $locked->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only a pending Contra Voucher can be rejected.']);
            }

            $metadata = $locked->metadata ?? [];
            $metadata['received_by'] = $receiver->id;
            $metadata['received_at'] = now()->toIso8601String();
            $metadata['receiver_remarks'] = $remarks;

            $locked->update(['status' => 'rejected', 'metadata' => $metadata]);

            return $locked->fresh(['category']);
        });
    }

    public function void(Voucher $voucher, User $user, string $reason): Voucher
    {
        return DB::transaction(function () use ($voucher, $user, $reason) {
            $locked = Voucher::lockForUpdate()->findOrFail($voucher->id);

            if ($locked->status !== 'posted') {
                throw ValidationException::withMessages(['status' => 'Only a posted voucher can be voided.']);
            }

            $locked->update([
                'status' => 'void',
                'voided_by' => $user->id,
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);

            $this->ledger->reverseSource('voucher', $locked->id, $user->id, now());

            return $locked;
        });
    }

    public function paymentAccount(string $method): FinancialAccount
    {
        $mapping = match ($method) {
            'bank' => 'default_bank',
            'cheque' => 'cheque_clearing',
            default => 'hospital_cash',
        };

        return $this->ledger->mappedAccount($mapping);
    }

    private function postEntries(Voucher $voucher): void
    {
        $voucher->loadMissing('category');

        $this->ledger->postVoucher($voucher);
    }
}
