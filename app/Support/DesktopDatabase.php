<?php

namespace App\Support;

class DesktopDatabase
{
    public static function isEnabled(): bool
    {
        if (!extension_loaded('pdo_sqlsrv')) {
            return false;
        }

        $connection = config('database.connections.desktop', []);

        return filter_var(config('desktop_sync.enabled', true), FILTER_VALIDATE_BOOLEAN)
            && !empty($connection['host'])
            && !empty($connection['database'])
            && !empty($connection['username']);
    }

    public static function getDisabledReason(): ?string
    {
        if (!extension_loaded('pdo_sqlsrv')) {
            return 'PHP SQL Server driver (pdo_sqlsrv) is not installed on this machine.';
        }

        if (!filter_var(config('desktop_sync.enabled', true), FILTER_VALIDATE_BOOLEAN)) {
            return 'Desktop sync is disabled (DESKTOP_SYNC_ENABLED / DESKTOP_DB_ENABLED).';
        }

        $connection = config('database.connections.desktop', []);

        if (empty($connection['host']) || empty($connection['database']) || empty($connection['username'])) {
            return 'Desktop SQL Server credentials are not configured.';
        }

        return null;
    }
}
