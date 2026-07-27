<?php

namespace App\Policies;

use App\Models\LimsBooking;
use App\Models\User;

/**
 * Booking cancel / finalize for commission paths.
 */
class LimsBookingPolicy
{
    public function view(User $user, LimsBooking $booking): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $effectiveId = method_exists($user, 'getEffectiveCollectionCenterId')
            ? $user->getEffectiveCollectionCenterId()
            : null;

        return $effectiveId && (int) $effectiveId === (int) $booking->collection_center_id;
    }

    public function cancel(User $user, LimsBooking $booking): bool
    {
        return $this->view($user, $booking);
    }

    public function finalizeInvoice(User $user, LimsBooking $booking): bool
    {
        return $this->view($user, $booking);
    }
}
