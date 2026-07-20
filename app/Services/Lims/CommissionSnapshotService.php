<?php

namespace App\Services\Lims;

use App\Models\LimsBooking;
use App\Models\LimsBookingItem;
use App\Models\LimsCommissionRule;
use App\Models\LimsCommissionSnapshot;
use App\Models\LimsDoctorLedger;
use App\Models\LimsLedgerEntry;
use App\Models\LimsTestCategory;
use App\Models\Test;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Snapshot commissions + CREDIT ledger in the same transaction as booking confirm.
 * Clawback uses frozen snapshot amounts only — never re-resolves rules.
 */
class CommissionSnapshotService
{
    public function __construct(
        private readonly CommissionRuleResolver $resolver,
    ) {}

    /**
     * Ensure snapshots + credits exist for a booked (non-self-referred) booking.
     * Idempotent: existing snapshots are never recalculated.
     *
     * @return list<LimsCommissionSnapshot>
     */
    public function snapshotBooking(LimsBooking $booking, ?User $actor = null): array
    {
        if ($booking->self_referred || $booking->doctor_id === null) {
            return [];
        }

        if ($booking->status === LimsBooking::STATUS_CANCELLED) {
            return [];
        }

        return DB::transaction(function () use ($booking, $actor) {
            $booking = LimsBooking::withoutGlobalScopes()
                ->with(['items', 'invoice'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ($booking->self_referred || $booking->doctor_id === null) {
                return [];
            }

            $asOf = $booking->booked_at ?? now();
            $created = [];

            foreach ($booking->items as $item) {
                if ($item->trashed()) {
                    continue;
                }

                $existing = LimsCommissionSnapshot::withoutGlobalScopes()
                    ->where('booking_item_id', $item->id)
                    ->first();

                if ($existing !== null) {
                    $created[] = $existing;
                    continue;
                }

                $created[] = $this->snapshotItem($booking, $item, $asOf, $actor);
            }

            return $created;
        });
    }

    private function snapshotItem(
        LimsBooking $booking,
        LimsBookingItem $item,
        mixed $asOf,
        ?User $actor,
    ): LimsCommissionSnapshot {
        $categoryId = $item->test_category_id
            ?? $this->resolveCategoryId($booking->organization_id, $item->test_id);

        if ($categoryId !== null && $item->test_category_id === null) {
            $item->test_category_id = $categoryId;
            $item->save();
        }

        $rule = $this->resolver->resolve(
            (int) $booking->organization_id,
            (int) $booking->doctor_id,
            (int) $item->test_id,
            $categoryId !== null ? (int) $categoryId : null,
            (int) $booking->collection_center_id,
            $asOf,
        );

        $baseAmount = (float) $item->net_price;
        $commissionAmount = 0.0;
        $ruleBasis = LimsCommissionRule::BASIS_FIXED;
        $ruleAmount = null;
        $rulePercent = null;
        $ruleId = null;

        if ($rule !== null) {
            $ruleId = $rule->id;
            $ruleBasis = $rule->basis;
            $ruleAmount = $rule->basis === LimsCommissionRule::BASIS_FIXED
                ? (float) $rule->amount
                : null;
            $rulePercent = $rule->basis === LimsCommissionRule::BASIS_PERCENT
                ? (float) $rule->percent
                : null;
            $commissionAmount = $this->resolver->calculateAmount($rule, $baseAmount);
        }

        $snapshot = LimsCommissionSnapshot::withoutGlobalScopes()->create([
            'organization_id' => $booking->organization_id,
            'collection_center_id' => $booking->collection_center_id,
            'booking_id' => $booking->id,
            'booking_item_id' => $item->id,
            'invoice_id' => $booking->invoice?->id,
            'doctor_id' => $booking->doctor_id,
            'commission_rule_id' => $ruleId,
            'rule_basis' => $ruleBasis,
            'rule_amount' => $ruleAmount,
            'rule_percent' => $rulePercent,
            'base_amount' => $baseAmount,
            'commission_amount' => $commissionAmount,
            'currency' => 'PKR',
            'snapshotted_at' => now(),
            'snapshotted_by' => $actor?->id,
            'is_clawed_back' => false,
            'clawed_back_at' => null,
        ]);

        if ($commissionAmount > 0) {
            $this->postCredit($snapshot, $booking, $actor);
        }

        return $snapshot;
    }

    private function postCredit(
        LimsCommissionSnapshot $snapshot,
        LimsBooking $booking,
        ?User $actor,
    ): void {
        $idempotencyKey = 'credit:snapshot:'.$snapshot->booking_item_id;

        $existing = LimsLedgerEntry::query()
            ->where('doctor_id', $snapshot->doctor_id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing !== null) {
            return;
        }

        $ledger = $this->lockLedger((int) $snapshot->doctor_id, (int) $snapshot->organization_id);
        $amount = (float) $snapshot->commission_amount;
        $newBalance = round((float) $ledger->balance + $amount, 2);

        LimsLedgerEntry::query()->create([
            'organization_id' => $snapshot->organization_id,
            'doctor_id' => $snapshot->doctor_id,
            'collection_center_id' => $snapshot->collection_center_id,
            'entry_type' => LimsLedgerEntry::TYPE_CREDIT,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'ref_type' => LimsLedgerEntry::REF_BOOKING,
            'ref_id' => $booking->id,
            'commission_snapshot_id' => $snapshot->id,
            'payout_id' => null,
            'narration' => 'Commission credit for booking '.$booking->lab_number,
            'occurred_at' => now(),
            'created_by' => $actor?->id,
            'idempotency_key' => $idempotencyKey,
            'created_at' => now(),
        ]);

        $ledger->balance = $newBalance;
        $ledger->updated_at = now();
        $ledger->save();
    }

    /**
     * Claw back all non-clawed snapshots for a booking (cancel path).
     * Uses frozen commission_amount; never re-resolves rules.
     *
     * @return list<LimsCommissionSnapshot>
     */
    public function clawbackBooking(LimsBooking $booking, ?User $actor = null): array
    {
        return DB::transaction(function () use ($booking, $actor) {
            $snapshots = LimsCommissionSnapshot::withoutGlobalScopes()
                ->where('booking_id', $booking->id)
                ->where('is_clawed_back', false)
                ->lockForUpdate()
                ->get();

            $clawed = [];

            foreach ($snapshots as $snapshot) {
                $clawed[] = $this->clawbackSnapshot($snapshot, $booking, $actor);
            }

            return $clawed;
        });
    }

    private function clawbackSnapshot(
        LimsCommissionSnapshot $snapshot,
        LimsBooking $booking,
        ?User $actor,
    ): LimsCommissionSnapshot {
        if ($snapshot->is_clawed_back) {
            return $snapshot;
        }

        $snapshot->is_clawed_back = true;
        $snapshot->clawed_back_at = now();
        $snapshot->save();

        $amount = (float) $snapshot->commission_amount;
        if ($amount <= 0) {
            return $snapshot;
        }

        $idempotencyKey = 'clawback:snapshot:'.$snapshot->id;

        $existing = LimsLedgerEntry::query()
            ->where('doctor_id', $snapshot->doctor_id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing !== null) {
            return $snapshot;
        }

        $ledger = $this->lockLedger((int) $snapshot->doctor_id, (int) $snapshot->organization_id);
        $newBalance = round((float) $ledger->balance - $amount, 2);

        LimsLedgerEntry::query()->create([
            'organization_id' => $snapshot->organization_id,
            'doctor_id' => $snapshot->doctor_id,
            'collection_center_id' => $snapshot->collection_center_id,
            'entry_type' => LimsLedgerEntry::TYPE_CLAWBACK,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'ref_type' => LimsLedgerEntry::REF_CANCELLATION,
            'ref_id' => $booking->id,
            'commission_snapshot_id' => $snapshot->id,
            'payout_id' => null,
            'narration' => 'Clawback for cancelled booking '.$booking->lab_number,
            'occurred_at' => now(),
            'created_by' => $actor?->id,
            'idempotency_key' => $idempotencyKey,
            'created_at' => now(),
        ]);

        $ledger->balance = $newBalance;
        $ledger->updated_at = now();
        $ledger->save();

        return $snapshot->fresh();
    }

    public function lockLedger(int $doctorId, int $organizationId): LimsDoctorLedger
    {
        $ledger = LimsDoctorLedger::query()
            ->where('doctor_id', $doctorId)
            ->lockForUpdate()
            ->first();

        if ($ledger !== null) {
            return $ledger;
        }

        LimsDoctorLedger::query()->create([
            'doctor_id' => $doctorId,
            'organization_id' => $organizationId,
            'balance' => 0,
            'updated_at' => now(),
        ]);

        return LimsDoctorLedger::query()
            ->where('doctor_id', $doctorId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function resolveCategoryId(int $organizationId, int $testId): ?int
    {
        $test = Test::query()->find($testId);
        if ($test === null) {
            return null;
        }

        if ($test->test_category_id) {
            return (int) $test->test_category_id;
        }

        $code = trim((string) ($test->category ?? ''));
        if ($code === '') {
            return null;
        }

        $category = LimsTestCategory::query()->firstOrCreate(
            [
                'organization_id' => $organizationId,
                'code' => mb_strtoupper($code),
            ],
            [
                'name' => $code,
            ]
        );

        if (! $test->test_category_id) {
            $test->test_category_id = $category->id;
            $test->save();
        }

        return (int) $category->id;
    }

    /**
     * Cancel booking + clawback snapshots in one TX.
     */
    public function cancelBooking(
        LimsBooking $booking,
        ?User $actor = null,
        ?string $reason = null,
    ): LimsBooking {
        return DB::transaction(function () use ($booking, $actor, $reason) {
            $booking = LimsBooking::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ($booking->status === LimsBooking::STATUS_CANCELLED) {
                $this->clawbackBooking($booking, $actor);

                return $booking->fresh(['items', 'commissionSnapshots']);
            }

            $booking->status = LimsBooking::STATUS_CANCELLED;
            $booking->cancelled_at = now();
            $booking->cancelled_by = $actor?->id;
            $booking->cancel_reason = $reason;
            $booking->save();

            $this->clawbackBooking($booking, $actor);

            return $booking->fresh(['items', 'commissionSnapshots']);
        });
    }
}
