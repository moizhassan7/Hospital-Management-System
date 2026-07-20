<?php

namespace App\Policies;

use App\Models\LimsDoctor;
use App\Models\User;
use App\Support\LabPermissions;

/**
 * Doctor ledger / payouts: Main Lab or Doctor Payout / Commission Admin.
 */
class LimsDoctorPolicy
{
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
}
