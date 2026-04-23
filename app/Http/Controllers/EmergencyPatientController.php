<?php

namespace App\Http\Controllers;

use App\Models\EmergencyPatient;
use App\Models\EmergencyService;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\EmergencyCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EmergencyPatientController extends Controller
{
    public function create()
    {
        // Load the necessary data for the form
        $emergencyServices = EmergencyService::all();
        $doctors = Doctor::all();
        $emergencyCharges = EmergencyCharge::latest()->first();

        return view('emergency.create_patient', compact('emergencyServices', 'doctors', 'emergencyCharges'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mr_number' => 'required|string|exists:patients,mr_number',
            'patient_name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'emergency_hours' => 'required|integer|min:1',
            'first_hour_charges' => 'required|numeric',
            'other_hours_charges' => 'required|numeric',
            'total_fee' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'amount_receivable' => 'required|numeric',
            'consultants' => 'required|json', // Validation for the new JSON column
        ]);
        
        try {
            DB::beginTransaction();

            $emergencyPatient = EmergencyPatient::create([
                'mr_number' => $request->mr_number,
                'patient_name' => $request->patient_name,
                'age' => $request->age,
                'gender' => $request->gender,
                'emergency_hours' => $request->emergency_hours,
                'first_hour_charges' => $request->first_hour_charges,
                'other_hours_charges' => $request->other_hours_charges,
                'total_fee' => $request->total_fee,
                'amount_paid' => $request->amount_paid,
                'amount_receivable' => $request->amount_receivable,
                'consultants' => $request->consultants, // Save the JSON string directly
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Emergency Patient registered successfully!', 
                'patient' => $emergencyPatient
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to register emergency patient.', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showAll()
    {
        $emergencyPatients = EmergencyPatient::all();
        return view('emergency.all_patients', compact('emergencyPatients'));
    }

    public function getPatientByMrNo($mr_no)
    {
        $patient = Patient::where('mr_number', $mr_no)->first();
        return response()->json($patient);
    }
    
    public function generateReceipt(EmergencyPatient $emergencyPatient)
    {
        // No longer need to load relationships since data is in JSON column
        // Just pass the patient data to the receipt view
        return view('emergency.receipt', compact('emergencyPatient'));
    }
}