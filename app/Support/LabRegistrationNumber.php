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

        return $query->where(function (Builder $inner) use ($column, $normalized, $value) {
            $inner->where($column, $normalized)
                ->orWhere($column, $value);

            if (is_numeric($normalized)) {
                $inner->orWhere($column, (string) (int) $normalized);
            }
        });
    }
}
