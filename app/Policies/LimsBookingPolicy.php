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
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->isCollectionCenterScope()
            && (int) $user->collection_center_id === (int) $booking->collection_center_id;
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
