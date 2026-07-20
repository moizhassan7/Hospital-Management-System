<?php

namespace App\Policies;

use App\Models\LimsCashClosure;
use App\Models\User;
use App\Support\LabPermissions;

/**
 * Cash closures: CC own center open/submit; Main Lab global list/approve.
 */
class LimsCashClosurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null
            || $user->isSuperAdmin()
            || $user->isMainLabScope()
            || $user->isCollectionCenterScope();
    }

    public function view(User $user, LimsCashClosure $closure): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->isCollectionCenterScope()
            && (int) $user->collection_center_id === (int) $closure->collection_center_id;
    }

    public function open(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        if (! $user->isCollectionCenterScope()) {
            return false;
        }

        return $user->hasPermission(LabPermissions::CASH_CLOSE)
            || $user->hasPermission(LabPermissions::FINANCIAL_SUMMARY)
            || $user->hasPermission(LabPermissions::BOOKING);
    }

    public function submit(User $user, LimsCashClosure $closure): bool
    {
        if (! $this->view($user, $closure)) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->hasPermission(LabPermissions::CASH_CLOSE)
            || $user->hasPermission(LabPermissions::FINANCIAL_SUMMARY)
            || $user->hasPermission(LabPermissions::BOOKING);
    }

    public function approve(User $user, LimsCashClosure $closure): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->hasPermission(LabPermissions::CASH_APPROVE);
    }
}
