<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Test/particular catalog is seeded in web DB — no recurring sync needed.
// Booking lookup still reads live from SQL Server on Sample Portal search.
// Run manually if needed: php artisan pathology:sync-desktop
// Schedule::command('pathology:sync-desktop')
//     ->everyThreeMinutes()
//     ->withoutOverlapping()
//     ->runInBackground();
//
// Schedule::command('pathology:sync-desktop-tests')->everyFifteenMinutes();

use App\Jobs\SyncDataToCloudJob;

if (config('sync.role') === 'local') {
    Schedule::job(new SyncDataToCloudJob())
        ->everyMinute()
        ->withoutOverlapping();
}
