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
use App\Http\Controllers\LabFinancialSummaryController;
use App\Http\Controllers\LabSettingsController;

// Root + dashboard redirects (cacheable — no closures)
Route::redirect('/', '/login');
Route::redirect('/dashboard', '/pathology')->name('dashboard');

// Authentication & User Profile
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('user.password.edit');
Route::put('/change-password', [UserController::class, 'updatePassword'])->name('user.password.update');

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
    Route::get('/print-report/{lab_patient_id}/all', [ResultEntryController::class, 'printAllReports'])->name('laboratory.print_all_reports');
});

// Public online report (token based)
Route::get('/report/{token}', [OnlineReportController::class, 'show'])->name('pathology.online_report');

// Pathology Lab UI
Route::prefix('pathology')->group(function () {
    Route::get('/', [PathologyHubController::class, 'index'])->name('pathology.index');

    Route::get('/bookings/create', [\App\Http\Controllers\BookingController::class, 'create'])->name('pathology.bookings.create');
    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->name('pathology.bookings.store');
    Route::get('/bookings/{id}/receipt', [\App\Http\Controllers\BookingController::class, 'printReceipt'])->name('pathology.bookings.receipt');
    Route::get('/bookings/{id}/a4-receipt', [\App\Http\Controllers\BookingController::class, 'a4Receipt'])->name('pathology.bookings.a4_receipt');
    Route::post('/bookings/{id}/collect-due', [\App\Http\Controllers\BookingController::class, 'collectDue'])->name('pathology.bookings.collect_due');
    Route::post('/bookings/{id}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('pathology.bookings.cancel');
    Route::get('/api/search-patients', [\App\Http\Controllers\BookingController::class, 'searchPatients'])->name('pathology.api.search_patients');

    Route::get('/result-entry', [ResultEntryController::class, 'searchPatient'])->name('pathology.result_entry.search');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'showResultForm'])->name('pathology.result_entry.show_form');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}/edit', [ResultEntryController::class, 'showResultForm'])->name('pathology.result_entry.edit');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}/view', [ResultEntryController::class, 'showResultForm'])->name('pathology.result_entry.view');
    Route::post('/result-entry/{lab_patient_id}/test/{test_id}/save', [ResultEntryController::class, 'saveResults'])->name('pathology.result_entry.save');
    Route::get('/print-report/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'printReport'])->name('pathology.print_report');
    Route::get('/print-report/{lab_patient_id}/test/{test_id}/pdf', [ResultEntryController::class, 'downloadPdf'])->name('pathology.print_report.pdf');
    Route::get('/print-report/{lab_patient_id}/all', [ResultEntryController::class, 'printAllReports'])->name('pathology.print_all_reports');

    Route::get('/front-desk-print', [FrontDeskPrintController::class, 'index'])->name('pathology.front_desk_print');
    Route::get('/front-desk-print/all', [FrontDeskPrintController::class, 'printAllReports'])->name('pathology.front_desk_print.all');
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

    Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('pathology.expenses.index');
    Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('pathology.expenses.store');
    Route::delete('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('pathology.expenses.destroy');

    Route::get('/lab-financial-summary', [\App\Http\Controllers\LabFinancialSummaryController::class, 'index'])->name('pathology.lab_financial_summary');
    Route::get('/lab-financial-summary/print', [LabFinancialSummaryController::class, 'print'])->name('pathology.lab_financial_summary.print');
    Route::get('/lab-financial-summary/pdf', [LabFinancialSummaryController::class, 'downloadPdf'])->name('pathology.lab_financial_summary.pdf');

    Route::get('/add-test-particulars', [TestParticularController::class, 'index'])->name('pathology.add_test_particulars');
    Route::get('/add-test-particulars/{testParticular}/edit', [TestParticularController::class, 'edit'])->name('pathology.add_test_particulars.edit');
    Route::get('/manage-test-head/{testHead}/edit', [TestHeadController::class, 'edit'])->name('pathology.test_head.edit');

    Route::get('/settings', [LabSettingsController::class, 'index'])->name('pathology.settings.index');
    Route::put('/settings/branding', [LabSettingsController::class, 'updateBranding'])->name('pathology.settings.branding.update');
    Route::post('/settings/doctors', [LabSettingsController::class, 'storeDoctor'])->name('pathology.settings.doctors.store');
    Route::put('/settings/doctors/{doctor}', [LabSettingsController::class, 'updateDoctor'])->name('pathology.settings.doctors.update');
    Route::delete('/settings/doctors/{doctor}', [LabSettingsController::class, 'destroyDoctor'])->name('pathology.settings.doctors.destroy');

    // LIMS hubs: collection centers, referring doctors, commission rules
    Route::get('/collection-centers', [\App\Http\Controllers\Lims\CollectionCenterController::class, 'index'])->name('pathology.collection_centers.index');
    Route::get('/collection-centers/create', [\App\Http\Controllers\Lims\CollectionCenterController::class, 'create'])->name('pathology.collection_centers.create');
    Route::post('/collection-centers', [\App\Http\Controllers\Lims\CollectionCenterController::class, 'store'])->name('pathology.collection_centers.store');
    Route::get('/collection-centers/{collectionCenter}/edit', [\App\Http\Controllers\Lims\CollectionCenterController::class, 'edit'])->name('pathology.collection_centers.edit');
    Route::put('/collection-centers/{collectionCenter}', [\App\Http\Controllers\Lims\CollectionCenterController::class, 'update'])->name('pathology.collection_centers.update');
    Route::post('/collection-centers/{collectionCenter}/toggle', [\App\Http\Controllers\Lims\CollectionCenterController::class, 'toggle'])->name('pathology.collection_centers.toggle');

    Route::get('/lims-doctors', [\App\Http\Controllers\Lims\LimsDoctorController::class, 'index'])->name('pathology.lims_doctors.index');
    Route::get('/lims-doctors/create', [\App\Http\Controllers\Lims\LimsDoctorController::class, 'create'])->name('pathology.lims_doctors.create');
    Route::post('/lims-doctors', [\App\Http\Controllers\Lims\LimsDoctorController::class, 'store'])->name('pathology.lims_doctors.store');
    Route::get('/lims-doctors/{limsDoctor}/edit', [\App\Http\Controllers\Lims\LimsDoctorController::class, 'edit'])->name('pathology.lims_doctors.edit');
    Route::put('/lims-doctors/{limsDoctor}', [\App\Http\Controllers\Lims\LimsDoctorController::class, 'update'])->name('pathology.lims_doctors.update');
    Route::get('/lims-doctors/{limsDoctor}/ledger', [\App\Http\Controllers\Lims\LimsDoctorController::class, 'ledger'])->name('pathology.lims_doctors.ledger');
    Route::post('/lims-doctors/{limsDoctor}/payouts', [\App\Http\Controllers\Lims\LimsDoctorController::class, 'storePayout'])->name('pathology.lims_doctors.payout');

    Route::get('/commission-rules', [\App\Http\Controllers\Lims\CommissionRuleController::class, 'index'])->name('pathology.commission_rules.index');
    Route::get('/commission-rules/create', [\App\Http\Controllers\Lims\CommissionRuleController::class, 'create'])->name('pathology.commission_rules.create');
    Route::post('/commission-rules', [\App\Http\Controllers\Lims\CommissionRuleController::class, 'store'])->name('pathology.commission_rules.store');
    Route::get('/commission-rules/{commissionRule}/edit', [\App\Http\Controllers\Lims\CommissionRuleController::class, 'edit'])->name('pathology.commission_rules.edit');
    Route::put('/commission-rules/{commissionRule}', [\App\Http\Controllers\Lims\CommissionRuleController::class, 'update'])->name('pathology.commission_rules.update');
    Route::post('/commission-rules/{commissionRule}/deactivate', [\App\Http\Controllers\Lims\CommissionRuleController::class, 'deactivate'])->name('pathology.commission_rules.deactivate');
    Route::get('/commission-snapshots', [\App\Http\Controllers\Lims\CommissionRuleController::class, 'snapshots'])->name('pathology.commission_snapshots.index');

    // Sample transit / chain of custody (Phase 2 Blade)
    Route::get('/sample-batches', [\App\Http\Controllers\Lims\SampleBatchController::class, 'index'])->name('pathology.sample_batches.index');
    Route::get('/sample-batches/create', [\App\Http\Controllers\Lims\SampleBatchController::class, 'create'])->name('pathology.sample_batches.create');
    Route::post('/sample-batches', [\App\Http\Controllers\Lims\SampleBatchController::class, 'store'])->name('pathology.sample_batches.store');
    Route::get('/sample-batches/{sampleBatch}', [\App\Http\Controllers\Lims\SampleBatchController::class, 'show'])->name('pathology.sample_batches.show');
    Route::post('/sample-batches/{sampleBatch}/items', [\App\Http\Controllers\Lims\SampleBatchController::class, 'addItem'])->name('pathology.sample_batches.items.store');
    Route::delete('/sample-batches/{sampleBatch}/items/{sampleId}', [\App\Http\Controllers\Lims\SampleBatchController::class, 'removeItem'])->name('pathology.sample_batches.items.destroy');
    Route::post('/sample-batches/{sampleBatch}/dispatch', [\App\Http\Controllers\Lims\SampleBatchController::class, 'dispatchBatch'])->name('pathology.sample_batches.dispatch');
    Route::post('/sample-batches/{sampleBatch}/in-transit', [\App\Http\Controllers\Lims\SampleBatchController::class, 'markInTransit'])->name('pathology.sample_batches.in_transit');
    Route::post('/sample-batches/{sampleBatch}/receive', [\App\Http\Controllers\Lims\SampleBatchController::class, 'receive'])->name('pathology.sample_batches.receive');
});

// Legacy path redirects (cacheable)
Route::redirect('/radiology', '/pathology');
Route::redirect('/radiology/{any}', '/pathology')->where('any', '.*');
Route::redirect('/lab-attendant', '/pathology/lab-attendant');
Route::redirect('/lab-attendant/{any}', '/pathology/lab-attendant')->where('any', '.*');
