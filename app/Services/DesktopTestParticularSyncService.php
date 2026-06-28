<?php

namespace App\Services;

use App\Models\Desktop\DesktopTestParticular;
use App\Models\SyncCursor;
use App\Models\Test;
use App\Models\TestParticular;
use App\Support\DesktopDatabase;
use App\Support\DesktopSyncHash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DesktopTestParticularSyncService
{
    public function __construct(
        private SyncLogService $syncLogService
    ) {}

    private ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return array{processed: int, inserted: int, updated: int, deactivated: int}
     */
    public function syncAll(): array
    {
        if (!DesktopDatabase::isEnabled()) {
            $this->lastError = DesktopDatabase::getDisabledReason();

            return ['processed' => 0, 'inserted' => 0, 'updated' => 0, 'deactivated' => 0];
        }

        $log = $this->syncLogService->start('test_particulars');

        try {
            $mapping = config('desktop_sync.sources.test_particulars.columns');
            $rows = DesktopTestParticular::query()->orderBy('id')->get();

            $inserted = 0;
            $updated = 0;
            $seenDesktopIds = [];

            $deactivated = 0;

            DB::transaction(function () use ($rows, $mapping, &$inserted, &$updated, &$deactivated, &$seenDesktopIds) {
                foreach ($rows as $row) {
                    $attrs = (array) $row->getAttributes();
                    $desktopId = (int) ($attrs[$mapping['id']] ?? 0);
                    $desktopTestId = (int) ($attrs[$mapping['test_id']] ?? 0);

                    if ($desktopId <= 0 || $desktopTestId <= 0) {
                        continue;
                    }

                    $seenDesktopIds[] = $desktopId;

                    $webTest = Test::query()
                        ->where('desktop_test_id', $desktopTestId)
                        ->orWhere('id', $desktopTestId)
                        ->first();

                    if (!$webTest) {
                        Log::warning('Desktop particular skipped — parent test not synced yet', [
                            'desktop_particular_id' => $desktopId,
                            'desktop_test_id' => $desktopTestId,
                        ]);
                        continue;
                    }

                    $maleRange = DesktopSyncHash::parseNumericRange($attrs[$mapping['male_range']] ?? null);
                    $femaleRange = DesktopSyncHash::parseNumericRange($attrs[$mapping['female_range']] ?? null);
                    $childRange = DesktopSyncHash::parseNumericRange($attrs[$mapping['child_range']] ?? null);

                    $referenceText = collect([
                        $maleRange['text'] ? 'Male: ' . $maleRange['text'] : null,
                        $femaleRange['text'] ? 'Female: ' . $femaleRange['text'] : null,
                        $childRange['text'] ? 'Child: ' . $childRange['text'] : null,
                    ])->filter()->implode(' | ');

                    $payload = [
                        'desktop_particular_id' => $desktopId,
                        'test_id' => $webTest->id,
                        'name' => trim((string) ($attrs[$mapping['name']] ?? '')),
                        'unit' => trim((string) ($attrs[$mapping['unit']] ?? '')) ?: null,
                        'normal_range_min' => $maleRange['min'] ?? $femaleRange['min'],
                        'normal_range_max' => $maleRange['max'] ?? $femaleRange['max'],
                        'reference_text' => $referenceText ?: null,
                        'is_active' => true,
                        'sort_order' => $desktopId,
                    ];

                    $hash = DesktopSyncHash::make($payload);
                    $payload['source_hash'] = $hash;
                    $payload['source_updated_at'] = now();

                    $existing = TestParticular::query()
                        ->where('desktop_particular_id', $desktopId)
                        ->first();

                    if ($existing) {
                        if ($existing->source_hash !== $hash) {
                            $existing->update($payload);
                            $updated++;
                        }
                    } else {
                        TestParticular::create($payload);
                        $inserted++;
                    }
                }

                if ($seenDesktopIds !== []) {
                    $deactivated = TestParticular::query()
                        ->whereNotNull('desktop_particular_id')
                        ->whereNotIn('desktop_particular_id', $seenDesktopIds)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);
                }
            });

            SyncCursor::setValue('test_particulars', (string) $rows->max('id'));

            $stats = [
                'processed' => $rows->count(),
                'inserted' => $inserted,
                'updated' => $updated,
                'deactivated' => $deactivated,
            ];

            $this->syncLogService->finishSuccess($log, $stats);

            return $stats;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            $this->syncLogService->finishFailure($log, $e->getMessage());

            throw $e;
        }
    }
}
