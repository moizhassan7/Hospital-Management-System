<?php

namespace App\Policies;

use App\Models\LimsCommissionSnapshot;
use App\Models\User;
use App\Support\LabPermissions;

/**
 * Snapshots: Main Lab / Commission Admin global; CC = originating center only.
 */
class LimsCommissionSnapshotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null || $user->isSuperAdmin();
    }

    public function view(User $user, LimsCommissionSnapshot $snapshot): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        if ($user->hasPermission(LabPermissions::COMMISSION_ADMIN)) {
            return true;
        }

        return $user->isCollectionCenterScope()
            && (int) $user->collection_center_id === (int) $snapshot->collection_center_id;
    }
}
