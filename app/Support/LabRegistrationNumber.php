<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class LabRegistrationNumber
{
    public static function normalize(string $value): string
    {
        return trim($value);
    }

    public static function applyToQuery(Builder $query, string $column, string $value): Builder
    {
        $normalized = self::normalize($value);

        $normalizedLower = strtolower($normalized);
        $valueLower = strtolower($value);

        return $query->where(function (Builder $inner) use ($column, $normalizedLower, $valueLower, $normalized) {
            $inner->whereRaw("LOWER({$column}) LIKE ?", ["%{$normalizedLower}%"])
                ->orWhereRaw("LOWER({$column}) LIKE ?", ["%{$valueLower}%"]);

            if (is_numeric($normalized)) {
                $numLower = strtolower((string) (int) $normalized);
                $inner->orWhereRaw("LOWER({$column}) LIKE ?", ["%{$numLower}%"]);
            }
        });
    }
}
