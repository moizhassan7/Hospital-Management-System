<?php

namespace App\Support;

class DesktopSyncHash
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function make(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    public static function parseNumericRange(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return ['min' => null, 'max' => null, 'text' => null];
        }

        if (preg_match('/(-?\d+(?:\.\d+)?)\s*[-–—to]+\s*(-?\d+(?:\.\d+)?)/i', $value, $matches)) {
            return [
                'min' => (float) $matches[1],
                'max' => (float) $matches[2],
                'text' => $value,
            ];
        }

        if (preg_match('/^[<>]=?\s*(-?\d+(?:\.\d+)?)/', $value, $matches)) {
            return ['min' => null, 'max' => null, 'text' => $value];
        }

        return ['min' => null, 'max' => null, 'text' => $value];
    }
}
