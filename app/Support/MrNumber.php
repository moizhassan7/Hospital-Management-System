<?php

namespace App\Support;

class MrNumber
{
    public static function normalize(string $mrNo): string
    {
        $mrNo = trim($mrNo);

        if ($mrNo === '') {
            return '';
        }

        if (is_numeric($mrNo)) {
            return (string) (int) $mrNo;
        }

        return $mrNo;
    }

    public static function applyToQuery($query, string $column, string $mrNo)
    {
        $normalized = self::normalize($mrNo);

        return $query->where(function ($q) use ($column, $mrNo, $normalized) {
            $q->where($column, $mrNo);

            if ($normalized !== $mrNo) {
                $q->orWhere($column, $normalized);
            }
        });
    }
}
