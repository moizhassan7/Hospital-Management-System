<?php

namespace App\Support;

/**
 * Decode a JSON array that may have been stored as a nested JSON string
 * (the array-cast double-encoding bug).
 */
class JsonList
{
    public static function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
