<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SpecialityController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ConsumableItemController;
use App\Http\Controllers\NonConsumableItemController;
use App\Http\Controllers\DoctorTypeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AddDoctorController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmergencyChargeController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\EmergencyPatientController;
use App\Http\Controllers\PatientDischargeController;
use App\Http\Controllers\DayCareController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InpatientDetailController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TestHeadController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestParticularController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\LabAttendantController;
use App\Http\Controllers\ResultEntryController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


// Login Routes
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');



Route::prefix('doctor-portal')->group(function () {
    Route::get('/login', [DoctorController::class, 'showLoginForm'])->name('doctors.login');
    Route::post('/login', [DoctorController::class, 'login'])->name('doctors.login.post');
    Route::post('/logout', [DoctorController::class, 'logout'])->name('doctors.logout');

    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('doctors.dashboard');
    Route::get('/write-prescription', function () {
        return view('doctors.write_prescription');
    })->name('doctors.write_prescription');
});

// Departments Module Routes
Route::prefix('departments')->group(function () {
    Route::get('/', function () {
        return view('departments.index');
    })->name('departments.index');

    Route::get('/add', [DepartmentController::class, 'add'])->name('departments.add');
    Route::get('/{department}/edit', [DepartmentController::class, 'add'])->name('departments.edit');
    Route::post('/add', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
});

// Specialities Module Routes
Route::prefix('specialities')->group(function () {
    Route::get('/add', [SpecialityController::class, 'add'])->name('specialities.add');
    Route::get('/{speciality}/edit', [SpecialityController::class, 'add'])->name('specialities.edit');
    Route::post('/add', [SpecialityController::class, 'store'])->name('specialities.store');
    Route::put('/{speciality}', [SpecialityController::class, 'update'])->name('specialities.update');
    Route::delete('/{speciality}', [SpecialityController::class, 'destroy'])->name('specialities.destroy');
});

// Floors Module Routes
Route::prefix('floors')->group(function () {
    Route::get('/add', [FloorController::class, 'add'])->name('floors.add');
    Route::get('/{floor}/edit', [FloorController::class, 'add'])->name('floors.edit');
    Route::post('/add', [FloorController::class, 'store'])->name('floors.store');
    Route::put('/{floor}', [FloorController::class, 'update'])->name('floors.update');
    Route::delete('/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');
});

// Rooms Module Routes
Route::prefix('rooms')->group(function () {
    Route::get('/add', [RoomController::class, 'add'])->name('rooms.add');
    Route::get('/{room}/edit', [RoomController::class, 'add'])->name('rooms.edit');
    Route::post('/add', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
});

// Doctor Types Module Routes (UPDATED for CRUD)
Route::prefix('doctor-types')->group(function () {
    // Route for displaying the form (add or pre-filled for edit) and list
    Route::get('/add', [DoctorTypeController::class, 'add'])->name('doctor_types.add');
    Route::get('/{doctorType}/edit', [DoctorTypeController::class, 'add'])->name('doctor_types.edit'); // Uses 'add' method for edit view

    // Route for handling form submission (add new)
    Route::post('/add', [DoctorTypeController::class, 'store'])->name('doctor_types.store');
    // Route for handling form submission (update existing)
    Route::put('/{doctorType}', [DoctorTypeController::class, 'update'])->name('doctor_types.update');
    // Route for handling deletion
    Route::delete('/{doctorType}', [DoctorTypeController::class, 'destroy'])->name('doctor_types.destroy');
});

// Shifts Module Routes
Route::prefix('shifts')->group(function () {
    // Route for adding a new shift
    Route::get('/add', function () {
        return view('shifts.add');
    })->name('shifts.add');
});

// Emergency Charges Module Routes (UPDATED for dynamic CRUD)
Route::prefix('emergency-charges')->group(function () {
    Route::get('/add', [EmergencyChargeController::class, 'add'])->name('emergency_charges.add');
    Route::get('/{emergencyCharge}/edit', [EmergencyChargeController::class, 'add'])->name('emergency_charges.edit');
    Route::post('/add', [EmergencyChargeController::class, 'store'])->name('emergency_charges.store');
    Route::put('/{emergencyCharge}', [EmergencyChargeController::class, 'update'])->name('emergency_charges.update');
    Route::delete('/{emergencyCharge}', [EmergencyChargeController::class, 'destroy'])->name('emergency_charges.destroy');
});


// Patients Module Routes
Route::prefix('patients')->group(function () {
    Route::get('/', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/register', [PatientController::class, 'register'])->name('patients.register');
    
    // Indoor routes
    Route::get('/indoor-register', [PatientController::class, 'indoorRegister'])->name('patients.indoor_register');
    Route::post('/indoor-register', [PatientController::class, 'storeIndoor'])->name('patients.store_indoor');

    // Outdoor routes
    Route::get('/outdoor-register', [PatientController::class, 'outdoorRegister'])->name('patients.outdoor_register');
    Route::post('/outdoor-register', [PatientController::class, 'storeOutdoor'])->name('patients.store_outdoor');

    // Appointment routes
    Route::get('/book-appointment', [PatientController::class, 'bookAppointment'])->name('patients.book_appointment');
    Route::post('/book-appointment', [PatientController::class, 'storeAppointment'])->name('patients.store_appointment');
    
    // Other patient management routes
    Route::post('/register', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/all', [PatientController::class, 'showAll'])->name('patients.all');
    Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');

    Route::get('/admission-form', function () {
        return view('patients.admission_form');
    })->name('patients.admission_form');

    Route::get('/discharge', [PatientDischargeController::class, 'create'])->name('patients.discharge');
    Route::post('/discharge', [PatientDischargeController::class, 'store'])->name('patients.store_discharge');

    Route::get('/birth-certificates', function () {
        return view('patients.birth_certificates');
    })->name('patients.birth_certificates');

    Route::get('/death-certificates', function () {
        return view('patients.death_certificates');
    })->name('patients.death_certificates');

    Route::get('/create', function () {
        return "<h1>Add Patient - Coming Soon!</h1>";
    })->name('patients.create');
    Route::get('/{id}/edit', function ($id) {
        return "<h1>Edit Patient $id - Coming Soon!</h1>";
    })->name('patients.edit');
    Route::get('/{id}', function ($id) {
        return "<h1>Patient Profile $id - Coming Soon!</h1>";
    })->name('patients.show');
});

// Doctors Module Routes
Route::prefix('doctors')->group(function () {
    Route::get('/', [AddDoctorController::class, 'index'])->name('doctors.index');

    // CRUD routes
    Route::get('/create', [AddDoctorController::class, 'create'])->name('doctors.create');
    Route::get('/{doctor}/edit', [AddDoctorController::class, 'create'])->name('doctors.edit');
    Route::post('/store', [AddDoctorController::class, 'store'])->name('doctors.store');
    Route::put('/{doctor}/update', [AddDoctorController::class, 'update'])->name('doctors.update');
    Route::delete('/{doctor}', [AddDoctorController::class, 'destroy'])->name('doctors.destroy');
    Route::get('/all', [AddDoctorController::class, 'showAll'])->name('doctors.all');
});
// OPD Module Routes
Route::prefix('opd')->group(function () {
    Route::get('/consultation', function () {
        return view('opd.consultation');
    })->name('opd.consultation');
});

// Admin Module Routes
Route::prefix('admin')->group(function () {
    // User Management
    Route::get('/user-manager', [UserController::class, 'manager'])->name('admin.user_manager');
    Route::get('/user-manager/{user}/edit', [UserController::class, 'manager'])->name('admin.user_manager.edit');
    Route::post('/user-manager', [UserController::class, 'store'])->name('admin.user_manager.store');
    Route::put('/user-manager/{user}', [UserController::class, 'store'])->name('admin.user_manager.update');

    // Role Management
    Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
    Route::get('/roles/{role}/edit', [RoleController::class, 'create'])->name('admin.roles.edit');
    Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
});

// Laboratory Module Routes
Route::prefix('laboratory')->group(function () {
    // Laboratory Management Dashboard Route
    Route::get('/', function () {
        return view('laboratory.index');
    })->name('laboratory.index');
// In routes/web.php inside the 'laboratory' group
Route::get('/manage-test-head', [TestHeadController::class, 'index'])->name('test_head');
Route::get('/manage-test-head/{testHead}/edit', [TestHeadController::class, 'edit'])->name('test_head.edit');
Route::post('/manage-test-head', [TestHeadController::class, 'store'])->name('test_head.store');
Route::delete('/manage-test-head/{testHead}', [TestHeadController::class, 'destroy'])->name('test_head.destroy');
Route::put('/manage-test-head/{testHead}', [TestHeadController::class, 'update'])->name('test_head.update');
 Route::get('/manage-test', [TestController::class, 'index'])->name('laboratory.manage_test');
Route::get('/manage-test/{test}/edit', [TestController::class, 'edit'])->name('laboratory.manage_test.edit');
Route::post('/manage-test', [TestController::class, 'store'])->name('laboratory.manage_test.store');
Route::put('/manage-test/{test}', [TestController::class, 'update'])->name('laboratory.manage_test.update');
Route::delete('/manage-test/{test}', [TestController::class, 'destroy'])->name('laboratory.manage_test.destroy');
  Route::get('/add-test-particulars', [TestParticularController::class, 'index'])->name('laboratory.add_test_particulars');
    Route::post('/add-test-particulars', [TestParticularController::class, 'store'])->name('laboratory.add_test_particulars.store');
    // API route for fetching tests dynamically
    Route::get('/api/tests-by-head/{testHeadId}', [TestParticularController::class, 'getTestsByHead'])->name('api.tests_by_head');
    Route::get('/test-particular-details', [TestParticularController::class, 'showDetails'])->name('laboratory.test_particular_details');
    Route::get('/test-catalog', [LaboratoryController::class, 'showTestCatalog'])->name('laboratory.test_catalog');
   
   Route::get('/patient-registration', [LaboratoryController::class, 'showPatientRegistration'])->name('laboratory.patient_registration');
    Route::post('/patient-registration', [LaboratoryController::class, 'storeLabRegistration'])->name('laboratory.patient_registration.store');
    Route::get('/api/search-test', [LaboratoryController::class, 'searchTest'])->name('laboratory.api_search_test');

    // API Route for AJAX search
    Route::get('/api/search-patient/{mrNo}', [LaboratoryController::class, 'getPatientByMrNo'])->name('laboratory.api_search_patient');
    
Route::get('/result-entry', [ResultEntryController::class, 'searchPatient'])->name('laboratory.result_entry.search');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'showResultForm'])->name('laboratory.result_entry.show_form');
    Route::get('/result-entry/{lab_patient_id}/test/{test_id}/view', [ResultEntryController::class, 'showResultForm'])->name('laboratory.result_entry.view');
    Route::post('/result-entry/{lab_patient_id}/test/{test_id}/save', [ResultEntryController::class, 'saveResults'])->name('laboratory.result_entry.save');
    Route::get('/print-report/{lab_patient_id}/test/{test_id}', [ResultEntryController::class, 'printReport'])->name('laboratory.print_report');
});
Route::prefix('emergency')->group(function () {
    Route::get('/', [EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('/add-service', [EmergencyController::class, 'createService'])->name('emergency.add_service');
    Route::get('/{emergencyService}/edit', [EmergencyController::class, 'createService'])->name('emergency.edit_service');
    Route::post('/add-service', [EmergencyController::class, 'storeService'])->name('emergency.store_service');
    Route::put('/{emergencyService}', [EmergencyController::class, 'updateService'])->name('emergency.update_service');
    Route::delete('/{emergencyService}', [EmergencyController::class, 'destroyService'])->name('emergency.destroy_service');
Route::get('/patients/create', [EmergencyPatientController::class, 'create'])->name('emergency.patients.create');
    Route::post('/patients', [EmergencyPatientController::class, 'store'])->name('emergency.patients.store');
    Route::get('/patients/all', [EmergencyPatientController::class, 'showAll'])->name('emergency.patients.all');
    Route::get('/emergency/patients/{emergencyPatient}/receipt', [EmergencyPatientController::class, 'generateReceipt'])->name('emergency.patient.receipt');
    // New API route for fetching patient details by MR Number
    Route::get('/api/get-patient-by-mr-no/{mr_no}', [EmergencyPatientController::class, 'getPatientByMrNo'])->name('emergency.api_get_patient_by_mr_no');
});
// Store Module Routes
Route::prefix('store')->group(function () {
    Route::get('/', function () {
        return view('store.index');
    })->name('store.index');

    Route::get('/consumable-items', [ConsumableItemController::class, 'add'])->name('store.consumable_items');
    Route::get('/consumable-items/{consumableItem}/edit', [ConsumableItemController::class, 'add'])->name('store.consumable_items.edit');
    Route::post('/consumable-items', [ConsumableItemController::class, 'store'])->name('store.consumable_items.store');
    Route::put('/consumable-items/{consumableItem}', [ConsumableItemController::class, 'update'])->name('store.consumable_items.update');
    Route::delete('/consumable-items/{consumableItem}', [ConsumableItemController::class, 'destroy'])->name('store.consumable_items.destroy');

    Route::get('/non-consumable-items', [NonConsumableItemController::class, 'add'])->name('store.non_consumable_items');
    Route::get('/non-consumable-items/{nonConsumableItem}/edit', [NonConsumableItemController::class, 'add'])->name('store.non_consumable_items.edit');
    Route::post('/non-consumable-items', [NonConsumableItemController::class, 'store'])->name('store.non_consumable_items.store');
    Route::put('/non-consumable-items/{nonConsumableItem}', [NonConsumableItemController::class, 'update'])->name('store.non_consumable_items.update');
    Route::delete('/non-consumable-items/{nonConsumableItem}', [NonConsumableItemController::class, 'destroy'])->name('store.non_consumable_items.destroy');

    Route::get('/issue-stock', function () {
        return view('store.issue_stock');
    })->name('store.issue_stock');

    Route::get('/purchase-stock', function () {
        return view('store.purchase_stock');
    })->name('store.purchase_stock');
    Route::get('/supplier', [SupplierController::class, 'add'])->name('store.supplier');
    Route::get('/supplier/{supplier}/edit', [SupplierController::class, 'add'])->name('store.supplier.edit');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('store.supplier.store');
    Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('store.supplier.update');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('store.supplier.destroy');

    // Return Stock route (NEW)
    Route::get('/return-stock', function () {
        return view('store.return_stock');
    })->name('store.return_stock');
});
// Supplier Module Routes
Route::prefix('supplier')->group(function () {
    Route::get('/', function () {
        return view('store.supplier.index');
    })->name('store.supplier');
    Route::get('/create', [App\Http\Controllers\SupplierController::class, 'create'])->name('store.supplier.create');
    Route::get('/{supplier}/edit', [
        App\Http\Controllers\SupplierController::class,
        'edit'
    ])->name('store.supplier.edit');
    Route::post('/store', [App\Http\Controllers\SupplierController::class, 'store'])->name('store.supplier.store');
    Route::put('/{supplier}', [App\Http\Controllers\SupplierController::class, 'update'])->name('store.supplier.update');
    Route::delete('/{supplier}', [App\Http\Controllers\SupplierController::class, 'destroy'])->name('store.supplier.destroy');
});
Route::prefix('day-care')->group(function () {
    Route::get('/register', [DayCareController::class, 'create'])->name('day-care.create');
    Route::post('/store', [DayCareController::class, 'store'])->name('day-care.store');
});
// Procedures Module Routes (NEW)
Route::prefix('procedures')->group(function () {
    Route::get('/', [ProcedureController::class, 'index'])->name('procedures.index');
    Route::get('/create', [ProcedureController::class, 'create'])->name('procedures.create');
    Route::get('/{procedure}/edit', [ProcedureController::class, 'create'])->name('procedures.edit');
    Route::post('/', [ProcedureController::class, 'store'])->name('procedures.store');
    Route::put('/{procedure}', [ProcedureController::class, 'update'])->name('procedures.update');
    Route::delete('/{procedure}', [ProcedureController::class, 'destroy'])->name('procedures.destroy');
});
// Admin Module Routes (Already defined above)
Route::prefix('inpatient_details')->group(function () {
    Route::get('/manage', [InpatientDetailController::class, 'create'])->name('inpatient.manage');
    Route::post('/store', [InpatientDetailController::class, 'store'])->name('inpatient.store');
});   
// API Routes
Route::prefix('api')->group(function () {
    // API routes for Patient and Discharge modules
    Route::get('/search-patient', [App\Http\Controllers\PatientController::class, 'searchPatient'])->name('patients.api_search_patient');
    Route::get('/search-doctor', [App\Http\Controllers\PatientController::class, 'searchDoctor'])->name('patients.api_search_doctor');
    Route::get('/get-by-mr-no/{mr_no}', [App\Http\Controllers\PatientController::class, 'getPatientByMrNo'])->name('patients.api_get_by_mr_no');
    Route::get('/check-booked-appointment', [App\Http\Controllers\PatientController::class, 'checkBookedAppointment']);
    Route::get('/generate-token-number', [App\Http\Controllers\PatientController::class, 'generateTokenNumber'])->name('api.generate-token-number');
    Route::get('/get-indoor-by-mr-no/{mr_no}', [App\Http\Controllers\PatientController::class, 'getIndoorPatientByMrNo'])->name('patients.api_get_indoor_by_mr_no');
    Route::get('/search-diagnosis', [App\Http\Controllers\PatientController::class, 'searchDiagnosis'])->name('patients.api_search_diagnosis');
    Route::get('/get-details-by-mr-no/{mr_no}', [App\Http\Controllers\InpatientDetailController::class, 'getDetailsByMrNo'])->name('inpatient.api.get_details');

   
});
Route::prefix('reports')->group(function () {
    Route::get('/', [App\Http\Controllers\ReportController::class,'index'])->name('index');
Route::get('/indoor-patient-summary', [ReportController::class, 'indoorPatientSummary'])->name('reports.indoor_patient_summary');
 Route::get('/indoor-patient-summary/download', [ReportController::class, 'downloadIndoorPatientSummaryPdf'])->name('reports.indoor_patient_summary.download');
Route::get('/opd-summary', [ReportController::class, 'opdSummary'])->name('reports.opd_summary');
 Route::get('/indoor-discharge-history', [ReportController::class, 'indoorDischargePatientHistory'])->name('reports.indoor_discharge_history');
    Route::get('/indoor-discharge-history/download', [ReportController::class, 'downloadIndoorDischargeHistoryPdf'])->name('reports.indoor_discharge_history.download');
 Route::get('/indoor-discharge-payment', [ReportController::class, 'indoorDischargePatientPayment'])->name('reports.indoor_discharge_payment');
    Route::get('/indoor-discharge-payment/download', [ReportController::class, 'downloadIndoorDischargePaymentPdf'])->name('reports.indoor_discharge_payment.download');
});


// Redirect the root URL to the login page if not authenticated
Route::get('/', function () {
    return redirect()->route('login');
});
Route::prefix('lab-attendant')->name('lab-attendant.')->group(function () {
    Route::get('/', [LabAttendantController::class, 'index'])->name('index');
    Route::get('/get-patient-tests/{mr_no}', [LabAttendantController::class, 'getPatientTests'])->name('get-patient-tests');
    Route::post('/save-result', [LabAttendantController::class, 'saveResult'])->name('save-result');
});

 