<?php

namespace App\Policies;

use App\Models\CollectionCenter;
use App\Models\User;
use App\Support\LabPermissions;

/**
 * Collection centers: Main Lab / Manage Collection Centers / Super Admin.
 */
class CollectionCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isCenterAdmin($user);
    }

    public function view(User $user, CollectionCenter $collectionCenter): bool
    {
        if ($this->isCenterAdmin($user)) {
            return true;
        }

        return $user->isCollectionCenterScope()
            && (int) $user->collection_center_id === (int) $collectionCenter->id;
    }

    public function create(User $user): bool
    {
        return $this->isCenterAdmin($user);
    }

    public function update(User $user, CollectionCenter $collectionCenter): bool
    {
        return $this->isCenterAdmin($user);
    }

    public function delete(User $user, CollectionCenter $collectionCenter): bool
    {
        return $this->isCenterAdmin($user);
    }

    private function isCenterAdmin(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission(LabPermissions::COLLECTION_CENTERS);
    }
}
