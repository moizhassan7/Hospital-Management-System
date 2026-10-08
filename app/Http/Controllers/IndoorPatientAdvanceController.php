<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndoorPatientAdvanceController extends Controller
{
    public function index()
    {
        // Fetch active indoor patients to manage their advances
        $indoorPatients = \App\Models\IndoorPatient::with(['patient', 'advances', 'advances.createdBy'])->active()->latest()->get();
        return view('patients.advances.index', compact('indoorPatients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mr_no' => 'required|exists:patients,mr_number',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'receipt_no' => 'nullable|string',
            'remarks' => 'nullable|string'
        ]);

        $patient = \App\Models\Patient::where('mr_number', $request->mr_no)->firstOrFail();
        $indoorPatient = \App\Models\IndoorPatient::where('patient_id', $patient->id)->active()->first();

        if (!$indoorPatient) {
            return back()->withErrors(['mr_no' => 'No active indoor stay found for this patient.']);
        }

        \App\Models\IndoorPatientAdvance::create([
            'indoor_patient_id' => $indoorPatient->id,
            'amount' => $request->amount,
            'date' => $request->date,
            'receipt_no' => $request->receipt_no,
            'remarks' => $request->remarks,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Advance amount added successfully.');
    }

    public function destroy($id)
    {
        $advance = \App\Models\IndoorPatientAdvance::findOrFail($id);
        $advance->delete();
        return back()->with('success', 'Advance amount deleted successfully.');
    }
}
