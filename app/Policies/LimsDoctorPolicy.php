<?php

namespace App\Policies;

use App\Models\LimsDoctor;
use App\Models\User;
use App\Support\LabPermissions;

/**
 * Referring doctors + ledger / payouts: Main Lab or Commission Admin / Doctor Payout.
 */
class LimsDoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function view(User $user, LimsDoctor $doctor): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function update(User $user, LimsDoctor $doctor): bool
    {
        return $this->isCommissionAdmin($user);
    }

    public function viewLedger(User $user, LimsDoctor $doctor): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->hasAnyPermission([
            LabPermissions::COMMISSION_ADMIN,
            LabPermissions::DOCTOR_PAYOUT,
        ]);
    }

    public function payout(User $user, LimsDoctor $doctor): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->hasPermission(LabPermissions::DOCTOR_PAYOUT)
            || $user->hasPermission(LabPermissions::COMMISSION_ADMIN);
    }

    public function viewPayouts(User $user, LimsDoctor $doctor): bool
    {
        return $this->viewLedger($user, $doctor);
    }

    private function isCommissionAdmin(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isMainLabScope()) {
            return true;
        }

        return $user->hasPermission(LabPermissions::COMMISSION_ADMIN);
    }
}
