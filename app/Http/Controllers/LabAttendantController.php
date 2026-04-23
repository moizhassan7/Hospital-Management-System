<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\LabTestResult;
use Illuminate\Http\Request;

class LabAttendantController extends Controller
{
    /**
     * Show the laboratory attendant's dashboard.
     */
    public function index()
    {
        return view('lab-attendant.index');
    }

    /**
     * Get a patient's pending tests by MR number.
     */
    public function getPatientTests($mr_no)
    {
        $patient = Patient::where('mr_number', $mr_no)->first();

        if (!$patient) {
            return response()->json(['error' => 'Patient not found.'], 404);
        }

        $pendingTests = LabTestResult::where('patient_id', $patient->id)
            ->with('test')
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'patient' => $patient,
            'tests' => $pendingTests,
        ]);
    }

    /**
     * Get the history of a specific test for a patient.
     */
    public function getTestHistory($test_id)
    {
        $testHistory = LabTestResult::where('test_id', $test_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($testHistory);
    }

    /**
     * Save the result for a specific test.
     */
    public function saveResult(Request $request)
    {
        $request->validate([
            'result_id' => 'required|exists:lab_test_results,id',
            'result_value' => 'required|string',
            'status' => 'required|in:completed,urgent',
        ]);

        $labTestResult = LabTestResult::find($request->result_id);
        $labTestResult->result = $request->result_value;
        $labTestResult->status = $request->status;
        $labTestResult->save();

        return response()->json(['message' => 'Test result saved successfully!']);
    }
}
