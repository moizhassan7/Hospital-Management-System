<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryPatient;
use App\Models\OpdAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('doctor')->check()) {
            return redirect()->route('doctors.dashboard');
        }

        return view('doctors.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ok = Auth::guard('doctor')->attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'is_active' => true,
            ],
            $request->boolean('remember')
        );

        if ($ok) {
            $request->session()->regenerate();

            return redirect()->intended(route('doctors.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match an active doctor profile.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $doctor = Auth::guard('doctor')->user()->loadMissing('department');

        $todayAppointments = OpdAppointment::query()
            ->where('doctor_code', $doctor->code)
            ->whereDate('appointment_date', today())
            ->count();

        $totalPatients = OpdAppointment::query()
            ->where('doctor_code', $doctor->code)
            ->distinct()
            ->count('mr_number');

        $pendingLabResults = LaboratoryPatient::query()
            ->where('refer_by_doctor_name', 'like', '%'.$doctor->name.'%')
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 'completed');
            })
            ->count();

        $todaysList = OpdAppointment::query()
            ->where('doctor_code', $doctor->code)
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->limit(8)
            ->get();

        return view('doctors.doctor_dashboard', compact(
            'doctor',
            'todayAppointments',
            'totalPatients',
            'pendingLabResults',
            'todaysList'
        ));
    }

    public function writePrescription()
    {
        return view('doctors.write_prescription', [
            'doctor' => Auth::guard('doctor')->user(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('doctor')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('doctors.login');
    }
}
