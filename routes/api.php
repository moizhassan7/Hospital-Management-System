<?php

use App\Http\Controllers\Api\CloudSyncController;
use App\Http\Controllers\Api\V1\SampleBatchController;
use App\Http\Middleware\EnsureApiAuthenticated;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Cloud sync remains token-authenticated. LIMS /api/v1 sample-batch routes use
| session auth (Sanctum not installed) — call from the same browser session
| as the web app, or Auth::login in smoke/tinker.
|
*/

Route::post('/sync/push', [CloudSyncController::class, 'receiveSyncPayload']);

Route::prefix('v1')
    ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        EnsureApiAuthenticated::class,
    ])
    ->group(function () {
        Route::get('/sample-batches', [SampleBatchController::class, 'index']);
        Route::post('/sample-batches', [SampleBatchController::class, 'store']);
        Route::get('/sample-batches/{batch}', [SampleBatchController::class, 'show']);
        Route::post('/sample-batches/{batch}/items', [SampleBatchController::class, 'addItem']);
        Route::delete('/sample-batches/{batch}/items/{sampleId}', [SampleBatchController::class, 'removeItem']);
        Route::post('/sample-batches/{batch}/dispatch', [SampleBatchController::class, 'dispatchBatch']);
        Route::post('/sample-batches/{batch}/in-transit', [SampleBatchController::class, 'inTransit']);
        Route::post('/sample-batches/{batch}/receive', [SampleBatchController::class, 'receive']);
        Route::get('/sample-batches/{batch}/events', [SampleBatchController::class, 'events']);
    });
