<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lab workflow timestamps were stored in UTC while the hospital operates in
 * Asia/Karachi (UTC+5). Shift existing values recorded before the timezone fix.
 */
return new class extends Migration
{
    private const OFFSET_HOURS = 5;

    /** When APP_TIMEZONE was switched to Asia/Karachi — do not shift newer records. */
    private const CUTOFF = '2026-07-06 10:43:00';

    private const VIAL_COLUMNS = [
        'collected_at',
        'received_in_lab_at',
        'reported_at',
        'expires_at',
    ];

    private const TEST_TIMESTAMP_KEYS = [
        'sample_collected_at',
        'sample_received_in_lab_at',
        'result_completed_at',
        'result_reported_at',
    ];

    public function up(): void
    {
        $cutoff = Carbon::parse(self::CUTOFF, 'Asia/Karachi');

        $this->shiftVialTimestamps($cutoff, self::OFFSET_HOURS);
        $this->shiftSelectedTestsTimestamps($cutoff, self::OFFSET_HOURS);
    }

    public function down(): void
    {
        $cutoff = Carbon::parse(self::CUTOFF, 'Asia/Karachi');

        $this->shiftVialTimestamps($cutoff, -self::OFFSET_HOURS, onlyShifted: true);
        $this->shiftSelectedTestsTimestamps($cutoff, -self::OFFSET_HOURS, onlyShifted: true);
    }

    private function shiftVialTimestamps(Carbon $cutoff, int $hours, bool $onlyShifted = false): void
    {
        DB::table('lab_sample_vials')
            ->orderBy('id')
            ->chunkById(100, function ($vials) use ($cutoff, $hours, $onlyShifted) {
                foreach ($vials as $vial) {
                    $updates = [];

                    foreach (self::VIAL_COLUMNS as $column) {
                        if (empty($vial->{$column})) {
                            continue;
                        }

                        $dt = Carbon::parse($vial->{$column}, 'Asia/Karachi');

                        if ($onlyShifted) {
                            $shiftedLower = $cutoff->copy()->subHours(abs($hours));
                            if ($dt->lt($shiftedLower) || $dt->gte($cutoff)) {
                                continue;
                            }
                        } elseif ($hours >= 0 && ! $dt->lt($cutoff)) {
                            continue;
                        } elseif ($hours < 0 && ! $dt->lt($cutoff)) {
                            continue;
                        }

                        $updates[$column] = $dt->copy()->addHours($hours)->toDateTimeString();
                    }

                    if ($updates !== []) {
                        DB::table('lab_sample_vials')
                            ->where('id', $vial->id)
                            ->update($updates);
                    }
                }
            });
    }

    private function shiftSelectedTestsTimestamps(Carbon $cutoff, int $hours, bool $onlyShifted = false): void
    {
        DB::table('laboratory_patients')
            ->whereNotNull('selected_tests')
            ->orderBy('id')
            ->chunkById(100, function ($patients) use ($cutoff, $hours, $onlyShifted) {
                foreach ($patients as $patient) {
                    $tests = json_decode($patient->selected_tests, true);

                    if (! is_array($tests)) {
                        continue;
                    }

                    $changed = false;

                    foreach ($tests as &$test) {
                        if (! is_array($test)) {
                            continue;
                        }

                        foreach (self::TEST_TIMESTAMP_KEYS as $key) {
                            if (empty($test[$key])) {
                                continue;
                            }

                            try {
                                $dt = Carbon::parse($test[$key], 'Asia/Karachi');
                            } catch (\Throwable) {
                                continue;
                            }

                            if ($onlyShifted) {
                                $shiftedLower = $cutoff->copy()->subHours(abs($hours));
                                if ($dt->lt($shiftedLower) || $dt->gte($cutoff)) {
                                    continue;
                                }
                            } elseif ($hours >= 0 && ! $dt->lt($cutoff)) {
                                continue;
                            } elseif ($hours < 0 && ! $dt->lt($cutoff)) {
                                continue;
                            }

                            $test[$key] = $dt->copy()->addHours($hours)->toDateTimeString();
                            $changed = true;
                        }
                    }
                    unset($test);

                    if ($changed) {
                        DB::table('laboratory_patients')
                            ->where('id', $patient->id)
                            ->update(['selected_tests' => json_encode($tests)]);
                    }
                }
            });
    }
};
