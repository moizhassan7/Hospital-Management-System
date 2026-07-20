<?php

namespace App\Services\Lims;

use App\Models\CollectionCenter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Concurrency-safe lab number allocation via INSERT … ON CONFLICT / UPDATE … RETURNING.
 * Format: {prefix}-{YYYYMM}-{seq:04d} e.g. CC1-202607-0001 (Asia/Karachi year_month).
 * Never use MAX(seq)+1.
 */
class LabNumberAllocator
{
    public const TIMEZONE = 'Asia/Karachi';

    /**
     * Allocate the next lab number for a collection center.
     *
     * @return array{lab_number: string, year_month: string, seq: int, prefix: string}
     */
    public function allocate(int $collectionCenterId, ?Carbon $at = null): array
    {
        $center = CollectionCenter::query()->find($collectionCenterId);

        if ($center === null) {
            throw new RuntimeException(
                "Collection center {$collectionCenterId} not found for lab number allocation."
            );
        }

        $prefix = trim((string) $center->lab_number_prefix);

        if ($prefix === '') {
            throw new RuntimeException(
                "Collection center {$collectionCenterId} has an empty lab_number_prefix."
            );
        }

        $at = ($at ?? now())->copy()->timezone(self::TIMEZONE);
        $yearMonth = $at->format('Ym');

        return DB::transaction(function () use ($collectionCenterId, $prefix, $yearMonth) {
            $row = DB::selectOne(
                'INSERT INTO lab_number_sequences (collection_center_id, year_month, next_seq)
                 VALUES (?, ?, 2)
                 ON CONFLICT (collection_center_id, year_month)
                 DO UPDATE SET
                    next_seq = lab_number_sequences.next_seq + 1
                 RETURNING (next_seq - 1) AS seq',
                [$collectionCenterId, $yearMonth]
            );

            if ($row === null || $row->seq === null) {
                throw new RuntimeException(
                    "Failed to allocate lab number for collection center {$collectionCenterId}."
                );
            }

            $seq = (int) $row->seq;
            $labNumber = sprintf('%s-%s-%04d', $prefix, $yearMonth, $seq);

            return [
                'lab_number' => $labNumber,
                'year_month' => $yearMonth,
                'seq' => $seq,
                'prefix' => $prefix,
            ];
        });
    }

    /**
     * Ensure a sequence row exists (e.g. when seeding a new month or center).
     */
    public function ensureSequence(int $collectionCenterId, string $yearMonth, int $nextSeq = 1): void
    {
        DB::table('lab_number_sequences')->insertOrIgnore([
            'collection_center_id' => $collectionCenterId,
            'year_month' => $yearMonth,
            'next_seq' => $nextSeq,
        ]);
    }
}
