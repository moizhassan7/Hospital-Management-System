<?php

namespace App\Services;

use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\TestResult;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaboratoryPatientExportService
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function parseDateRange(string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getPatientSummaryRows(string $dateFrom, string $dateTo): Collection
    {
        [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

        return LaboratoryPatient::query()
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get()
            ->map(fn (LaboratoryPatient $patient) => $this->mapPatientSummaryRow($patient));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getPatientTestDetailRows(string $dateFrom, string $dateTo): Collection
    {
        [$from, $to] = $this->parseDateRange($dateFrom, $dateTo);

        $patients = LaboratoryPatient::query()
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $resultsExist = TestResult::query()
            ->whereIn('laboratory_patient_id', $patients->pluck('id'))
            ->selectRaw('laboratory_patient_id, test_id')
            ->distinct()
            ->get()
            ->mapWithKeys(fn ($r) => [$r->laboratory_patient_id . '_' . $r->test_id => true]);

        $rows = collect();

        foreach ($patients as $patient) {
            $tests = $patient->getSelectedTestsArray();

            if (empty($tests)) {
                $rows->push($this->mapPatientTestRow($patient, [], $resultsExist));

                continue;
            }

            foreach ($tests as $test) {
                $rows->push($this->mapPatientTestRow($patient, $test, $resultsExist));
            }
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPatientSummaryRow(LaboratoryPatient $patient): array
    {
        $tests = $patient->getSelectedTestsArray();
        $testNames = collect($tests)->pluck('name')->filter()->implode(', ');

        return [
            'registered_at' => $this->formatDateTime($patient->created_at),
            'last_updated_at' => $this->formatDateTime($patient->updated_at),
            'lab_registration_no' => $patient->lab_registration_no,
            'mr_no' => $patient->mr_no,
            'patient_name' => $patient->patient_name,
            'gender' => $patient->gender,
            'age' => $patient->age,
            'contact_no' => $patient->contact_no,
            'file_no' => $patient->file_no,
            'referred_by' => $patient->getConsultantLabel(),
            'self_referred' => $patient->self_referred ? 'Yes' : 'No',
            'desktop_invoice' => $patient->desktop_invoice,
            'tests' => $testNames,
            'test_count' => count($tests),
            'sub_total' => $patient->sub_total,
            'discount' => $patient->discount,
            'grand_total' => $patient->grand_total,
            'paid_amount' => $patient->paid_amount,
            'due_amount' => $patient->due_amount,
            'previous_due' => $patient->previous_due,
            'status' => $patient->status,
        ];
    }

    /**
     * @param  array<string, mixed>  $test
     * @param  Collection<string, bool>  $resultsExist
     * @return array<string, mixed>
     */
    private function mapPatientTestRow(LaboratoryPatient $patient, array $test, Collection $resultsExist): array
    {
        $testId = (int) ($test['id'] ?? 0);
        $sampleStatus = $test['sample_status'] ?? LabSampleVial::STATUS_NOT_COLLECTED;

        return [
            'registered_at' => $this->formatDateTime($patient->created_at),
            'last_updated_at' => $this->formatDateTime($patient->updated_at),
            'lab_registration_no' => $patient->lab_registration_no,
            'mr_no' => $patient->mr_no,
            'patient_name' => $patient->patient_name,
            'gender' => $patient->gender,
            'age' => $patient->age,
            'contact_no' => $patient->contact_no,
            'file_no' => $patient->file_no,
            'referred_by' => $patient->getConsultantLabel(),
            'self_referred' => $patient->self_referred ? 'Yes' : 'No',
            'desktop_invoice' => $patient->desktop_invoice,
            'test_name' => $test['name'] ?? '',
            'test_price' => $test['price'] ?? '',
            'test_status' => $test['status'] ?? 'Pending',
            'sample_status' => LabSampleVial::statusLabel($sampleStatus),
            'sample_collected_at' => $this->formatDateTime($test['sample_collected_at'] ?? null),
            'sample_received_in_lab_at' => $this->formatDateTime($test['sample_received_in_lab_at'] ?? null),
            'result_completed_at' => $this->formatDateTime($test['result_completed_at'] ?? $test['result_reported_at'] ?? null),
            'result_entered_by' => $test['result_entered_by_name'] ?? '',
            'has_results' => $testId > 0 && $resultsExist->has($patient->id . '_' . $testId) ? 'Yes' : 'No',
            'sub_total' => $patient->sub_total,
            'discount' => $patient->discount,
            'grand_total' => $patient->grand_total,
            'paid_amount' => $patient->paid_amount,
            'due_amount' => $patient->due_amount,
            'previous_due' => $patient->previous_due,
            'patient_status' => $patient->status,
        ];
    }

    private function formatDateTime(mixed $value): string
    {
        if (empty($value)) {
            return '';
        }

        return Carbon::parse($value)->format('d-m-Y h:i A');
    }

    public function buildExportFileName(string $dateFrom, string $dateTo): string
    {
        $from = Carbon::parse($dateFrom)->format('d-m-Y');
        $to = Carbon::parse($dateTo)->format('d-m-Y');

        return "patients-export-{$from}-to-{$to}.xlsx";
    }
}
