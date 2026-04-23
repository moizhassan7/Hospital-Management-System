<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryPatient;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add this line at the top
class ResultEntryController extends Controller
{
       public function searchPatient(Request $request)
    {
        $mr_no = $request->input('mr_no');
        $patientRecord = null;
        $pendingTests = collect();
        $testHistory = collect();

        if ($mr_no) {
            $patientRecord = LaboratoryPatient::where('mr_no', $mr_no)->orderBy('created_at', 'desc')->first();

            if ($patientRecord) {
                // Check if selected_tests is a string and decode it
                if (is_string($patientRecord->selected_tests)) {
                    $selectedTestsArray = json_decode($patientRecord->selected_tests, true);
                } else {
                    $selectedTestsArray = $patientRecord->selected_tests;
                }
                
                // Now safely use the array to create a collection
                $pendingTests = collect($selectedTestsArray)->filter(function ($test) {
                    return isset($test['carry_out']) && filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN) && (!isset($test['status']) || $test['status'] === 'Pending');
                });

                // Get test history from the TestResult model
                $testHistory = TestResult::with('test', 'testParticular')
                                          ->where('laboratory_patient_id', $patientRecord->id)
                                          ->get()
                                          ->groupBy('test_id');
            }
        }
        return view('laboratory.result_entry', compact('patientRecord', 'pendingTests', 'testHistory'));
    }


public function showResultForm($lab_patient_id, $test_id)
{
    $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);
    
    // Get the collection of all tests for this patient
    $testsCollection = $labPatient->tests(); 
    
    // Use the Collection's filter method to find the specific test
    $test = $testsCollection->firstWhere('id', $test_id);

    // If the test is not found, throw a 404 exception
    if (!$test) {
        // You can use a more specific exception if you want, like a 404 Not Found
        // or a custom exception.
        abort(404, 'Test not found for this patient.');
    }

    // Eager load test particulars to prevent N+1 queries
    $test->load('testParticulars');

    return view('laboratory.result_entry_form', compact('labPatient', 'test'));
}

    // Save the entered results
    public function saveResults(Request $request, $lab_patient_id, $test_id)
    {
        $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);

        try {
            DB::beginTransaction();
            // Loop through the submitted results and save them
            foreach ($request->all() as $key => $value) {
                if (str_starts_with($key, 'result_')) {
                    $testParticularId = str_replace('result_', '', $key);
                    TestResult::create([
                        'laboratory_patient_id' => $lab_patient_id,
                        'test_id' => $test_id,
                        'test_particular_id' => $testParticularId,
                        'result_value' => $value,
                    ]);
                }
            }

            // Update the status of the test in the selected_tests JSON
            $selectedTests = $labPatient->selected_tests;
            foreach ($selectedTests as &$test) {
                if ($test['id'] == $test_id) {
                    $test['status'] = 'Completed';
                    break;
                }
            }
            $labPatient->selected_tests = $selectedTests;
            $labPatient->save();

            DB::commit();
            return redirect()->route('laboratory.result_entry.search')->with('success', 'Results saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save results. Please try again.');
        }
    }
}