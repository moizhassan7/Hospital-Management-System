<?php

namespace App\Services\Lims;

use App\Models\LimsDoctor;
use App\Models\LimsDoctorPayout;
use App\Models\LimsLedgerEntry;
use App\Models\LimsPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Doctor payout = DEBIT ledger only. Never recalculates commissions.
 */
class DoctorPayoutService
{
    public function __construct(
        private readonly CommissionSnapshotService $snapshots,
    ) {}

    public function payout(
        LimsDoctor $doctor,
        float $amount,
        ?User $actor = null,
        string $method = LimsPayment::METHOD_CASH,
        ?string $notes = null,
        ?string $idempotencyKey = null,
        ?string $paidAt = null,
    ): LimsDoctorPayout {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payout amount must be > 0.');
        }

        $allowed = [
            LimsPayment::METHOD_CASH,
            LimsPayment::METHOD_CARD,
            LimsPayment::METHOD_BANK,
            LimsPayment::METHOD_ONLINE,
            LimsPayment::METHOD_ADJUSTMENT,
        ];
        if (! in_array($method, $allowed, true)) {
            throw new InvalidArgumentException('Invalid payout method.');
        }

        $idempotencyKey = $idempotencyKey ?: ('payout:'.$doctor->id.':'.uniqid('', true));

        return DB::transaction(function () use (
            $doctor, $amount, $actor, $method, $notes, $idempotencyKey, $paidAt
        ) {
            $existing = LimsDoctorPayout::query()
                ->where('organization_id', $doctor->organization_id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $ledger = $this->snapshots->lockLedger((int) $doctor->id, (int) $doctor->organization_id);
            // Allow negative balance after clawback-post-payout (edge case 6).
            $newBalance = round((float) $ledger->balance - $amount, 2);

            $payout = LimsDoctorPayout::query()->create([
                'organization_id' => $doctor->organization_id,
                'doctor_id' => $doctor->id,
                'amount' => $amount,
                'paid_at' => $paidAt ? Carbon::parse($paidAt) : now(),
                'paid_by' => $actor?->id,
                'method' => $method,
                'notes' => $notes,
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
            ]);

            LimsLedgerEntry::query()->create([
                'organization_id' => $doctor->organization_id,
                'doctor_id' => $doctor->id,
                'collection_center_id' => null,
                'entry_type' => LimsLedgerEntry::TYPE_DEBIT,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'ref_type' => LimsLedgerEntry::REF_PAYOUT,
                'ref_id' => $payout->id,
                'commission_snapshot_id' => null,
                'payout_id' => $payout->id,
                'narration' => $notes ?: 'Doctor payout',
                'occurred_at' => $payout->paid_at,
                'created_by' => $actor?->id,
                'idempotency_key' => 'debit:payout:'.$payout->id,
                'created_at' => now(),
            ]);

            $ledger->balance = $newBalance;
            $ledger->updated_at = now();
            $ledger->save();

            return $payout;
        });
    }
}
