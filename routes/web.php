<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SpecialityController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ConsumableItemController;
use App\Http\Controllers\NonConsumableItemController; // Add this line

// Dashboard Route
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

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

// Doctor Types Module Routes
Route::prefix('doctor-types')->group(function () {
    // Route for adding a new doctor type
    Route::get('/add', function () {
        return view('doctor_types.add');
    })->name('doctor_types.add');
});

// Shifts Module Routes
Route::prefix('shifts')->group(function () {
    // Route for adding a new shift
    Route::get('/add', function () {
        return view('shifts.add');
    })->name('shifts.add');
});

// Emergency Charges Module Routes
Route::prefix('emergency-charges')->group(function () {
    // Route for adding emergency charges
    Route::get('/add', function () {
        return view('emergency_charges.add');
    })->name('emergency_charges.add');
});


// Patients Module Routes
Route::prefix('patients')->group(function () {
    Route::get('/', function () {
        return view('patients.index');
    })->name('patients.index');

    Route::get('/indoor-register', function () {
        return view('patients.indoor_register');
    })->name('patients.indoor_register');

    Route::get('/outdoor-register', function () {
        return view('patients.outdoor_register');
    })->name('patients.outdoor_register');

    Route::get('/all', function () {
        return view('patients.all');
    })->name('patients.all');

    Route::get('/admission-form', function () {
        return view('patients.admission_form');
    })->name('patients.admission_form');

    Route::get('/discharge-form', function () {
        return view('patients.discharge_form');
    })->name('patients.discharge_form');

    Route::get('/birth-certificates', function () {
        return view('patients.birth_certificates');
    })->name('patients.birth_certificates');

    Route::get('/death-certificates', function () {
        return view('patients.death_certificates');
    })->name('patients.death_certificates');

    Route::get('/create', function () { return "<h1>Add Patient - Coming Soon!</h1>"; })->name('patients.create');
    Route::get('/{id}/edit', function ($id) { return "<h1>Edit Patient $id - Coming Soon!</h1>"; })->name('patients.edit');
    Route::get('/{id}', function ($id) { return "<h1>Patient Profile $id - Coming Soon!</h1>"; })->name('patients.show');
});


// Doctors Module Routes
Route::prefix('doctors')->group(function () {
    Route::get('/', function () {
        return view('doctors.dashboard');
    })->name('doctors.index');

    Route::get('/create', function () {
        return view('doctors.create');
    })->name('doctors.create');

    Route::get('/all', function () {
        return view('doctors.all');
    })->name('doctors.all');

    Route::get('/{id}/edit', function ($id) { return "<h1>Edit Doctor $id - Coming Soon!</h1>"; })->name('doctors.edit');
});

// OPD Module Routes
Route::prefix('opd')->group(function () {
    Route::get('/consultation', function () {
        return view('opd.consultation');
    })->name('opd.consultation');
});

// Admin Module Routes
Route::prefix('admin')->group(function () {
    Route::get('/user-manager', function () {
        return view('users.manager');
    })->name('admin.user_manager');
});

// Laboratory Module Routes
Route::prefix('laboratory')->group(function () {
    Route::get('/', function () {
        return view('laboratory.index');
    })->name('laboratory.index');

    Route::get('/manage-test-head', function () {
        return view('laboratory.manage_test_head');
    })->name('laboratory.manage_test_head');

    Route::get('/manage-test', function () {
        return view('laboratory.manage_test');
    })->name('laboratory.manage_test');

    Route::get('/add-test-particulars', function () {
        return view('laboratory.add_test_particulars');
    })->name('laboratory.add_test_particulars');

    Route::get('/test-particular-details', function () {
        return view('laboratory.test_particular_details');
    })->name('laboratory.test_particular_details');

    Route::get('/patient-registration', function () {
        return view('laboratory.patient_registration');
    })->name('laboratory.patient_registration');
});

// Store Module Routes (UPDATED for Non-Consumable Items)
Route::prefix('store')->group(function () {
    Route::get('/', function () {
        return view('store.index');
    })->name('store.index');

    // Routes for Consumable Items
    Route::get('/consumable-items', [ConsumableItemController::class, 'add'])->name('store.consumable_items');
    Route::get('/consumable-items/{consumableItem}/edit', [ConsumableItemController::class, 'add'])->name('store.consumable_items.edit');
    Route::post('/consumable-items', [ConsumableItemController::class, 'store'])->name('store.consumable_items.store');
    Route::put('/consumable-items/{consumableItem}', [ConsumableItemController::class, 'update'])->name('store.consumable_items.update');
    Route::delete('/consumable-items/{consumableItem}', [ConsumableItemController::class, 'destroy'])->name('store.consumable_items.destroy');

    // Routes for Non-Consumable Items (NEW)
    Route::get('/non-consumable-items', [NonConsumableItemController::class, 'add'])->name('store.non_consumable_items');
    Route::get('/non-consumable-items/{nonConsumableItem}/edit', [NonConsumableItemController::class, 'add'])->name('store.non_consumable_items.edit');
    Route::post('/non-consumable-items', [NonConsumableItemController::class, 'store'])->name('store.non_consumable_items.store');
    Route::put('/non-consumable-items/{nonConsumableItem}', [NonConsumableItemController::class, 'update'])->name('store.non_consumable_items.update');
    Route::delete('/non-consumable-items/{nonConsumableItem}', [NonConsumableItemController::class, 'destroy'])->name('store.non_consumable_items.destroy');
 Route::get('/issue-stock', function () {
        return view('store.issue_stock');
    })->name('store.issue_stock');
});


// Redirect the root URL to the main dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});