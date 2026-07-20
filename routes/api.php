<?php

use App\Http\Controllers\Api\CloudSyncController;
use App\Http\Controllers\Api\V1\BookingCommissionController;
use App\Http\Controllers\Api\V1\CommissionRuleController;
use App\Http\Controllers\Api\V1\CommissionSnapshotController;
use App\Http\Controllers\Api\V1\DoctorLedgerController;
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
| Cloud sync remains token-authenticated. LIMS /api/v1 routes use session auth
| (Sanctum not installed) — call from the same browser session as the web app,
| or Auth::login in smoke/tinker.
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

        // Phase 3 — commissions (architecture §4.2)
        Route::get('/commission-rules', [CommissionRuleController::class, 'index']);
        Route::post('/commission-rules', [CommissionRuleController::class, 'store']);
        Route::put('/commission-rules/{commissionRule}', [CommissionRuleController::class, 'update']);
        Route::post('/commission-rules/{commissionRule}/deactivate', [CommissionRuleController::class, 'deactivate']);

        Route::get('/commission-snapshots', [CommissionSnapshotController::class, 'index']);

        Route::post('/bookings/{booking}/finalize-invoice', [BookingCommissionController::class, 'finalizeInvoice']);
        Route::post('/bookings/{booking}/cancel', [BookingCommissionController::class, 'cancel']);

        Route::get('/doctors/{doctor}/ledger', [DoctorLedgerController::class, 'ledger']);
        Route::get('/doctors/{doctor}/payouts', [DoctorLedgerController::class, 'payouts']);
        Route::post('/doctors/{doctor}/payouts', [DoctorLedgerController::class, 'storePayout']);
    });
