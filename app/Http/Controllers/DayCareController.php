<?php

namespace App\Http\Controllers;

use App\Models\DayCareProcedure;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DayCareController extends Controller
{
    /**
     * Show the day care registration form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Fetch the list of doctors to populate the dropdown/search
        $doctors = Doctor::all();
        return view('day_care.day_care_form', compact('doctors'));
    }

    /**
     * Store a new day care procedure record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'mr_no' => 'required|string|exists:patients,mr_number',
            'procedure_details' => 'nullable|string',
            'hours_of_stay' => 'nullable|integer|min:0',
            'consultants' => 'required|json',
            'total_bill' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'amount_receivable' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            $patient = Patient::where('mr_number', $validatedData['mr_no'])->first();

            if (!$patient) {
                return response()->json(['message' => 'Patient not found.'], 404);
            }
            
            $dayCareProcedure = DayCareProcedure::create([
                'patient_id' => $patient->id,
                'mr_no' => $validatedData['mr_no'],
                'procedure_details' => $validatedData['procedure_details'],
                'hours_of_stay' => $validatedData['hours_of_stay'],
                'consultants' => $validatedData['consultants'],
                'total_bill' => $validatedData['total_bill'],
                'amount_paid' => $validatedData['amount_paid'],
                'amount_receivable' => $validatedData['amount_receivable'],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Day care procedure saved successfully!',
                'data' => $dayCareProcedure,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
