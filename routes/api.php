<?php

// In routes/api.php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientDischargeController; // Import this if needed here
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\CategoryController;


;



// Consolidated API routes for Patient and Discharge modules
Route::get('/search-patient', [PatientController::class, 'searchPatient'])->name('patients.api_search_patient');
Route::get('/search-doctor', [PatientController::class, 'searchDoctor'])->name('patients.api_search_doctor');
Route::get('/get-by-mr-no/{mr_no}', [PatientController::class, 'getPatientByMrNo'])->name('patients.api_get_by_mr_no');
Route::get('/check-booked-appointment', [PatientController::class, 'checkBookedAppointment']);
Route::get('/generate-token-number', [PatientController::class, 'generateTokenNumber'])->name('api.generate-token-number');
Route::get('/get-doctor-fee', [PatientController::class, 'getDoctorFee'])->name('patients.api_get_doctor_fee');
Route::group(['middleware' => ['api']], function () {
    // Your API routes here
    Route::post('/consumable-items/category', [App\Http\Controllers\Api\CategoryController::class, 'store'])->name('storeCategory');
    Route::get('/consumable-items/category', [App\Http\Controllers\Api\CategoryController::class, 'show'])->name('showCategory');
});
// New API routes for the Discharge module
Route::get('/get-indoor-by-mr-no/{mr_no}', [PatientController::class, 'getIndoorPatientByMrNo'])->name('patients.api_get_indoor_by_mr_no');
Route::get('/search-diagnosis', [PatientController::class, 'searchDiagnosis'])->name('patients.api_search_diagnosis');


Route::prefix('locations')->group(function () {
    Route::get('/', [LocationController::class, 'index']);
    Route::post('/', [LocationController::class, 'store']);
    Route::put('/{location}', [LocationController::class, 'update']);
    Route::delete('/{location}', [LocationController::class, 'destroy']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});