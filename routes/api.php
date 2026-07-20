<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This standalone pathology lab app exposes its lab-facing AJAX endpoints
| under the /laboratory prefix in routes/web.php (e.g. api.tests_by_head).
| No public /api routes are currently required.
|
*/

use App\Http\Controllers\Api\CloudSyncController;

Route::post('/sync/push', [CloudSyncController::class, 'receiveSyncPayload']);
