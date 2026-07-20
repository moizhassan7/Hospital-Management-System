<?php

namespace App\Support;

class LabPermissions
{
    public const SAMPLE_COLLECTION = 'Sample Collection';

    public const LAB_ATTENDANT = 'Lab Attendant';

    public const RESULT_ENTRY = 'Result Entry';

    public const RESULT_EDIT = 'Edit Results';

    public const FRONT_DESK_PRINT = 'Front Desk Print';

    public const CRITICAL_REPORT = 'Critical Report';

    public const SAMPLES_REPORT = 'Samples Report';

    public const FINANCIAL_SUMMARY = 'Financial Summary';

    public const BOOKING = 'Create Booking';

    public const COMMISSION_ADMIN = 'Commission Admin';

    public const DOCTOR_PAYOUT = 'Doctor Payout';

    public const CASH_CLOSE = 'Cash Close';

    public const CASH_APPROVE = 'Cash Approve';

    /** Main Lab: manage collection centers (sites). */
    public const COLLECTION_CENTERS = 'Manage Collection Centers';

    public const GROUP = 'Laboratory';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::SAMPLE_COLLECTION,
            self::LAB_ATTENDANT,
            self::RESULT_ENTRY,
            self::RESULT_EDIT,
            self::FRONT_DESK_PRINT,
            self::CRITICAL_REPORT,
            self::SAMPLES_REPORT,
            self::FINANCIAL_SUMMARY,
            self::BOOKING,
            self::COMMISSION_ADMIN,
            self::DOCTOR_PAYOUT,
            self::CASH_CLOSE,
            self::CASH_APPROVE,
            self::COLLECTION_CENTERS,
        ];
    }

    /**
     * Permissions that Main Lab scope may access without an explicit grant
     * (mirrors LIMS policies: Main Lab OR named permission).
     *
     * @return list<string>
     */
    public static function mainLabImplied(): array
    {
        return [
            self::COMMISSION_ADMIN,
            self::DOCTOR_PAYOUT,
            self::COLLECTION_CENTERS,
            self::CASH_APPROVE,
        ];
    }

    /**
     * Ordered map of landing route name => the permission required to reach it.
     *
     * Used to send a user straight to the first module they actually have
     * access to (after login, or when they hit a module they lack access to).
     * Every route listed here is guaranteed to pass EnsureModuleAccess for a
     * user holding the mapped permission.
     *
     * @return array<string, string>
     */
    public static function landingRoutes(): array
    {
        return [
            'pathology.sample_portal' => self::SAMPLE_COLLECTION,
            'pathology.lab_attendant' => self::LAB_ATTENDANT,
            'pathology.result_entry.search' => self::RESULT_ENTRY,
            'pathology.front_desk_print' => self::FRONT_DESK_PRINT,
            'pathology.critical_report' => self::CRITICAL_REPORT,
            'pathology.lab_samples_report' => self::SAMPLES_REPORT,
            'pathology.lab_financial_summary' => self::FINANCIAL_SUMMARY,
            'pathology.bookings.create' => self::BOOKING,
            'pathology.sample_batches.index' => self::SAMPLE_COLLECTION,
            'pathology.lims_doctors.index' => self::COMMISSION_ADMIN,
            'pathology.collection_centers.index' => self::COLLECTION_CENTERS,
        ];
    }

    /**
     * Sample transit UI: collectors, attendants, or scoped lab users.
     */
    public static function canAccessSampleTransit($user): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'isMainLabScope') && $user->isMainLabScope()) {
            return true;
        }

        if (method_exists($user, 'isCollectionCenterScope') && $user->isCollectionCenterScope()) {
            return true;
        }

        return $user->hasAnyPermission([self::SAMPLE_COLLECTION, self::LAB_ATTENDANT]);
    }

    public static function permissionForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        if ($routeName === 'pathology.index') {
            return null;
        }

        if (str_starts_with($routeName, 'pathology.sample_batches')) {
            return self::SAMPLE_COLLECTION;
        }

        if (str_starts_with($routeName, 'pathology.bookings')) {
            return self::BOOKING;
        }

        if (str_starts_with($routeName, 'pathology.collection_centers')) {
            return self::COLLECTION_CENTERS;
        }

        if (
            str_starts_with($routeName, 'pathology.lims_doctors')
            || str_starts_with($routeName, 'pathology.commission_rules')
            || str_starts_with($routeName, 'pathology.commission_snapshots')
        ) {
            return self::COMMISSION_ADMIN;
        }

        if (str_starts_with($routeName, 'pathology.sample_portal')) {
            return self::SAMPLE_COLLECTION;
        }

        if (str_starts_with($routeName, 'pathology.lab_attendant')) {
            return self::LAB_ATTENDANT;
        }

        if (
            str_ends_with($routeName, '.result_entry.edit')
        ) {
            return self::RESULT_EDIT;
        }

        if (str_ends_with($routeName, '.result_entry.save')) {
            return null;
        }

        if (
            str_starts_with($routeName, 'pathology.result_entry')
            || str_starts_with($routeName, 'laboratory.result_entry')
            || str_starts_with($routeName, 'pathology.print_report')
            || str_starts_with($routeName, 'laboratory.print_report')
        ) {
            return self::RESULT_ENTRY;
        }

        if (str_starts_with($routeName, 'pathology.front_desk_print')) {
            return self::FRONT_DESK_PRINT;
        }

        if (str_starts_with($routeName, 'pathology.critical_report')) {
            return self::CRITICAL_REPORT;
        }

        if (str_starts_with($routeName, 'pathology.lab_samples_report')) {
            return self::SAMPLES_REPORT;
        }

        if (str_starts_with($routeName, 'pathology.lab_financial_summary')) {
            return self::FINANCIAL_SUMMARY;
        }

        return null;
    }

    public static function isLabRoute(?string $routeName, string $path): bool
    {
        if ($routeName && self::permissionForRoute($routeName) !== null) {
            return true;
        }

        if ($routeName === 'pathology.index') {
            return true;
        }

        return str_starts_with($path, 'pathology')
            || str_starts_with($path, 'laboratory')
            || str_starts_with($path, 'lab-attendant');
    }
}
