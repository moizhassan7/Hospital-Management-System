<?php

namespace App\Services;

use App\Support\LabRegistrationNumber;

class LabPatientLookupService
{
    /**
     * Look up pathology patient record locally.
     *
     * @return array{patient: ?\App\Models\LaboratoryPatient, imported: bool, error: ?string, ambiguous_patients: \Illuminate\Support\Collection}
     */
    public function findOrImportByLabRegNo(string $labRegNo, ?int $patientId = null): array
    {
        $labRegNo = LabRegistrationNumber::normalize($labRegNo);

        if ($labRegNo === '') {
            return ['patient' => null, 'imported' => false, 'error' => null, 'ambiguous_patients' => collect()];
        }

        $query = LabRegistrationNumber::applyToQuery(
            \App\Models\LaboratoryPatient::query(),
            'lab_registration_no',
            $labRegNo
        )->orderByDesc('created_at');

        $patients = $query->get();

        if ($patients->isEmpty()) {
            return ['patient' => null, 'imported' => false, 'error' => null, 'ambiguous_patients' => collect()];
        }

        if ($patientId) {
            $patient = $patients->firstWhere('id', $patientId);
            if ($patient) {
                return ['patient' => $patient, 'imported' => false, 'error' => null, 'ambiguous_patients' => collect()];
            }
        }

        if ($patients->count() === 1) {
            return ['patient' => $patients->first(), 'imported' => false, 'error' => null, 'ambiguous_patients' => collect()];
        }

        return [
            'patient' => null,
            'imported' => false,
            'error' => null,
            'ambiguous_patients' => $patients,
        ];
    }
}
