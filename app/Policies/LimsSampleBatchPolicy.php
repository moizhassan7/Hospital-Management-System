<?php

namespace App\Policies;

use App\Models\LimsSampleBatch;
use App\Models\User;

/**
 * Tenancy: CC users only their center's batches; Main Lab can view/receive globally.
 */
class LimsSampleBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null || $user->isSuperAdmin();
    }

    public function view(User $user, LimsSampleBatch $batch): bool
    {
        if ($user->isSuperAdmin() || (method_exists($user, 'isMainLabScope') && $user->isMainLabScope())) {
            return true;
        }

        $effectiveId = method_exists($user, 'getEffectiveCollectionCenterId')
            ? $user->getEffectiveCollectionCenterId()
            : null;

        if (!$effectiveId) {
            return false;
        }

        return (int) $effectiveId === (int) $batch->collection_center_id
            || (int) $effectiveId === (int) $batch->destination_site_id;
    }

    public function create(User $user): bool
    {
        return $user->isCollectionCenterScope()
            || $user->isMainLabScope()
            || $user->isSuperAdmin();
    }

    public function update(User $user, LimsSampleBatch $batch): bool
    {
        // Mutates open/dispatch for own batches.
        if ($user->isSuperAdmin() || (method_exists($user, 'isMainLabScope') && $user->isMainLabScope())) {
            return true;
        }

        $effectiveId = method_exists($user, 'getEffectiveCollectionCenterId')
            ? $user->getEffectiveCollectionCenterId()
            : null;

        return $effectiveId && (int) $effectiveId === (int) $batch->collection_center_id;
    }

    public function dispatch(User $user, LimsSampleBatch $batch): bool
    {
        return $this->update($user, $batch);
    }

    public function markInTransit(User $user, LimsSampleBatch $batch): bool
    {
        return $this->view($user, $batch);
    }

    public function receive(User $user, LimsSampleBatch $batch): bool
    {
        if ($user->isSuperAdmin() || (method_exists($user, 'isMainLabScope') && $user->isMainLabScope())) {
            return true;
        }

        $effectiveId = method_exists($user, 'getEffectiveCollectionCenterId')
            ? $user->getEffectiveCollectionCenterId()
            : null;

        return $effectiveId && (int) $effectiveId === (int) $batch->destination_site_id;
    }
}
