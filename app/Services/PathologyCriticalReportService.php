<?php

namespace App\Services;

use App\Models\Test;
use App\Models\TestResult;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PathologyCriticalReportService
{
    public function __construct(
        private PathologyFormulaService $formulaService
    ) {}

    /**
     * @return array{
     *     date_from: Carbon,
     *     date_to: Carbon,
     *     patient_count: int,
     *     critical_count: int,
     *     records: Collection<int, array<string, mixed>>
     * }
     */
    public function buildReport(string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $results = TestResult::query()
            ->with(['laboratoryPatient', 'test', 'testParticular'])
            ->whereIn('test_id', Test::pathologyIds())
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        $records = collect();
        $patientIds = [];

        foreach ($results as $result) {
            $particular = $result->testParticular;
            $patient = $result->laboratoryPatient;

            if (! $particular || ! $patient || ! is_numeric($result->result_value)) {
                continue;
            }

            if ($particular->normal_range_min === null && $particular->normal_range_max === null) {
                continue;
            }

            $flag = $this->formulaService->isAbnormal(
                (float) $result->result_value,
                $particular->normal_range_min,
                $particular->normal_range_max
            );

            if ($flag !== 'high' && $flag !== 'low') {
                continue;
            }

            $patientIds[$patient->id] = true;

            $records->push([
                'result_date' => $result->created_at,
                'patient_name' => $patient->patient_name,
                'lab_registration_no' => $patient->lab_registration_no,
                'mr_no' => $patient->mr_no,
                'contact_no' => $patient->contact_no,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'test_name' => $result->test?->name ?? '—',
                'parameter' => $particular->name,
                'result_value' => $result->result_value,
                'unit' => $particular->unit,
                'reference_range' => $this->formatReferenceRange($particular),
                'flag' => strtoupper($flag),
                'lab_patient_id' => $patient->id,
                'test_id' => $result->test_id,
            ]);
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
            'patient_count' => count($patientIds),
            'critical_count' => $records->count(),
            'records' => $records,
        ];
    }

    private function formatReferenceRange($particular): string
    {
        if ($particular->normal_range_min !== null || $particular->normal_range_max !== null) {
            return ($particular->normal_range_min ?? '—') . ' – ' . ($particular->normal_range_max ?? '—');
        }

        return $particular->reference_text ?: '—';
    }
}
