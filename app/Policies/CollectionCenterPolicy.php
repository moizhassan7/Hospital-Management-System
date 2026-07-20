<?php

namespace App\Policies;

use App\Models\CollectionCenter;
use App\Models\User;

/**
 * Stub tenancy policy for collection centers (CC vs Main Lab).
 * Expand with real abilities in later phases.
 */
class CollectionCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isMainLabScope() || $user->isSuperAdmin();
    }

    public function view(User $user, CollectionCenter $collectionCenter): bool
    {
        if ($user->isMainLabScope() || $user->isSuperAdmin()) {
            return true;
        }

        return $user->isCollectionCenterScope()
            && (int) $user->collection_center_id === (int) $collectionCenter->id;
    }

    public function create(User $user): bool
    {
        return $user->isMainLabScope() || $user->isSuperAdmin();
    }

    public function update(User $user, CollectionCenter $collectionCenter): bool
    {
        return $user->isMainLabScope() || $user->isSuperAdmin();
    }

    public function delete(User $user, CollectionCenter $collectionCenter): bool
    {
        return $user->isMainLabScope() || $user->isSuperAdmin();
    }
}
