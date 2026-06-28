<?php

namespace App\Support;

class DesktopDatabase
{
    public static function isEnabled(): bool
    {
        if (!extension_loaded('pdo_sqlsrv')) {
            return false;
        }

        return filter_var(config('desktop_sync.enabled', true), FILTER_VALIDATE_BOOLEAN)
            && env('DESKTOP_DB_HOST')
            && env('DESKTOP_DB_DATABASE')
            && env('DESKTOP_DB_USERNAME');
    }

    public static function getDisabledReason(): ?string
    {
        if (!extension_loaded('pdo_sqlsrv')) {
            return 'PHP SQL Server driver (pdo_sqlsrv) is not installed on this machine.';
        }

        if (!filter_var(config('desktop_sync.enabled', true), FILTER_VALIDATE_BOOLEAN)) {
            return 'Desktop sync is disabled (DESKTOP_SYNC_ENABLED / DESKTOP_DB_ENABLED).';
        }

        if (!env('DESKTOP_DB_HOST') || !env('DESKTOP_DB_DATABASE') || !env('DESKTOP_DB_USERNAME')) {
            return 'Desktop SQL Server credentials are not configured.';
        }

        return null;
    }
}
