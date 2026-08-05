<?php

namespace App\Services;

use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\Test;
use App\Models\TestResult;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LabSamplesReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     date_from: Carbon,
     *     date_to: Carbon,
     *     summary: array<string, int>,
     *     test_rows: LengthAwarePaginator,
     *     vial_rows: LengthAwarePaginator
     * }
     */
    public function buildReport(array $filters, int $perPage = 50): array
    {
        [$from, $to] = $this->parseDateRange($filters);

        // Load the pathology test catalog once (id => name) so the per-row and
        // per-vial lookups below hit memory instead of issuing a query each time.
        $pathologyTestNames = Test::pathologyNamesById();
        $pathologyTestIds = $pathologyTestNames->keys()->flip();

        $patientsQuery = LaboratoryPatient::query()
            ->select([
                'id',
                'mr_no',
                'lab_registration_no',
                'patient_name',
                'contact_no',
                'selected_tests',
                'created_at',
            ])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at');

        $effectiveId = null;
        if (auth()->check() && !auth()->user()->isSuperAdmin()) {
            $effectiveId = method_exists(auth()->user(), 'getEffectiveCollectionCenterId')
                ? auth()->user()->getEffectiveCollectionCenterId()
                : null;
        }

        if (!empty($filters['collection_center_id'])) {
            // Allow override if explicitly requested in filters
            // (e.g. by super admin or if the user is allowed to select from UI)
            $effectiveId = $filters['collection_center_id'];
        }

        if ($effectiveId) {
            $patientsQuery->whereHas('limsBooking', function ($q) use ($effectiveId) {
                $q->where('collection_center_id', $effectiveId);
            });
        }

        $patients = $patientsQuery->get();

        $patientIds = $patients->pluck('id');
        $resultsExist = TestResult::query()
            ->whereIn('laboratory_patient_id', $patientIds)
            ->selectRaw('laboratory_patient_id, test_id')
            ->distinct()
            ->get()
            ->mapWithKeys(fn ($r) => [$r->laboratory_patient_id . '_' . $r->test_id => true]);

        $testRows = collect();
        $vialRows = collect();

        foreach ($patients as $patient) {
            foreach ($patient->getSelectedTestsArray() as $test) {
                $testId = (int) ($test['id'] ?? 0);
                if (! $pathologyTestIds->has($testId)) {
                    continue;
                }

                if (! empty($test['carry_out']) && ! filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }

                $sampleStatus = $test['sample_status'] ?? LabSampleVial::STATUS_NOT_COLLECTED;
                $resultStatus = ($test['status'] ?? 'Pending') === 'Completed' ? 'completed' : 'pending';
                $hasResults = $resultsExist->has($patient->id . '_' . $testId);

                $row = [
                    'lab_patient_id' => $patient->id,
                    'test_id' => $testId,
                    'registration_date' => $patient->created_at,
                    'lab_registration_no' => $patient->lab_registration_no,
                    'mr_no' => $patient->mr_no,
                    'patient_name' => $patient->patient_name,
                    'contact_no' => $patient->contact_no,
                    'test_name' => $test['name'] ?? '—',
                    'sample_status' => $sampleStatus,
                    'sample_status_label' => LabSampleVial::statusLabel($sampleStatus),
                    'sample_collected_at' => $test['sample_collected_at'] ?? null,
                    'sample_received_in_lab_at' => $test['sample_received_in_lab_at'] ?? null,
                    'result_status' => $resultStatus,
                    'result_completed_at' => $test['result_completed_at'] ?? $test['result_reported_at'] ?? null,
                    'has_results' => $hasResults,
                ];

                if (! $this->matchesTestFilters($row, $filters)) {
                    continue;
                }

                $testRows->push($row);
            }
        }

        $vialsQuery = LabSampleVial::query()
            ->select('lab_sample_vials.*')
            ->join('laboratory_patients', 'laboratory_patients.id', '=', 'lab_sample_vials.laboratory_patient_id')
            ->whereBetween('laboratory_patients.created_at', [$from, $to]);

        if ($effectiveId) {
            $vialsQuery->whereExists(function ($query) use ($effectiveId) {
                $query->select(DB::raw(1))
                      ->from('lims_bookings')
                      ->whereColumn('lims_bookings.laboratory_patient_id', 'laboratory_patients.id')
                      ->where('lims_bookings.collection_center_id', $effectiveId);
            });
        }

        $vials = $vialsQuery->with([
                'laboratoryPatient:id,mr_no,lab_registration_no,patient_name,created_at',
            ])
            ->orderByDesc('lab_sample_vials.collected_at')
            ->orderByDesc('lab_sample_vials.id')
            ->get();

        foreach ($vials as $vial) {
            $patient = $vial->laboratoryPatient;
            if (! $patient) {
                continue;
            }

            $testNames = collect($vial->test_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $pathologyTestNames->has($id))
                ->unique()
                ->sort()
                ->map(fn ($id) => $pathologyTestNames->get($id))
                ->implode(', ');

            if ($testNames === '') {
                continue;
            }

            $row = [
                'vial_id' => $vial->id,
                'lab_patient_id' => $patient->id,
                'registration_date' => $patient->created_at,
                'lab_registration_no' => $patient->lab_registration_no,
                'patient_name' => $patient->patient_name,
                'barcode' => $vial->barcode,
                'vial_type' => $vial->vial_type,
                'vial_number' => $vial->vial_number,
                'tests' => $testNames,
                'status' => $vial->status,
                'status_label' => LabSampleVial::statusLabel($vial->status),
                'collected_at' => $vial->collected_at,
                'received_in_lab_at' => $vial->received_in_lab_at,
                'reported_at' => $vial->reported_at,
                'expires_at' => $vial->expires_at,
            ];

            if (! $this->matchesVialFilters($row, $filters)) {
                continue;
            }

            $vialRows->push($row);
        }

        $summary = $this->buildSummary($testRows, $vialRows, $patients->count());

        return [
            'date_from' => $from,
            'date_to' => $to,
            'summary' => $summary,
            'test_rows' => $this->paginateCollection($testRows, $filters['page'] ?? 1, $perPage, 'page'),
            'vial_rows' => $this->paginateCollection($vialRows, $filters['vpage'] ?? 1, $perPage, 'vpage'),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function parseDateRange(array $filters): array
    {
        $from = Carbon::parse($filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d'))->startOfDay();
        $to = Carbon::parse($filters['date_to'] ?? now()->format('Y-m-d'))->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function matchesTestFilters(array $row, array $filters): bool
    {
        if (! empty($filters['lab_reg']) && stripos((string) $row['lab_registration_no'], (string) $filters['lab_reg']) === false) {
            return false;
        }

        if (! empty($filters['mr']) && (string) $row['mr_no'] !== (string) $filters['mr']) {
            return false;
        }

        if (! empty($filters['name']) && stripos((string) $row['patient_name'], (string) $filters['name']) === false) {
            return false;
        }

        if (! empty($filters['sample_status']) && $filters['sample_status'] !== 'all' && $row['sample_status'] !== $filters['sample_status']) {
            return false;
        }

        if (! empty($filters['result_status']) && $filters['result_status'] !== 'all' && $row['result_status'] !== $filters['result_status']) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function matchesVialFilters(array $row, array $filters): bool
    {
        if (! empty($filters['lab_reg']) && stripos((string) $row['lab_registration_no'], (string) $filters['lab_reg']) === false) {
            return false;
        }

        if (! empty($filters['name']) && stripos((string) $row['patient_name'], (string) $filters['name']) === false) {
            return false;
        }

        if (! empty($filters['barcode']) && stripos((string) $row['barcode'], (string) $filters['barcode']) === false) {
            return false;
        }

        if (! empty($filters['sample_status']) && $filters['sample_status'] !== 'all' && $row['status'] !== $filters['sample_status']) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, int>
     */
    private function buildSummary(Collection $testRows, Collection $vialRows, int $patientCount): array
    {
        return [
            'patients' => $patientCount,
            'tests' => $testRows->count(),
            'vials' => $vialRows->count(),
            'not_collected' => $testRows->where('sample_status', LabSampleVial::STATUS_NOT_COLLECTED)->count(),
            'collected' => $testRows->where('sample_status', LabSampleVial::STATUS_COLLECTED)->count(),
            'in_lab' => $testRows->whereIn('sample_status', [LabSampleVial::STATUS_IN_LAB, LabSampleVial::STATUS_PROCESSING])->count(),
            'completed' => $testRows->where('result_status', 'completed')->count(),
            'pending_results' => $testRows->where('result_status', 'pending')->whereIn('sample_status', [
                LabSampleVial::STATUS_IN_LAB,
                LabSampleVial::STATUS_PROCESSING,
                LabSampleVial::STATUS_COLLECTED,
                LabSampleVial::STATUS_COMPLETED,
            ])->count(),
        ];
    }

    private function paginateCollection(Collection $items, int $page, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = max(1, $page);
        $total = $items->count();
        $slice = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }
}
