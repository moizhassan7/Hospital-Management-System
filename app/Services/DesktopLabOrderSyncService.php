<?php

namespace App\Services;

use App\Models\Desktop\DesktopRegTest;
use App\Models\LabOrder;
use App\Models\LabOrderTest;
use App\Models\SyncCursor;
use App\Models\Test;
use App\Support\DesktopDatabase;
use App\Support\DesktopSyncHash;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DesktopLabOrderSyncService
{
    public function __construct(
        private SyncLogService $syncLogService,
        private LabOrderMaterializer $materializer,
        private DesktopBookingSyncService $bookingMapper
    ) {}

    private ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return array{processed: int, inserted: int, updated: int, deactivated: int, materialized: int}
     */
    public function syncAll(bool $full = false): array
    {
        if (!DesktopDatabase::isEnabled()) {
            $this->lastError = DesktopDatabase::getDisabledReason();

            return ['processed' => 0, 'inserted' => 0, 'updated' => 0, 'deactivated' => 0, 'materialized' => 0];
        }

        $log = $this->syncLogService->start('lab_orders');
        $mapping = config('desktop_sync.sources.bookings.columns');

        try {
            $query = DesktopRegTest::query()->orderBy($mapping['line_id']);

            if (!$full) {
                $cursor = SyncCursor::getValue('lab_orders');
                if ($cursor !== null && $cursor !== '') {
                    $query->where($mapping['line_id'], '>', (int) $cursor);
                }
            }

            $rows = $query->get();
            $grouped = $rows->groupBy(fn ($row) => (string) ($row->{$mapping['booking_id']} ?? ''));

            $inserted = 0;
            $updated = 0;
            $materialized = 0;

            DB::transaction(function () use ($grouped, $mapping, &$inserted, &$updated, &$materialized) {
                foreach ($grouped as $bookingId => $lines) {
                    if ($bookingId === '') {
                        continue;
                    }

                    $result = $this->upsertBookingGroup($bookingId, $lines, $mapping);
                    $inserted += $result['inserted'];
                    $updated += $result['updated'];

                    if ($result['order']) {
                        $patient = $this->materializer->materialize($result['order']);
                        if ($patient) {
                            $materialized++;
                        }
                    }
                }
            });

            if ($rows->isNotEmpty()) {
                SyncCursor::setValue('lab_orders', (string) $rows->max($mapping['line_id']));
            }

            $stats = [
                'processed' => $rows->count(),
                'inserted' => $inserted,
                'updated' => $updated,
                'deactivated' => 0,
                'materialized' => $materialized,
            ];

            $this->syncLogService->finishSuccess($log, $stats, ['bookings' => $grouped->count()]);

            return $stats;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            $this->syncLogService->finishFailure($log, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Import one booking from SQL Server when it is not yet in the web DB.
     * Used as a lookup fallback when background sync has not run yet.
     */
    public function syncSingleByLabRegNo(string $labRegNo): ?\App\Models\LaboratoryPatient
    {
        if (!DesktopDatabase::isEnabled()) {
            $this->lastError = DesktopDatabase::getDisabledReason();

            return null;
        }

        $labRegNo = trim($labRegNo);
        $mapping = config('desktop_sync.sources.bookings.columns');
        $bookingCol = $mapping['booking_id'];

        $rows = DesktopRegTest::query()
            ->where($bookingCol, $labRegNo)
            ->orderBy($mapping['line_id'])
            ->get();

        if ($rows->isEmpty() && is_numeric($labRegNo)) {
            $rows = DesktopRegTest::query()
                ->where($bookingCol, (int) $labRegNo)
                ->orderBy($mapping['line_id'])
                ->get();
        }

        if ($rows->isEmpty()) {
            $this->lastError = 'Lab registration not found in desktop database.';

            return null;
        }

        $bookingId = (string) $rows->first()->{$bookingCol};
        $order = null;

        DB::transaction(function () use ($bookingId, $rows, $mapping, &$order) {
            $result = $this->upsertBookingGroup($bookingId, $rows, $mapping);
            $order = $result['order'];
        });

        if (!$order) {
            $this->lastError = 'Desktop booking could not be saved to web database.';

            return null;
        }

        $patient = $this->materializer->materialize($order->fresh(['orderTests']));

        if (!$patient) {
            $this->lastError = 'Booking found but tests could not be matched to the web pathology catalog.';
        }

        return $patient;
    }

    /**
     * @return array{order: ?LabOrder, inserted: int, updated: int}
     */
    private function upsertBookingGroup(string $bookingId, Collection $lines, array $mapping): array
    {
        $first = $lines->first();
        $inserted = 0;
        $updated = 0;

        $subTotal = $lines->sum(fn ($row) => (float) ($row->{$mapping['test_price']} ?? 0));
        $isCancelled = $lines->every(fn ($row) => (bool) ($row->{$mapping['is_cancelled']} ?? false));

        $orderPayload = [
            'desktop_booking_id' => (int) $bookingId,
            'lab_registration_no' => (string) $bookingId,
            'mr_no' => $this->normalizeMrNo($first->{$mapping['mr_no']} ?? null),
            'patient_name' => trim((string) ($first->{$mapping['patient_name']} ?? 'Unknown Patient')),
            'gender' => $this->normalizeGender($first->{$mapping['gender']} ?? 'Other'),
            'contact_no' => $first->{$mapping['mobile']} ?? null,
            'age' => $this->normalizeAge($first->{$mapping['age']} ?? $first->{$mapping['system_age']} ?? 0),
            'file_no' => isset($first->{$mapping['file_no']}) ? (string) $first->{$mapping['file_no']} : null,
            'refer_by_doctor_name' => $first->{$mapping['doctor']} ?? null,
            'self_referred' => empty($first->{$mapping['doctor']}),
            'booking_date' => $first->{$mapping['booking_date']} ?? null,
            'priority' => $first->{$mapping['priority']} ?? 'Routine',
            'sub_total' => $subTotal,
            'discount' => (float) ($first->{$mapping['discount']} ?? 0),
            'grand_total' => (float) ($first->{$mapping['total']} ?? $subTotal),
            'paid_amount' => (float) ($first->{$mapping['paid']} ?? $first->{$mapping['total']} ?? $subTotal),
            'due_amount' => (float) ($first->{$mapping['due']} ?? 0),
            'lab_share_total' => (float) ($first->{$mapping['lab_share']} ?? 0),
            'hospital_share_total' => (float) ($first->{$mapping['hosp_share']} ?? 0),
            'status' => $isCancelled ? 'Cancelled' : 'Pending',
            'synced_at' => now(),
        ];

        $orderHash = DesktopSyncHash::make($orderPayload);
        $orderPayload['source_hash'] = $orderHash;
        $orderPayload['source_updated_at'] = now();

        $order = LabOrder::query()->where('desktop_booking_id', (int) $bookingId)->first();

        if ($order) {
            if ($order->source_hash !== $orderHash) {
                $order->update($orderPayload);
                $updated++;
            }
        } else {
            $order = LabOrder::create($orderPayload);
            $inserted++;
        }

        foreach ($lines as $row) {
            $this->upsertOrderLine($order, $row, $mapping);
        }

        return ['order' => $order->fresh(['orderTests']), 'inserted' => $inserted, 'updated' => $updated];
    }

    private function upsertOrderLine(LabOrder $order, object $row, array $mapping): void
    {
        $lineId = (int) ($row->{$mapping['line_id']} ?? 0);

        if ($lineId <= 0) {
            return;
        }

        $desktopTestId = $row->{$mapping['test_id']} ?? null;
        $webTest = $desktopTestId ? $this->bookingMapper->resolveWebTest($row) : null;

        if (!$webTest && $desktopTestId) {
            $webTest = Test::query()
                ->where('desktop_test_id', (int) $desktopTestId)
                ->orWhere('id', (int) $desktopTestId)
                ->first();
        }

        $testName = trim((string) ($row->{$mapping['test_name']} ?? ''));
        if ($testName === '' && $desktopTestId) {
            $testName = (string) ($this->bookingMapper->resolveDesktopTestName($row) ?? 'Unknown Test');
        }

        $price = (float) ($row->{$mapping['test_price']} ?? ($webTest->price ?? 0));
        $isCancelled = (bool) ($row->{$mapping['is_cancelled']} ?? false);

        $existing = LabOrderTest::query()->where('desktop_line_id', $lineId)->first();

        if ($existing) {
            // Never overwrite booking-time price/name snapshots.
            $existing->update([
                'lab_order_id' => $order->id,
                'desktop_test_id' => $desktopTestId ? (int) $desktopTestId : null,
                'test_id' => $webTest?->id,
                'status' => $isCancelled ? 'Cancelled' : ($existing->status ?: 'Pending'),
                'is_cancelled' => $isCancelled,
            ]);

            return;
        }

        $linePayload = [
            'lab_order_id' => $order->id,
            'desktop_line_id' => $lineId,
            'desktop_test_id' => $desktopTestId ? (int) $desktopTestId : null,
            'test_id' => $webTest?->id,
            'test_name_snapshot' => $testName,
            'price_snapshot' => $price,
            'status' => $isCancelled ? 'Cancelled' : 'Pending',
            'is_cancelled' => $isCancelled,
        ];

        $linePayload['source_hash'] = DesktopSyncHash::make($linePayload);
        LabOrderTest::create($linePayload);
    }

    private function normalizeMrNo(mixed $mrNo): ?string
    {
        $mrNo = trim((string) $mrNo);

        if ($mrNo === '') {
            return null;
        }

        return is_numeric($mrNo) ? (string) (int) $mrNo : $mrNo;
    }

    private function normalizeGender(?string $gender): string
    {
        $gender = strtolower(trim((string) $gender));

        return match (true) {
            str_contains($gender, 'f') => 'Female',
            str_contains($gender, 'm') => 'Male',
            default => 'Other',
        };
    }

    private function normalizeAge(mixed $age): int
    {
        if (is_numeric($age)) {
            return max(0, (int) $age);
        }

        if (preg_match('/(\d+)/', (string) $age, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
