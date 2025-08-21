<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\IssueController; 
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReturnController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Category API Routes
Route::apiResource('categories', CategoryController::class);

// Location API Routes
Route::apiResource('locations', LocationController::class);

// Issue Stock API Routes (NEW)
Route::get('issue/search-employees', [IssueController::class, 'searchEmployees']);
Route::get('issue/search-items', [IssueController::class, 'searchItems']);
Route::post('issue/store', [IssueController::class, 'store']); 
Route::get('purchase/search-items', [PurchaseController::class, 'searchItems']);
Route::post('purchase/store', [PurchaseController::class, 'store']);
Route::get('return/search-issued-items', [ReturnController::class, 'searchIssuedItems']);
Route::post('return/store', [ReturnController::class, 'store']);