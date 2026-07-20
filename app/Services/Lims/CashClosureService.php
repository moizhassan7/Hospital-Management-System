<?php

namespace App\Services\Lims;

use App\Models\CollectionCenter;
use App\Models\LimsBooking;
use App\Models\LimsCashClosure;
use App\Models\LimsCommissionSnapshot;
use App\Models\LimsInvoice;
use App\Models\LimsPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Collection-center cash drawer: open → summary → submit → Main Lab approve/lock.
 *
 * Post-lock policy: payments stamp only while a closure is `open`. After lock,
 * further same-day payments leave cash_closure_id null until CC opens the next
 * shift (`day`, `day-2`, …) for the Asia/Karachi business_date.
 */
class CashClosureService
{
    public const TZ = 'Asia/Karachi';

    /**
     * Current open shift for a collection center (if any).
     */
    public function currentOpen(int $collectionCenterId): ?LimsCashClosure
    {
        return LimsCashClosure::withoutGlobalScopes()
            ->where('collection_center_id', $collectionCenterId)
            ->where('status', LimsCashClosure::STATUS_OPEN)
            ->first();
    }

    /**
     * Resolve open closure id for payment stamping (null when drawer closed).
     */
    public function openClosureIdForCenter(int $collectionCenterId): ?int
    {
        return $this->currentOpen($collectionCenterId)?->id;
    }

    /**
     * Open a cash shift. Idempotent when the same idempotency_key is reused.
     * If an open shift already exists for the CC, returns it (idempotent open).
     *
     * @param  array{opening_float?: float|int|string, shift_label?: string, idempotency_key?: string|null}  $options
     */
    public function openShift(User $actor, array $options = []): LimsCashClosure
    {
        $ccId = $this->resolveCollectionCenterId($actor, $options['collection_center_id'] ?? null);
        $organizationId = $this->resolveOrganizationId($actor, $ccId);
        $idempotencyKey = $options['idempotency_key'] ?? null;

        return DB::transaction(function () use ($actor, $ccId, $organizationId, $options, $idempotencyKey) {
            if ($idempotencyKey) {
                $byKey = LimsCashClosure::withoutGlobalScopes()
                    ->where('collection_center_id', $ccId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();
                if ($byKey !== null) {
                    return $byKey;
                }
            }

            $existingOpen = LimsCashClosure::withoutGlobalScopes()
                ->where('collection_center_id', $ccId)
                ->where('status', LimsCashClosure::STATUS_OPEN)
                ->lockForUpdate()
                ->first();

            if ($existingOpen !== null) {
                return $existingOpen;
            }

            $businessDate = $this->businessDate();
            $shiftLabel = isset($options['shift_label']) && trim((string) $options['shift_label']) !== ''
                ? trim((string) $options['shift_label'])
                : $this->nextShiftLabel($ccId, $businessDate);

            return LimsCashClosure::withoutGlobalScopes()->create([
                'organization_id' => $organizationId,
                'collection_center_id' => $ccId,
                'business_date' => $businessDate,
                'shift_label' => $shiftLabel,
                'status' => LimsCashClosure::STATUS_OPEN,
                'opening_float' => round((float) ($options['opening_float'] ?? 0), 2),
                'opened_by' => $actor->id,
                'idempotency_key' => $idempotencyKey,
                'summary_json' => [],
            ]);
        });
    }

    /**
     * Live pre-close summary (does not persist unless $persist is true).
     *
     * @return array<string, mixed>
     */
    public function buildSummary(LimsCashClosure $closure, bool $persist = false): array
    {
        $closure = LimsCashClosure::withoutGlobalScopes()->findOrFail($closure->id);

        $payments = LimsPayment::withoutGlobalScopes()
            ->where('cash_closure_id', $closure->id)
            ->whereNull('deleted_at')
            ->get();

        $cash = 0.0;
        $card = 0.0;
        $other = 0.0;
        $byMethod = [];

        foreach ($payments as $payment) {
            $amount = round((float) $payment->amount, 2);
            $method = (string) $payment->method;
            $byMethod[$method] = round(($byMethod[$method] ?? 0) + $amount, 2);

            if ($method === LimsPayment::METHOD_CASH) {
                $cash = round($cash + $amount, 2);
            } elseif ($method === LimsPayment::METHOD_CARD) {
                $card = round($card + $amount, 2);
            } else {
                $other = round($other + $amount, 2);
            }
        }

        $bookingIds = LimsInvoice::withoutGlobalScopes()
            ->whereIn('id', $payments->pluck('invoice_id')->unique()->filter()->all())
            ->pluck('booking_id')
            ->unique()
            ->values();

        // Also count bookings created at this CC on the business date (even unpaid).
        $dayStart = Carbon::parse($closure->business_date->format('Y-m-d'), self::TZ)->startOfDay();
        $dayEnd = (clone $dayStart)->endOfDay();

        $bookingsOnDate = LimsBooking::withoutGlobalScopes()
            ->where('collection_center_id', $closure->collection_center_id)
            ->whereBetween('booked_at', [$dayStart->utc(), $dayEnd->utc()])
            ->whereNull('deleted_at')
            ->pluck('id');

        $allBookingIds = $bookingIds->merge($bookingsOnDate)->unique()->values();

        $dueTotal = round((float) LimsInvoice::withoutGlobalScopes()
            ->whereIn('booking_id', $allBookingIds->all())
            ->whereNull('deleted_at')
            ->sum('due_total'), 2);

        // Informational: commission credits snapshotted for this CC on business_date.
        $commissionCredits = round((float) LimsCommissionSnapshot::withoutGlobalScopes()
            ->where('collection_center_id', $closure->collection_center_id)
            ->whereBetween('snapshotted_at', [$dayStart->utc(), $dayEnd->utc()])
            ->sum('commission_amount'), 2);

        $summary = [
            'closure_id' => $closure->id,
            'business_date' => $closure->business_date->format('Y-m-d'),
            'shift_label' => $closure->shift_label,
            'status' => $closure->status,
            'opening_float' => round((float) $closure->opening_float, 2),
            'system_cash_total' => $cash,
            'system_card_total' => $card,
            'system_other_total' => $other,
            'payment_count' => $payments->count(),
            'booking_count' => $allBookingIds->count(),
            'due_total' => $dueTotal,
            'by_method' => $byMethod,
            'commission_credits_today' => $commissionCredits,
            'allow_close_with_due' => true,
        ];

        if ($persist && $closure->isOpen()) {
            $closure->fill([
                'system_cash_total' => $cash,
                'system_card_total' => $card,
                'system_other_total' => $other,
                'due_total' => $dueTotal,
                'booking_count' => $allBookingIds->count(),
                'payment_count' => $payments->count(),
                'summary_json' => $summary,
            ]);
            $closure->save();
        }

        return $summary;
    }

    /**
     * Submit counted cash → variance; status open → submitted.
     * Idempotent: re-submit of already-submitted returns the same row.
     */
    public function submit(
        LimsCashClosure $closure,
        float $countedCashTotal,
        ?User $actor = null,
        ?string $idempotencyKey = null,
    ): LimsCashClosure {
        return DB::transaction(function () use ($closure, $countedCashTotal, $actor, $idempotencyKey) {
            $closure = LimsCashClosure::withoutGlobalScopes()
                ->where('id', $closure->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($closure->status === LimsCashClosure::STATUS_SUBMITTED
                || $closure->status === LimsCashClosure::STATUS_LOCKED
                || $closure->status === LimsCashClosure::STATUS_APPROVED) {
                return $closure;
            }

            if ($closure->status !== LimsCashClosure::STATUS_OPEN) {
                throw new InvalidArgumentException(
                    "Cannot submit closure in status {$closure->status}."
                );
            }

            if ($idempotencyKey && $closure->idempotency_key === null) {
                $closure->idempotency_key = $idempotencyKey;
            }

            $summary = $this->buildSummary($closure, persist: false);
            $systemCash = (float) $summary['system_cash_total'];
            $counted = round($countedCashTotal, 2);
            $variance = round($counted - $systemCash, 2);

            $closure->fill([
                'system_cash_total' => $systemCash,
                'system_card_total' => $summary['system_card_total'],
                'system_other_total' => $summary['system_other_total'],
                'due_total' => $summary['due_total'],
                'booking_count' => $summary['booking_count'],
                'payment_count' => $summary['payment_count'],
                'summary_json' => array_merge($summary, [
                    'counted_cash_total' => $counted,
                    'variance_cash' => $variance,
                ]),
                'counted_cash_total' => $counted,
                'variance_cash' => $variance,
                'status' => LimsCashClosure::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'submitted_by' => $actor?->id,
            ]);
            $closure->save();

            return $closure->fresh();
        });
    }

    /**
     * Main Lab approve → locked. Idempotent if already locked.
     */
    public function approve(LimsCashClosure $closure, ?User $actor = null): LimsCashClosure
    {
        return DB::transaction(function () use ($closure, $actor) {
            $closure = LimsCashClosure::withoutGlobalScopes()
                ->where('id', $closure->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($closure->status === LimsCashClosure::STATUS_LOCKED
                || $closure->status === LimsCashClosure::STATUS_APPROVED) {
                if ($closure->status === LimsCashClosure::STATUS_APPROVED) {
                    $closure->status = LimsCashClosure::STATUS_LOCKED;
                    $closure->save();
                }

                return $closure->fresh();
            }

            if ($closure->status !== LimsCashClosure::STATUS_SUBMITTED) {
                throw new InvalidArgumentException(
                    "Cannot approve closure in status {$closure->status}; must be submitted."
                );
            }

            $closure->fill([
                'status' => LimsCashClosure::STATUS_LOCKED,
                'approved_at' => now(),
                'approved_by' => $actor?->id,
            ]);
            $closure->save();

            return $closure->fresh();
        });
    }

    /**
     * Main Lab list for a business date (optional CC filter).
     *
     * @return \Illuminate\Support\Collection<int, LimsCashClosure>
     */
    public function listByDate(string $businessDate, ?int $collectionCenterId = null)
    {
        $query = LimsCashClosure::withoutGlobalScopes()
            ->whereDate('business_date', $businessDate)
            ->orderBy('collection_center_id')
            ->orderBy('shift_label');

        if ($collectionCenterId !== null) {
            $query->where('collection_center_id', $collectionCenterId);
        }

        return $query->get();
    }

    public function businessDate(?Carbon $at = null): string
    {
        return ($at ?? now())->copy()->timezone(self::TZ)->toDateString();
    }

    private function nextShiftLabel(int $collectionCenterId, string $businessDate): string
    {
        $count = LimsCashClosure::withoutGlobalScopes()
            ->where('collection_center_id', $collectionCenterId)
            ->whereDate('business_date', $businessDate)
            ->count();

        return $count === 0 ? 'day' : 'day-'.($count + 1);
    }

    private function resolveCollectionCenterId(User $actor, mixed $override): int
    {
        if ($actor->isCollectionCenterScope()) {
            if (! $actor->collection_center_id) {
                throw new RuntimeException('CC user has no collection_center_id.');
            }

            return (int) $actor->collection_center_id;
        }

        if ($override !== null) {
            return (int) $override;
        }

        throw new InvalidArgumentException(
            'collection_center_id is required when opening a shift as Main Lab.'
        );
    }

    private function resolveOrganizationId(User $actor, int $collectionCenterId): int
    {
        if ($actor->organization_id) {
            return (int) $actor->organization_id;
        }

        $cc = CollectionCenter::query()->findOrFail($collectionCenterId);

        return (int) $cc->organization_id;
    }
}
