<?php

namespace App\Http\Controllers;

use App\Models\EmergencyService;
use App\Models\EmergencyCharge;
use App\Models\Doctor;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function index()
    {
        return view('emergency.index');
    }

    public function createService(EmergencyService $emergencyService = null)
    {
        $emergencyServices = EmergencyService::all();
        
        return view('emergency.add_service', compact('emergencyServices', 'emergencyService'));
    }

    public function createPatient()
    {
        $emergencyCharges = EmergencyCharge::latest()->first();
        $emergencyServices = EmergencyService::all();
        $doctors = Doctor::all();
        
        return view('emergency.create_patient', compact('emergencyCharges', 'emergencyServices', 'doctors'));
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'service_fee' => 'required|numeric|min:0',
        ]);

        EmergencyService::create([
            'name' => $request->input('service_name'),
            'fee' => $request->input('service_fee'),
        ]);

        return redirect()->route('emergency.add_service')->with('success', 'Emergency Service added successfully!');
    }

    public function updateService(Request $request, EmergencyService $emergencyService)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'service_fee' => 'required|numeric|min:0',
        ]);
        
        $emergencyService->update([
            'name' => $request->input('service_name'),
            'fee' => $request->input('service_fee'),
        ]);

        return redirect()->route('emergency.add_service')->with('success', 'Emergency Service updated successfully!');
    }

    public function destroyService(EmergencyService $emergencyService)
    {
        $emergencyService->delete();
        return redirect()->route('emergency.add_service')->with('success', 'Emergency Service deleted successfully!');
    }
}