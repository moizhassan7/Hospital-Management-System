<?php

namespace App\Policies;

use App\Models\LimsPatient;
use App\Models\User;

/**
 * Stub tenancy policy for the global LIMS patient registry.
 * SELECT is org-wide; create/update allowed from any scoped user in the org.
 */
class LimsPatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null
            || $user->isSuperAdmin();
    }

    public function view(User $user, LimsPatient $patient): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->organization_id !== null
            && (int) $user->organization_id === (int) $patient->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->organization_id !== null
            || $user->isSuperAdmin();
    }

    public function update(User $user, LimsPatient $patient): bool
    {
        return $this->view($user, $patient);
    }

    public function delete(User $user, LimsPatient $patient): bool
    {
        return $user->isMainLabScope() || $user->isSuperAdmin();
    }
}
