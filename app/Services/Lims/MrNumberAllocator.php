<?php

namespace App\Services\Lims;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Concurrency-safe MR allocation via UPDATE … RETURNING (never MAX+1).
 */
class MrNumberAllocator
{
    /**
     * Allocate the next MR number for an organization and return it as a string.
     *
     * Uses INSERT … ON CONFLICT so the first allocation can create the sequence
     * row; subsequent calls row-lock via the upsert update path.
     */
    public function allocate(int $organizationId): string
    {
        return DB::transaction(function () use ($organizationId) {
            $row = DB::selectOne(
                'INSERT INTO mr_number_sequences (organization_id, next_value, updated_at)
                 VALUES (?, 2, NOW())
                 ON CONFLICT (organization_id)
                 DO UPDATE SET
                    next_value = mr_number_sequences.next_value + 1,
                    updated_at = NOW()
                 RETURNING (next_value - 1) AS mr_no',
                [$organizationId]
            );

            if ($row === null || $row->mr_no === null) {
                throw new RuntimeException(
                    "Failed to allocate MR number for organization {$organizationId}."
                );
            }

            return (string) $row->mr_no;
        });
    }

    /**
     * Ensure a sequence row exists (e.g. when seeding an organization).
     */
    public function ensureSequence(int $organizationId, int $nextValue = 1): void
    {
        DB::table('mr_number_sequences')->insertOrIgnore([
            'organization_id' => $organizationId,
            'next_value' => $nextValue,
            'updated_at' => now(),
        ]);
    }
}
