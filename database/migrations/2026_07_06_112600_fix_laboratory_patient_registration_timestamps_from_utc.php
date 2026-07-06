<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * laboratory_patients.created_at (Registered At) was stored in UTC before
 * APP_TIMEZONE was set to Asia/Karachi.
 */
return new class extends Migration
{
    private const OFFSET_HOURS = 5;

    /** Same cutoff as lab workflow timestamp fix — do not shift newer records. */
    private const CUTOFF = '2026-07-06 10:43:00';

    public function up(): void
    {
        $cutoff = Carbon::parse(self::CUTOFF, 'Asia/Karachi');

        $this->shiftRegistrationTimestamps($cutoff, self::OFFSET_HOURS);
    }

    public function down(): void
    {
        $cutoff = Carbon::parse(self::CUTOFF, 'Asia/Karachi');

        $this->shiftRegistrationTimestamps($cutoff, -self::OFFSET_HOURS, onlyShifted: true);
    }

    private function shiftRegistrationTimestamps(Carbon $cutoff, int $hours, bool $onlyShifted = false): void
    {
        DB::table('laboratory_patients')
            ->orderBy('id')
            ->chunkById(100, function ($patients) use ($cutoff, $hours, $onlyShifted) {
                foreach ($patients as $patient) {
                    if (empty($patient->created_at)) {
                        continue;
                    }

                    $dt = Carbon::parse($patient->created_at, 'Asia/Karachi');

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

                    DB::table('laboratory_patients')
                        ->where('id', $patient->id)
                        ->update([
                            'created_at' => $dt->copy()->addHours($hours)->toDateTimeString(),
                        ]);
                }
            });
    }
};
