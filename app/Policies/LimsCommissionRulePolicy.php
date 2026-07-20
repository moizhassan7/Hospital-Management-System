<?php

namespace App\Policies;

use App\Models\LimsCommissionRule;
use App\Models\User;
use App\Support\LabPermissions;

/**
 * Commission rules: Main Lab / Commission Admin / Super Admin.
 */
class LimsCommissionRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function view(User $user, LimsCommissionRule $rule): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function update(User $user, LimsCommissionRule $rule): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function deactivate(User $user, LimsCommissionRule $rule): bool
    {
        return $this->isCommissionAdmin($user);
    }

    private function isCommissionAdmin(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->hasPermission(LabPermissions::COMMISSION_ADMIN);
    }
}
