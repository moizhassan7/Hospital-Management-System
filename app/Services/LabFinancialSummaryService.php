<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Models\Test;
use App\Models\TestHead;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LabFinancialSummaryService
{
    /**
     * Desktop-synced pathology test rate list (all catalog prices).
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     summary: array<string, float|int>,
     *     rate_rows: LengthAwarePaginator,
     *     test_heads: Collection<int, TestHead>
     * }
     */
    public function buildRateList(array $filters, int $perPage = 100): array
    {
        $query = Test::query()
            ->pathology()
            ->with('testHead:id,name')
            ->when(! empty($filters['test_head_id']), fn ($q) => $q->where('test_head_id', $filters['test_head_id']))
            ->when(! empty($filters['test_name']), function ($q) use ($filters) {
                $term = '%' . mb_strtolower(trim((string) $filters['test_name'])) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(CAST(test_id AS TEXT)) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(CAST(desktop_test_id AS TEXT)) LIKE ?', [$term]);
                });
            })
            ->orderBy(
                TestHead::select('name')->whereColumn('test_heads.id', 'tests.test_head_id')
            )
            ->orderBy('name');

        $allMatching = (clone $query)->get(['id', 'test_id', 'desktop_test_id', 'name', 'price', 'test_head_id', 'type', 'is_active']);

        $summary = [
            'tests' => $allMatching->count(),
            'with_price' => $allMatching->filter(fn (Test $t) => (float) $t->price > 0)->count(),
            'total_rate_value' => round((float) $allMatching->sum(fn (Test $t) => (float) $t->price), 2),
            'heads' => $allMatching->pluck('test_head_id')->filter()->unique()->count(),
        ];

        $rows = $allMatching->map(fn (Test $test) => [
            'desktop_test_id' => $test->desktop_test_id ?: $test->test_id,
            'test_name' => $test->name,
            'test_head' => $test->testHead?->name ?? '—',
            'type' => $test->type ?: '—',
            'price' => $this->toFloat($test->price),
            'is_active' => (bool) $test->is_active,
        ]);

        $testHeads = TestHead::query()
            ->where('category', 'Pathology')
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'summary' => $summary,
            'rate_rows' => $this->paginateCollection($rows, $filters['rpage'] ?? 1, $perPage, 'rpage'),
            'test_heads' => $testHeads,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     date_from: Carbon,
     *     date_to: Carbon,
     *     summary: array<string, float|int>,
     *     patient_rows: LengthAwarePaginator,
     *     test_rows: LengthAwarePaginator
     * }
     */
    public function buildReport(array $filters, int $perPage = 50): array
    {
        [$from, $to] = $this->parseDateRange($filters);

        $patientsQuery = LaboratoryPatient::query()
            ->select([
                'id',
                'mr_no',
                'lab_registration_no',
                'patient_name',
                'contact_no',
                'selected_tests',
                'sub_total',
                'discount',
                'discount_type',
                'discount_value',
                'grand_total',
                'lab_share_total',
                'hospital_share_total',
                'paid_amount',
                'due_amount',
                'previous_due',
                'status',
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
            $effectiveId = $filters['collection_center_id'];
        }

        if ($effectiveId) {
            $patientsQuery->whereHas('limsBooking', function ($q) use ($effectiveId) {
                $q->where('collection_center_id', $effectiveId);
            });
        }

        $patients = $patientsQuery->get();

        $patientRows = collect();
        $testAggregates = [];

        foreach ($patients as $patient) {
            $row = $this->mapPatientRow($patient);

            if (! $this->matchesPatientFilters($row, $filters)) {
                continue;
            }

            $patientRows->push($row);

            if ($patient->is_returned || $row['status'] === 'Cancelled') {
                continue;
            }

            foreach ($patient->getSelectedTestsArray() as $test) {
                if (! empty($test['carry_out']) && ! filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }

                $testName = trim((string) ($test['name'] ?? ''));
                if ($testName === '') {
                    $testName = 'Unknown test';
                }

                if (! empty($filters['test_name']) && stripos($testName, (string) $filters['test_name']) === false) {
                    continue;
                }

                $price = $this->toFloat($test['price'] ?? 0);
                $key = mb_strtolower($testName);

                if (! isset($testAggregates[$key])) {
                    $testAggregates[$key] = [
                        'test_name' => $testName,
                        'test_count' => 0,
                        'revenue' => 0.0,
                    ];
                }

                $testAggregates[$key]['test_count']++;
                $testAggregates[$key]['revenue'] += $price;
            }
        }

        $testRows = collect(array_values($testAggregates))
            ->sortByDesc('revenue')
            ->values();

        $expenses = \App\Models\Expense::whereBetween('expense_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])->sum('amount');
        
        $summary = $this->buildSummary($patientRows, $testRows, $expenses);

        return [
            'date_from' => $from,
            'date_to' => $to,
            'summary' => $summary,
            'patient_rows' => $this->paginateCollection($patientRows, $filters['page'] ?? 1, $perPage, 'page'),
            'test_rows' => $this->paginateCollection($testRows, $filters['tpage'] ?? 1, $perPage, 'tpage'),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function parseDateRange(array $filters): array
    {
        $from = Carbon::parse($filters['date_from'] ?? now()->format('Y-m-d'))->startOfDay();
        $to = Carbon::parse($filters['date_to'] ?? now()->format('Y-m-d'))->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPatientRow(LaboratoryPatient $patient): array
    {
        $tests = $patient->getSelectedTestsArray();
        $testNames = collect($tests)->pluck('name')->filter()->implode(', ');
        $due = $this->toFloat($patient->due_amount);

        return [
            'lab_patient_id' => $patient->id,
            'registration_date' => $patient->created_at,
            'lab_registration_no' => $patient->lab_registration_no,
            'desktop_invoice' => null,
            'mr_no' => $patient->mr_no,
            'patient_name' => $patient->patient_name,
            'contact_no' => $patient->contact_no,
            'tests' => $testNames,
            'tests_data' => $tests,
            'test_count' => count($tests),
            'sub_total' => $this->toFloat($patient->sub_total),
            'discount' => $this->toFloat($patient->discount),
            'discount_type' => $patient->discount_type ?? ($patient->discount > 0 ? 'flat' : '—'),
            'discount_value' => $this->toFloat($patient->discount_value ?? ($patient->discount > 0 ? $patient->discount : 0)),
            'grand_total' => $this->toFloat($patient->grand_total),
            'lab_share_total' => $this->toFloat($patient->lab_share_total),
            'hospital_share_total' => $this->toFloat($patient->hospital_share_total),
            'paid_amount' => $this->toFloat($patient->paid_amount),
            'due_amount' => $due,
            'previous_due' => $this->toFloat($patient->previous_due),
            'payment_status' => $due > 0.009 ? 'due' : 'paid',
            'status' => $patient->status,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function matchesPatientFilters(array $row, array $filters): bool
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

        if (! empty($filters['invoice']) && stripos((string) $row['lab_registration_no'], (string) $filters['invoice']) === false) {
            return false;
        }

        $paymentStatus = $filters['payment_status'] ?? 'all';
        if ($paymentStatus !== 'all' && $row['payment_status'] !== $paymentStatus) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, float|int>
     */
    private function buildSummary(Collection $patientRows, Collection $testRows, float $expenses = 0): array
    {
        $activeRows = $patientRows->filter(fn($row) => $row['status'] !== 'Cancelled');
        
        $paidAmount = round((float) $activeRows->sum('paid_amount'), 2);

        return [
            'patients' => $patientRows->count(),
            'tests' => (int) $testRows->sum('test_count'),
            'unique_tests' => $testRows->count(),
            'sub_total' => round((float) $activeRows->sum('sub_total'), 2),
            'discount' => round((float) $activeRows->sum('discount'), 2),
            'grand_total' => round((float) $activeRows->sum('grand_total'), 2),
            'paid_amount' => $paidAmount,
            'expenses' => round($expenses, 2),
            'cash_in_hand' => round($paidAmount - $expenses, 2),
            'due_amount' => round((float) $activeRows->sum('due_amount'), 2),
            'lab_share_total' => round((float) $activeRows->sum('lab_share_total'), 2),
            'hospital_share_total' => round((float) $activeRows->sum('hospital_share_total'), 2),
            'paid_patients' => $activeRows->where('payment_status', 'paid')->count(),
            'due_patients' => $activeRows->where('payment_status', 'due')->count(),
        ];
    }

    private function toFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return round((float) $value, 2);
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
