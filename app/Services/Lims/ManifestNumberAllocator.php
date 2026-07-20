<?php

namespace App\Services\Lims;

use App\Models\CollectionCenter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Concurrency-safe manifest number allocation via INSERT … ON CONFLICT / UPDATE … RETURNING.
 * Format: {prefix}-M-{YYYYMMDD}-{seq:04d} e.g. CC1-M-20260721-0001 (Asia/Karachi date).
 * Never use MAX(seq)+1.
 */
class ManifestNumberAllocator
{
    public const TIMEZONE = 'Asia/Karachi';

    /**
     * @return array{manifest_no: string, business_date: string, seq: int, prefix: string}
     */
    public function allocate(int $collectionCenterId, ?Carbon $at = null): array
    {
        $center = CollectionCenter::query()->find($collectionCenterId);

        if ($center === null) {
            throw new RuntimeException(
                "Collection center {$collectionCenterId} not found for manifest allocation."
            );
        }

        $prefix = trim((string) $center->lab_number_prefix);

        if ($prefix === '') {
            throw new RuntimeException(
                "Collection center {$collectionCenterId} has an empty lab_number_prefix."
            );
        }

        $at = ($at ?? now())->copy()->timezone(self::TIMEZONE);
        $businessDate = $at->format('Ymd');

        return DB::transaction(function () use ($collectionCenterId, $prefix, $businessDate) {
            $row = DB::selectOne(
                'INSERT INTO lims_manifest_sequences (collection_center_id, business_date, next_seq)
                 VALUES (?, ?, 2)
                 ON CONFLICT (collection_center_id, business_date)
                 DO UPDATE SET
                    next_seq = lims_manifest_sequences.next_seq + 1
                 RETURNING (next_seq - 1) AS seq',
                [$collectionCenterId, $businessDate]
            );

            if ($row === null || $row->seq === null) {
                throw new RuntimeException(
                    "Failed to allocate manifest number for collection center {$collectionCenterId}."
                );
            }

            $seq = (int) $row->seq;
            $manifestNo = sprintf('%s-M-%s-%04d', $prefix, $businessDate, $seq);

            return [
                'manifest_no' => $manifestNo,
                'business_date' => $businessDate,
                'seq' => $seq,
                'prefix' => $prefix,
            ];
        });
    }

    public function ensureSequence(int $collectionCenterId, string $businessDate, int $nextSeq = 1): void
    {
        DB::table('lims_manifest_sequences')->insertOrIgnore([
            'collection_center_id' => $collectionCenterId,
            'business_date' => $businessDate,
            'next_seq' => $nextSeq,
        ]);
    }
}
