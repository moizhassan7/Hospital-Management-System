<?php

namespace App\Services;

use App\Models\Desktop\DesktopTest;
use App\Models\Test;
use App\Models\TestHead;
use App\Support\DesktopSyncHash;
use Database\Seeders\PathologyVialMapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DesktopTestCatalogSyncService
{
    public function __construct(
        private ?SyncLogService $syncLogService = null
    ) {}
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private ?string $lastError = null;

    public function isDesktopEnabled(): bool
    {
        return \App\Support\DesktopDatabase::isEnabled();
    }

    /**
     * @return array{created: int, updated: int, total: int, source: string}
     */
    public function syncAll(bool $preferDesktop = true): array
    {
        $rows = [];

        if ($preferDesktop && $this->isDesktopEnabled()) {
            try {
                $rows = $this->fetchFromDesktopDatabase();
                $source = 'desktop_database';
            } catch (\Throwable $e) {
                $this->lastError = $e->getMessage();
                Log::warning('Desktop test catalog DB read failed, falling back to file: ' . $e->getMessage());
                $rows = $this->fetchFromDataFile();
                $source = 'data_file_fallback';
            }
        } else {
            $rows = $this->fetchFromDataFile();
            $source = 'data_file';
        }

        if (empty($rows)) {
            return ['created' => 0, 'updated' => 0, 'total' => 0, 'source' => $source];
        }

        $result = $this->upsertRows($rows);
        $result['source'] = $source;

        Cache::put('desktop_tests_last_sync', now()->toIso8601String(), now()->addDay());

        if ($this->syncLogService && $preferDesktop && $source === 'desktop_database') {
            $log = $this->syncLogService->start('tests');
            $this->syncLogService->finishSuccess($log, [
                'processed' => $result['total'],
                'inserted' => $result['created'],
                'updated' => $result['updated'],
                'deactivated' => $result['deactivated'] ?? 0,
            ], ['source' => $source]);
        }

        return $result;
    }

    /**
     * Throttled sync for booking/search flows.
     */
    public function syncIfStale(int $minutes = 15): array
    {
        $key = 'desktop_tests_catalog_sync';

        return Cache::remember($key, now()->addMinutes($minutes), function () {
            return $this->syncAll(true);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchFromDesktopDatabase(): array
    {
        return DesktopTest::query()
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => $this->normalizeRow((array) $row->getAttributes()))
            ->filter(fn ($row) => !empty($row['id']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchFromDataFile(): array
    {
        $path = base_path('data_from_desktop_test.txt');

        if (!file_exists($path)) {
            $this->lastError = 'Desktop test data file not found: data_from_desktop_test.txt';

            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows = [];

        foreach ($lines as $index => $line) {
            if ($index === 0 && str_starts_with(strtolower($line), 'id')) {
                continue;
            }

            $parts = preg_split("/\t+/", trim($line));
            if (count($parts) < 6) {
                continue;
            }

            $rows[] = $this->normalizeRow([
                'id' => $parts[0],
                'name' => $parts[1],
                'price' => $parts[2],
                'type' => $parts[3],
                'carry_out' => $parts[4],
                'report' => $parts[5],
            ]);
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{created: int, updated: int, total: int, deactivated: int}
     */
    public function upsertRows(array $rows): array
    {
        $created = 0;
        $updated = 0;
        $deactivated = 0;
        $headCache = [];
        $seenDesktopIds = [];

        DB::transaction(function () use ($rows, &$created, &$updated, &$deactivated, &$headCache, &$seenDesktopIds) {
            foreach ($rows as $row) {
                $desktopId = (int) $row['id'];
                $seenDesktopIds[] = $desktopId;
                $headName = $this->normalizeHeadName((string) $row['carry_out']);

                if (!isset($headCache[$headName])) {
                    $headCache[$headName] = TestHead::firstOrCreate(
                        ['name' => $headName, 'category' => 'Pathology'],
                        ['category' => 'Pathology']
                    )->id;
                }

                $vialInfo = PathologyVialMapper::resolve((string) $row['name'], $headName);

                $attributes = [
                    'test_id' => (string) $desktopId,
                    'desktop_test_id' => $desktopId,
                    'name' => (string) $row['name'],
                    'price' => (float) $row['price'],
                    'type' => (string) ($row['type'] ?: 'Routine'),
                    'test_head_id' => $headCache[$headName],
                    'category' => 'Pathology',
                    'priority' => $this->mapPriority((string) $row['type']),
                    'report_time' => $this->parseReportTime((string) $row['report']),
                    'report_format' => 'Quantitative',
                    'sample_vial' => $vialInfo['sample_vial'],
                    'sample_expiry_hours' => $vialInfo['sample_expiry_hours'],
                    'vials_required' => $vialInfo['vials_required'],
                    'is_active' => true,
                ];

                $hash = DesktopSyncHash::make($attributes);
                $attributes['source_hash'] = $hash;
                $attributes['source_updated_at'] = now();

                $existing = Test::find($desktopId);

                if ($existing) {
                    if ($existing->source_hash !== $hash) {
                        $existing->update($attributes);
                        $updated++;
                    }
                } else {
                    $test = new Test($attributes);
                    $test->id = $desktopId;
                    $test->save();
                    $created++;
                }
            }

            if ($seenDesktopIds !== []) {
                $deactivated = Test::query()
                    ->where('category', 'Pathology')
                    ->whereNotNull('desktop_test_id')
                    ->whereNotIn('desktop_test_id', $seenDesktopIds)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('tests', 'id'), COALESCE((SELECT MAX(id) FROM tests), 1), true)");
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($rows),
            'deactivated' => $deactivated,
        ];
    }

    private function normalizeRow(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => trim((string) ($row['name'] ?? '')),
            'price' => (float) ($row['price'] ?? 0),
            'type' => trim((string) ($row['type'] ?? 'Routine')),
            'carry_out' => trim((string) ($row['carry_out'] ?? 'General')),
            'report' => trim((string) ($row['report'] ?? 'Same Day')),
        ];
    }

    private function normalizeHeadName(string $head): string
    {
        $head = trim($head);

        if ($head === '' || strcasecmp($head, 'None') === 0) {
            return 'General Pathology';
        }

        return $head;
    }

    private function mapPriority(string $type): string
    {
        return match (strtolower($type)) {
            'special', 'special01', 'special02', 'pcr' => 'Urgent',
            default => 'Routine',
        };
    }

    public function parseReportTime(string $str): int
    {
        $str = strtolower(trim($str));

        if ($str === '' || $str === 'null') {
            return 24;
        }

        if (str_contains($str, 'same day') || $str === '1' || str_contains($str, 'sameday')) {
            return 24;
        }

        if (str_contains($str, 'next day') || str_contains($str, 'nextday')) {
            return 48;
        }

        if (preg_match('/(\d+)\s*days?/', $str, $matches)) {
            return (int) $matches[1] * 24;
        }

        if (preg_match('/(\d+)\s*weeks?/', $str, $matches)) {
            return (int) $matches[1] * 7 * 24;
        }

        if (preg_match('/(\d+)\s*hrs?/', $str, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)\s*hours?/', $str, $matches)) {
            return (int) $matches[1];
        }

        if (ctype_digit($str)) {
            return max(1, (int) $str) * 24;
        }

        return 24;
    }
}
