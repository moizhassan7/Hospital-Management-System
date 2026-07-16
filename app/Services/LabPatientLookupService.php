<?php

namespace App\Services;

use App\Support\LabRegistrationNumber;

class LabPatientLookupService
{
    /**
     * Look up pathology patient record locally.
     *
     * @return array{patient: ?\App\Models\LaboratoryPatient, imported: bool, error: ?string}
     */
    public function findOrImportByLabRegNo(string $labRegNo): array
    {
        $labRegNo = LabRegistrationNumber::normalize($labRegNo);

        if ($labRegNo === '') {
            return ['patient' => null, 'imported' => false, 'error' => null];
        }

        $patient = LabRegistrationNumber::applyToQuery(
            \App\Models\LaboratoryPatient::query(),
            'lab_registration_no',
            $labRegNo
        )->orderByDesc('created_at')->first();

        return [
            'patient' => $patient,
            'imported' => false,
            'error' => null,
        ];
    }
}
