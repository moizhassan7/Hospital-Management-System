<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestHeadController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestParticularController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\PathologyHubController;
use App\Http\Controllers\ResultEntryController;
use App\Http\Controllers\SamplePortalController;
use App\Http\Controllers\FrontDeskPrintController;
use App\Http\Controllers\LabAttendantController;
use App\Http\Controllers\OnlineReportController;
use App\Http\Controllers\CriticalTestReportController;
use App\Http\Controllers\LabSamplesReportController;
use App\Http\Controllers\LabSettingsController;

// Root + dashboard redirects (cacheable — no closures)
Route::redirect('/', '/login');
Route::redirect('/dashboard', '/pathology')->name('dashboard');

// Authentication
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Admin: user & role management
Route::prefix('admin')->group(function () {
    Route::get('/user-manager', [UserController::class, 'manager'])->name('admin.user_manager');
    Route::get('/user-manager/{user}/edit', [UserController::class, 'manager'])->name('admin.user_manager.edit');
    Route::post('/user-manager', [UserController::class, 'store'])->name('admin.user_manager.store');
    Route::put('/user-manager/{user}', [UserController::class, 'store'])->name('admin.user_manager.update');

    Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
    Route::get('/roles/{role}/edit', [RoleController::class, 'create'])->name('admin.roles.edit');
    Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
});

// Laboratory backend routes (forms / API only — all UI via /pathology)
Route::prefix('laboratory')->group(function () {
    Route::redirect('/', '/pathology');
    Route::redirect('/manage-test', '/pathology/manage-test');
    Route::redirect('/manage-test-head', '/pathology/manage-test-head');
    Route::redirect('/add-test-particulars', '/pathology/add-test-particulars');
    Route::redirect('/test-catalog', '/pathology/test-catalog');
    Route::redirect('/result-entry', '/pathology/result-entry');
    Route::redirect('/manage-test/{test}/edit', '/pathology/manage-test/{test}/edit');
    Route::redirect('/manage-test-head/{testHead}/edit', '/pathology/manage-test-head/{testHead}/edit');
    Route::redirect('/add-test-particulars/{testParticular}/edit', '/pathology/add-test-particulars/{testParticular}/edit');

    Route::post('/manage-test-head', [TestHeadController::class, 'store'])->name('test_head.store');
    Route::delete('/manage-test-head/{testHead}', [TestHeadController::class, 'destroy'])->name('test_head.destroy');
    Route::put('/manage-test-head/{testHead}', [TestHeadController::class, 'update'])->name('test_head.update');

    Route::post('/manage-test', [TestController::class, 'store'])->name('laboratory.manage_test.store');
    Route::put('/manage-test/{test}', [TestController::class, 'update'])->name('laboratory.manage_test.update');
    Route::delete('/manage-test/{test}', [TestController::class, 'destroy'])->name('laboratory.manage_test.destroy');

    Route::post('/add-test-particulars', [TestParticularController::class, 'store'])->name('laboratory.add_test_particulars.store');
    Route::post('/add-test-particulars/bulk', [TestParticularController::class, 'storeBulk'])->name('laboratory.add_test_particulars.store_bulk');
    Route::put('/add-test-particulars/{testParticular}', [TestParticularController::class, 'update'])->name('laboratory.add_test_particulars.update');
    Route::delete('/add-test-particulars/{testParticular}', [TestParticularController::class, 'destroy'])->name('laboratory.add_test_particulars.destroy');
    Route::get('/api/tests-by-head/{testHeadId}', [TestParticularController::class, 'getTestsByHead'])->name('api.tests_by_head');
    Route::get('/test-particular-details', [TestParticularController::class, 'showDetails'])->name('laboratory.test_particular_details');

    Route::get('/result-entry/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'showResultForm'])->name('laboratory.result_entry.show_form');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}/edit', [ResultEntryController::class, 'showResultForm'])->name('laboratory.result_entry.edit');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}/view', [ResultEntryController::class, 'showResultForm'])->name('laboratory.result_entry.view');
    Route::post('/result-entry/{lab_patient_id}/test/{test_id}/save', [ResultEntryController::class, 'saveResults'])->name('laboratory.result_entry.save');
    Route::get('/print-report/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'printReport'])->name('laboratory.print_report');
});

// Public online report (token based)
Route::get('/report/{token}', [OnlineReportController::class, 'show'])->name('pathology.online_report');

// Pathology Lab UI
Route::prefix('pathology')->group(function () {
    Route::get('/', [PathologyHubController::class, 'index'])->name('pathology.index');

    Route::get('/result-entry', [ResultEntryController::class, 'searchPatient'])->name('pathology.result_entry.search');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'showResultForm'])->name('pathology.result_entry.show_form');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}/edit', [ResultEntryController::class, 'showResultForm'])->name('pathology.result_entry.edit');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}/view', [ResultEntryController::class, 'showResultForm'])->name('pathology.result_entry.view');
    Route::post('/result-entry/{lab_patient_id}/test/{test_id}/save', [ResultEntryController::class, 'saveResults'])->name('pathology.result_entry.save');
    Route::get('/print-report/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'printReport'])->name('pathology.print_report');
    Route::get('/print-report/{lab_patient_id}/test/{test_id}/pdf', [ResultEntryController::class, 'downloadPdf'])->name('pathology.print_report.pdf');

    Route::get('/front-desk-print', [FrontDeskPrintController::class, 'index'])->name('pathology.front_desk_print');
    Route::get('/front-desk-print/{lab_patient_id}/test/{test_id}/print', [FrontDeskPrintController::class, 'printReport'])->name('pathology.front_desk_print.print');
    Route::get('/front-desk-print/{lab_patient_id}/test/{test_id}/pdf', [FrontDeskPrintController::class, 'downloadPdf'])->name('pathology.front_desk_print.pdf');

    Route::get('/test-catalog', [LaboratoryController::class, 'showTestCatalog'])->name('pathology.test_catalog');
    Route::get('/manage-test', [TestController::class, 'index'])->name('pathology.manage_test');
    Route::get('/manage-test/{test}/edit', [TestController::class, 'edit'])->name('pathology.manage_test.edit');
    Route::get('/manage-test-head', [TestHeadController::class, 'index'])->name('pathology.test_head');

    Route::get('/sample-portal', [SamplePortalController::class, 'index'])->name('pathology.sample_portal');
    Route::post('/sample-portal/collect', [SamplePortalController::class, 'collectAndPrint'])->name('pathology.sample_portal.collect');
    Route::post('/sample-portal/collect-test', [SamplePortalController::class, 'collectAndPrintTest'])->name('pathology.sample_portal.collect_test');
    Route::get('/sample-portal/{laboratory_patient_id}/print', [SamplePortalController::class, 'printBarcodes'])->name('pathology.sample_portal.print');
    Route::get('/sample-portal/{laboratory_patient_id}/print/zpl', [SamplePortalController::class, 'downloadZplLabels'])->name('pathology.sample_portal.print.zpl');
    Route::get('/sample-portal/{laboratory_patient_id}/print/tspl', [SamplePortalController::class, 'downloadTsplLabels'])->name('pathology.sample_portal.print.tspl');
    Route::post('/sample-portal/{laboratory_patient_id}/test-status', [SamplePortalController::class, 'updateTestSampleStatus'])->name('pathology.sample_portal.test_status');
    Route::post('/sample-portal/vial/{vial}/status', [SamplePortalController::class, 'updateVialStatus'])->name('pathology.sample_portal.vial_status');

    Route::get('/lab-attendant', [LabAttendantController::class, 'index'])->name('pathology.lab_attendant');
    Route::post('/lab-attendant/scan', [LabAttendantController::class, 'scanBarcode'])->name('pathology.lab_attendant.scan');

    Route::get('/critical-report', [CriticalTestReportController::class, 'index'])->name('pathology.critical_report');
    Route::get('/critical-report/print', [CriticalTestReportController::class, 'print'])->name('pathology.critical_report.print');
    Route::get('/critical-report/pdf', [CriticalTestReportController::class, 'downloadPdf'])->name('pathology.critical_report.pdf');

    Route::get('/lab-samples-report', [LabSamplesReportController::class, 'index'])->name('pathology.lab_samples_report');
    Route::get('/lab-samples-report/print', [LabSamplesReportController::class, 'print'])->name('pathology.lab_samples_report.print');
    Route::get('/lab-samples-report/pdf', [LabSamplesReportController::class, 'downloadPdf'])->name('pathology.lab_samples_report.pdf');

    Route::get('/add-test-particulars', [TestParticularController::class, 'index'])->name('pathology.add_test_particulars');
    Route::get('/add-test-particulars/{testParticular}/edit', [TestParticularController::class, 'edit'])->name('pathology.add_test_particulars.edit');
    Route::get('/manage-test-head/{testHead}/edit', [TestHeadController::class, 'edit'])->name('pathology.test_head.edit');

    Route::get('/settings', [LabSettingsController::class, 'index'])->name('pathology.settings.index');
    Route::put('/settings/branding', [LabSettingsController::class, 'updateBranding'])->name('pathology.settings.branding.update');
    Route::post('/settings/doctors', [LabSettingsController::class, 'storeDoctor'])->name('pathology.settings.doctors.store');
    Route::put('/settings/doctors/{doctor}', [LabSettingsController::class, 'updateDoctor'])->name('pathology.settings.doctors.update');
    Route::delete('/settings/doctors/{doctor}', [LabSettingsController::class, 'destroyDoctor'])->name('pathology.settings.doctors.destroy');
});

// Legacy path redirects (cacheable)
Route::redirect('/radiology', '/pathology');
Route::redirect('/radiology/{any}', '/pathology')->where('any', '.*');
Route::redirect('/lab-attendant', '/pathology/lab-attendant');
Route::redirect('/lab-attendant/{any}', '/pathology/lab-attendant')->where('any', '.*');
