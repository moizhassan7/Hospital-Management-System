<?php

// Local/dev cleanup. Review + back up DB before running. Not auto-run.
//
// This migration drops legacy Hospital Management System (HMS) tables that are
// no longer used by the standalone Pathology Lab app. It is intentionally NOT
// run as part of any automated pipeline. A human must review it and back up the
// database before executing `php artisan migrate`.
//
// The original create-table migrations are deliberately left in place so the
// migration history stays intact; this drop migration is the safe forward-only
// mechanism for removing the legacy tables.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy HMS tables to drop, ordered children-before-parents so the drop
     * works even on engines that enforce foreign keys. Foreign key checks are
     * also disabled below as a belt-and-braces safeguard.
     *
     * @var list<string>
     */
    private array $tables = [
        // Emergency module (pivot + children first, then reference tables)
        'emergency_patient_service',
        'emergency_patients',
        'emergency_charges',
        'emergency_services',

        // Store / inventory module
        'stock_issue_items',
        'stock_issues',
        'stock_returns',
        'purchase_bill_items',
        'purchase_bills',
        'consumable_items',
        'non_consumable_items',
        'suppliers',

        // Procedures / day care
        'day_care_procedures',
        'procedures',

        // Inpatient / OPD / discharge
        'inpatient_details',
        'patient_discharges',
        'diagnoses',
        'indoor_patients',
        'opd_appointments',

        // Legacy lab order tables (superseded by live desktop lookup)
        'lab_order_tests',
        'lab_orders',
        'lab_test_results',

        // Doctors
        'doctors',
        'doctor_types',

        // Facility structure
        'rooms',
        'floors',
        'specialities',
        'departments',

        // Shared lookups
        'locations',
        'categories',

        // Core patient table (parent of most of the above)
        'patients',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->tables as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // No-op: recreating the dropped legacy HMS tables is out of scope.
        // These tables belong to the retired Hospital Management System and are
        // not part of the Pathology Lab app schema, so there is nothing to
        // restore here. Restore from a database backup if a rollback is needed.
    }
};
