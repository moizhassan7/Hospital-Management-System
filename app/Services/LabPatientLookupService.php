<?php

namespace App\Services;

use App\Support\LabRegistrationNumber;

class LabPatientLookupService
{
    public function __construct(
        private DesktopBookingSyncService $desktopSync,
        private DesktopTestCatalogSyncService $testCatalogSync
    ) {}

    /**
     * Lab bookings/patients are read live from the desktop SQL Server view (Leb_reg_test_info).
     * Only tests/particulars are synced into the web DB — see pathology:sync-desktop.
     *
     * @return array{patient: ?\App\Models\LaboratoryPatient, imported: bool, error: ?string}
     */
    public function findOrImportByLabRegNo(string $labRegNo): array
    {
        $labRegNo = LabRegistrationNumber::normalize($labRegNo);

        if ($labRegNo === '') {
            return ['patient' => null, 'imported' => false, 'error' => null];
        }

        // Re-use local record when this lab reg was already opened in the web app
        // (sample collection, result entry state lives here).
        $patient = LabRegistrationNumber::applyToQuery(
            \App\Models\LaboratoryPatient::query(),
            'lab_registration_no',
            $labRegNo
        )->first();

        if (!$patient) {
            $patient = LabRegistrationNumber::applyToQuery(
                \App\Models\LaboratoryPatient::query(),
                'desktop_invoice',
                $labRegNo
            )->first();
        }

        if ($patient) {
            if (!$patient->lab_registration_no) {
                $patient->update(['lab_registration_no' => $labRegNo]);
            }

            return ['patient' => $patient, 'imported' => false, 'error' => null];
        }

        // Ensure synced test catalog exists for mapping desktop test IDs.
        $this->testCatalogSync->syncIfStale(15);

        // Read booking directly from desktop view and create local workflow record.
        $sync = $this->desktopSync->syncBookingByLabRegNo($labRegNo);

        return [
            'patient' => $sync['patient'],
            'imported' => $sync['imported'],
            'error' => $this->desktopSync->getLastError(),
        ];
    }
}
