<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Support\LabRegistrationNumber;

class LabPatientLookupService
{
    public function __construct(
        private DesktopBookingSyncService $desktopSync,
        private DesktopTestCatalogSyncService $testCatalogSync
    ) {}

    /**
     * @return array{patient: ?LaboratoryPatient, imported: bool, error: ?string}
     */
    public function findOrImportByLabRegNo(string $labRegNo): array
    {
        $labRegNo = LabRegistrationNumber::normalize($labRegNo);

        if ($labRegNo === '') {
            return ['patient' => null, 'imported' => false, 'error' => null];
        }

        $this->testCatalogSync->syncIfStale(15);

        $patient = LabRegistrationNumber::applyToQuery(
            LaboratoryPatient::query(),
            'lab_registration_no',
            $labRegNo
        )->first();

        if ($patient) {
            return ['patient' => $patient, 'imported' => false, 'error' => null];
        }

        $sync = $this->desktopSync->syncBookingByLabRegNo($labRegNo);

        return [
            'patient' => $sync['patient'],
            'imported' => $sync['imported'],
            'error' => $this->desktopSync->getLastError(),
        ];
    }
}
