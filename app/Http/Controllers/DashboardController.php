<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\EmergencyPatient;
use App\Models\IndoorPatient;
use App\Models\Doctor;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a dynamic dashboard with real-time data.
     *
     * @return \Illuminate\View\View
     */

    public function index()
    {
        // 1. Patients Registered Today
        $patientsRegisteredToday = Patient::whereDate('created_at', Carbon::today())->count();

        // 2. Emergency Patients
        $emergencyPatientsCount = EmergencyPatient::count();

        // 3. Bed Occupancy & Available Beds
        $totalBeds = Room::sum('number_of_beds');
        $occupiedBeds = IndoorPatient::count();
        $availableBeds = $totalBeds - $occupiedBeds;
        $bedOccupancyPercentage = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0;

        // 4. Indoor Patients
        $indoorPatientsCount = IndoorPatient::count();

        // 5. Revenue Today
        // This assumes you have `total_fee` on EmergencyPatient and `total_amount` on IndoorPatient
        $revenueIndoor = IndoorPatient::whereDate('created_at', Carbon::today())->sum('total_amount');
        $revenueEmergency = EmergencyPatient::whereDate('created_at', Carbon::today())->sum('total_fee');
        $revenueToday = $revenueIndoor + $revenueEmergency;

        // 6. Active Doctors
        // This assumes a boolean 'is_active' column on your doctors table.
        $activeDoctorsCount = Doctor::where('is_active', true)->count();

        // 7. Upcoming Appointments
        // This is a placeholder as your schema for appointments is not provided.
        // You would query your `appointments` table here.
        $upcomingAppointments = 60; 

        // 8. Patients by Department
        // This is a placeholder. A real implementation would involve grouping and counting patients by their department.
        $patientsByDepartment = [
            ['department' => 'Cardiology', 'patients' => 75],
            ['department' => 'Pediatrics', 'patients' => 50],
            ['department' => 'Neurology', 'patients' => 30],
            ['department' => 'Orthopedics', 'patients' => 65],
            ['department' => 'Emergency', 'patients' => 25],
        ];

        // 9. Doctors by Department
        // This is a placeholder. A real implementation would involve joining doctors with their departments.
        $doctorsByDepartment = [
            ['department' => 'Cardiology', 'doctors' => 10],
            ['department' => 'Pediatrics', 'doctors' => 8],
            ['department' => 'Neurology', 'doctors' => 7],
            ['department' => 'Orthopedics', 'doctors' => 9],
            ['department' => 'Emergency', 'doctors' => 12],
        ];

        return view('dashboard', compact(
            'patientsRegisteredToday',
            'emergencyPatientsCount',
            'bedOccupancyPercentage',
            'indoorPatientsCount',
            'revenueToday',
            'activeDoctorsCount',
            'availableBeds',
            'upcomingAppointments',
            'patientsByDepartment',
            'doctorsByDepartment'
        ));
    }
}