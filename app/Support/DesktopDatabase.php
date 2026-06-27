<?php

namespace App\Support;

class DesktopDatabase
{
    public static function isEnabled(): bool
    {
        return extension_loaded('pdo_sqlsrv')
            && filter_var(env('DESKTOP_DB_ENABLED', true), FILTER_VALIDATE_BOOLEAN)
            && env('DESKTOP_DB_HOST')
            && env('DESKTOP_DB_DATABASE')
            && env('DESKTOP_DB_USERNAME');
    }
}
