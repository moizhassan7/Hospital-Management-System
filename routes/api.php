<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController; // Ensure these are correct
use App\Http\Controllers\Api\LocationController; // Ensure these are correct

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Category API Routes
Route::apiResource('categories', CategoryController::class);

// Location API Routes
Route::apiResource('locations', LocationController::class);