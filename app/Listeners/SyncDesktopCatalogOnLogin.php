<?php

namespace App\Listeners;

use App\Services\DesktopSyncOrchestrator;
use App\Support\DesktopDatabase;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class SyncDesktopCatalogOnLogin
{
    /**
     * After a successful login, sync tests & particulars from SQL Server in the background.
     * Login response is not delayed — sync runs after the redirect is sent.
     */
    public function handle(Login $event): void
    {
        if (!filter_var(config('desktop_sync.sync_on_login', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        if (!DesktopDatabase::isEnabled()) {
            return;
        }

        dispatch(function () {
            try {
                app(DesktopSyncOrchestrator::class)->syncAll();
            } catch (\Throwable $e) {
                Log::warning('Desktop catalog sync on login failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }
}
