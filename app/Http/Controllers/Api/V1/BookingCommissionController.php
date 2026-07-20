<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LimsBooking;
use App\Services\Lims\CommissionSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Commission-related booking actions (cancel clawback, ensure snapshots).
 * Primary booking create remains dual-write via BookingController + LimsBookingSync.
 */
class BookingCommissionController extends Controller
{
    public function __construct(
        private readonly CommissionSnapshotService $commissions,
    ) {}

    /**
     * Safety net: ensure snapshots exist (e.g. after invoice finalize).
     * Never recalculates existing commission_amount.
     */
    public function finalizeInvoice(Request $request, LimsBooking $booking): JsonResponse
    {
        $booking = LimsBooking::withoutGlobalScopes()->findOrFail($booking->id);
        $this->authorize('finalizeInvoice', $booking);

        $snapshots = $this->commissions->snapshotBooking($booking, $request->user());

        return response()->json([
            'data' => [
                'booking' => $booking->fresh(['invoice', 'commissionSnapshots']),
                'snapshots' => $snapshots,
            ],
        ]);
    }

    public function cancel(Request $request, LimsBooking $booking): JsonResponse
    {
        $booking = LimsBooking::withoutGlobalScopes()->findOrFail($booking->id);
        $this->authorize('cancel', $booking);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $cancelled = $this->commissions->cancelBooking(
            $booking,
            $request->user(),
            $data['reason'] ?? null,
        );

        return response()->json([
            'data' => $cancelled->load(['commissionSnapshots', 'invoice']),
        ]);
    }
}
