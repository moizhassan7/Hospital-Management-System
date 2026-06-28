<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pathology:sync-desktop')
    ->everyThreeMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Legacy alias — kept for manual runs / backward compatibility
Schedule::command('pathology:sync-desktop-tests')->everyFifteenMinutes();
