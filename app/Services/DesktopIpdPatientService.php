<?php

namespace App\Services;

use App\Models\Desktop\DesktopIndoorPatientHistory;
use App\Models\Desktop\DesktopIpdPatientDetail;
use App\Support\DesktopDatabase;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DesktopIpdPatientService
{
    private ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isDesktopEnabled(): bool
    {
        return DesktopDatabase::isEnabled();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{paginator: LengthAwarePaginator, summary: array<string, int>}
     */
    public function searchHistory(array $filters): array
    {
        [$from, $to] = $this->parseDateRange($filters);

        $query = DesktopIndoorPatientHistory::query()
            ->whereBetween('RegistrationDate', [$from, $to]);

        $this->applyCommonPatientFilters($query, $filters, [
            'mr' => 'Mr_No',
            'name' => 'PatientName',
            'mobile' => 'MobileNo',
            'ward' => 'WardNo',
            'bed' => 'BedNo',
            'patient_type' => 'PatientType',
            'slip' => 'SlipNo',
            'cnic' => 'CNIC',
        ]);

        $this->applyDischargeFilter($query, $filters, 'Discharge_Date');

        $summaryQuery = clone $query;

        $paginator = $query
            ->orderByDesc('RegistrationDate')
            ->orderByDesc('Ipd_Id')
            ->paginate(50)
            ->withQueryString();

        return [
            'paginator' => $paginator,
            'summary' => $this->buildHistorySummary($summaryQuery),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{paginator: LengthAwarePaginator, summary: array<string, int>}
     */
    public function searchDetail(array $filters): array
    {
        [$from, $to] = $this->parseDateRange($filters);

        $query = DesktopIpdPatientDetail::query()
            ->whereBetween('Reg_date', [$from, $to]);

        $this->applyCommonPatientFilters($query, $filters, [
            'mr' => 'MR_No',
            'name' => 'PatientName',
            'mobile' => 'MobileNo',
            'ward' => 'Ward_No',
            'bed' => 'Bed_No',
            'patient_type' => 'PatientType',
            'slip' => 'Slip_ID',
            'cnic' => 'CNIC',
        ]);

        if (! empty($filters['doctor'])) {
            $query->where('Dr_Name', 'like', '%' . trim((string) $filters['doctor']) . '%');
        }

        if (! empty($filters['department'])) {
            $query->where('Dr_Dept', 'like', '%' . trim((string) $filters['department']) . '%');
        }

        if (! empty($filters['active'])) {
            if ($filters['active'] === 'yes') {
                $query->where('Active', 1);
            } elseif ($filters['active'] === 'no') {
                $query->where(function (Builder $q) {
                    $q->whereNull('Active')->orWhere('Active', '!=', 1);
                });
            }
        }

        $this->applyDischargeFilter($query, $filters, 'Disharge_date');

        $summaryQuery = clone $query;

        $paginator = $query
            ->orderByDesc('Reg_date')
            ->orderByDesc('Slip_ID')
            ->paginate(50)
            ->withQueryString();

        return [
            'paginator' => $paginator,
            'summary' => $this->buildDetailSummary($summaryQuery),
        ];
    }

    public function findHistory(int $id): ?DesktopIndoorPatientHistory
    {
        return DesktopIndoorPatientHistory::query()->find($id);
    }

    public function findDetail(int $id): ?DesktopIpdPatientDetail
    {
        return DesktopIpdPatientDetail::query()->find($id);
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
     * @param  array<string, string>  $map
     */
    private function applyCommonPatientFilters(Builder $query, array $filters, array $map): void
    {
        foreach ($map as $filterKey => $column) {
            if (empty($filters[$filterKey])) {
                continue;
            }

            $value = trim((string) $filters[$filterKey]);

            if (in_array($filterKey, ['name', 'doctor', 'department'], true)) {
                $query->where($column, 'like', '%' . $value . '%');
            } else {
                $query->where($column, $value);
            }
        }
    }

    private function applyDischargeFilter(Builder $query, array $filters, string $dischargeColumn): void
    {
        $status = $filters['status'] ?? 'all';

        if ($status === 'admitted') {
            $query->whereNull($dischargeColumn);
        } elseif ($status === 'discharged') {
            $query->whereNotNull($dischargeColumn);
        }
    }

    /**
     * @return array<string, int>
     */
    private function buildHistorySummary(Builder $query): array
    {
        $base = clone $query;

        return [
            'total' => (clone $base)->count(),
            'admitted' => (clone $base)->whereNull('Discharge_Date')->count(),
            'discharged' => (clone $base)->whereNotNull('Discharge_Date')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function buildDetailSummary(Builder $query): array
    {
        $base = clone $query;

        return [
            'total' => (clone $base)->count(),
            'admitted' => (clone $base)->whereNull('Disharge_date')->count(),
            'discharged' => (clone $base)->whereNotNull('Disharge_date')->count(),
            'active' => (clone $base)->where('Active', 1)->count(),
        ];
    }

    public function testConnection(): bool
    {
        if (! $this->isDesktopEnabled()) {
            $this->lastError = 'Desktop SQL Server connection is not configured.';

            return false;
        }

        try {
            DB::connection('desktop')->getPdo();

            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();

            return false;
        }
    }
}
