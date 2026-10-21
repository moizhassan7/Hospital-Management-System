<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\EmergencyPatient;
use App\Models\IndoorPatient;
use App\Models\OpdAppointment;
use App\Models\Patient;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $patientsRegisteredToday = Patient::whereDate('created_at', Carbon::today())->count();
        $emergencyPatientsCount = EmergencyPatient::count();

        $totalBeds = (int) Room::sum('number_of_beds');
        $occupiedBeds = IndoorPatient::active()->count();
        $availableBeds = max(0, $totalBeds - $occupiedBeds);
        $bedOccupancyPercentage = $totalBeds > 0 ? (int) round(($occupiedBeds / $totalBeds) * 100) : 0;

        $indoorPatientsCount = $occupiedBeds;

        $revenueIndoor = IndoorPatient::whereDate('created_at', Carbon::today())->sum('total_amount');
        $revenueEmergency = EmergencyPatient::whereDate('created_at', Carbon::today())->sum('total_fee');
        $revenueToday = (float) $revenueIndoor + (float) $revenueEmergency;

        $activeDoctorsCount = Doctor::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('employee_group')
                    ->orWhereNotIn('employee_group', ['Procedure', 'Service']);
            })
            ->count();

        $upcomingAppointments = OpdAppointment::query()
            ->whereDate('appointment_date', '>=', Carbon::today())
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        $patientsByDepartment = IndoorPatient::query()
            ->active()
            ->join('doctors', 'indoor_patients.consultant_id', '=', 'doctors.id')
            ->join('departments', 'doctors.department_id', '=', 'departments.id')
            ->select('departments.name as department', DB::raw('COUNT(indoor_patients.id) as patients'))
            ->groupBy('departments.name')
            ->orderByDesc('patients')
            ->get()
            ->map(fn ($row) => ['department' => $row->department, 'patients' => (int) $row->patients])
            ->all();

        if ($patientsByDepartment === []) {
            $patientsByDepartment = OpdAppointment::query()
                ->join('doctors', 'opd_appointments.doctor_code', '=', 'doctors.code')
                ->join('departments', 'doctors.department_id', '=', 'departments.id')
                ->whereDate('appointment_date', Carbon::today())
                ->select('departments.name as department', DB::raw('COUNT(opd_appointments.id) as patients'))
                ->groupBy('departments.name')
                ->orderByDesc('patients')
                ->get()
                ->map(fn ($row) => ['department' => $row->department, 'patients' => (int) $row->patients])
                ->all();
        }

        $doctorsByDepartment = Doctor::query()
            ->where('doctors.is_active', true)
            ->where(function ($query) {
                $query->whereNull('doctors.employee_group')
                    ->orWhereNotIn('doctors.employee_group', ['Procedure', 'Service']);
            })
            ->join('departments', 'doctors.department_id', '=', 'departments.id')
            ->select('departments.name as department', DB::raw('COUNT(doctors.id) as doctors'))
            ->groupBy('departments.name')
            ->orderBy('departments.name')
            ->get()
            ->map(fn ($row) => ['department' => $row->department, 'doctors' => (int) $row->doctors])
            ->all();

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
