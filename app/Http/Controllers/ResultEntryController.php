<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryPatient;
use App\Models\TestResult;
use App\Models\TestResultImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add this line at the top
class ResultEntryController extends Controller
{
       public function searchPatient(Request $request)
    {
        $mr_no = $request->input('mr_no');
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : null);
        $patientRecord = null;
        $pendingTests = collect();
        $testHistory = collect();

        if ($mr_no) {
            $patientRecord = LaboratoryPatient::where('mr_no', $mr_no)->orderBy('created_at', 'desc')->first();

            if ($patientRecord) {
                // Get all registration IDs for this MR number to show complete history
                $allPatientIds = LaboratoryPatient::where('mr_no', $mr_no)->pluck('id');

                // Check if selected_tests is a string and decode it
                if (is_string($patientRecord->selected_tests)) {
                    $selectedTestsArray = json_decode($patientRecord->selected_tests, true);
                } else {
                    $selectedTestsArray = $patientRecord->selected_tests;
                }
                
                // Now safely use the array to create a collection
                $pendingTests = collect($selectedTestsArray)->filter(function ($test) use ($category) {
                    $isPending = isset($test['carry_out']) && filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN) && (!isset($test['status']) || $test['status'] === 'Pending');
                    if ($category && $isPending) {
                        $testModel = \App\Models\Test::find($test['id']);
                        return $testModel && $testModel->category === $category;
                    }
                    return $isPending;
                });

                // Get test history from all registrations of this patient
                $historyQuery = TestResult::with('test', 'testParticular')
                                           ->whereIn('laboratory_patient_id', $allPatientIds);
                
                if ($category) {
                    $historyQuery->whereHas('test', function($q) use ($category) {
                        $q->where('category', $category);
                    });
                }

                $testHistory = $historyQuery->get()
                                           ->groupBy(function($item) {
                                               return $item->test_id . '_' . $item->laboratory_patient_id;
                                           });
            }
        }
        return view('laboratory.result_entry', compact('patientRecord', 'pendingTests', 'testHistory', 'category'));
    }


public function showResultForm($lab_patient_id, $test_id)
{
    $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);
    $isReadOnly = request()->routeIs('laboratory.result_entry.view');
    
    // Get the collection of all tests for this patient
    $testsCollection = $labPatient->tests(); 
    
    // Use the Collection's filter method to find the specific test
    $test = $testsCollection->firstWhere('id', '==', $test_id);

    // If the test is not found, throw a 404 exception
    if (!$test) {
        abort(404, 'Test not found for this patient.');
    }

    // Eager load test particulars
    $test->load('testParticulars');

    // Get existing results if any
    $existingResults = TestResult::where('laboratory_patient_id', $lab_patient_id)
                                 ->where('test_id', $test_id)
                                 ->get()
                                 ->pluck('result_value', 'test_particular_id');
                                 
    $testImages = TestResultImage::where('laboratory_patient_id', $lab_patient_id)
                                 ->where('test_id', $test_id)
                                 ->get();

    return view('laboratory.result_entry_form', compact('labPatient', 'test', 'isReadOnly', 'existingResults', 'testImages'));
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

            if ($request->hasFile('test_images')) {
                foreach ($request->file('test_images') as $image) {
                    $path = $image->store('test_images', 'public');
                    TestResultImage::create([
                        'laboratory_patient_id' => $lab_patient_id,
                        'test_id' => $test_id,
                        'image_path' => $path,
                    ]);
                }
            }

            // Update the status of the test in the selected_tests JSON
            $selectedTests = is_string($labPatient->selected_tests) ? json_decode($labPatient->selected_tests, true) : $labPatient->selected_tests;
            
            if (is_array($selectedTests)) {
                foreach ($selectedTests as &$test_item) {
                    if (isset($test_item['id']) && $test_item['id'] == $test_id) {
                        $test_item['status'] = 'Completed';
                        break;
                    }
                }
            }
            $labPatient->selected_tests = $selectedTests;
            $labPatient->save();

            DB::commit();
            return redirect()->route('laboratory.result_entry.search')->with('success', 'Results saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving lab result: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to save results. Please try again. Error: ' . $e->getMessage());
        }
    }

    public function printReport($lab_patient_id, $test_id)
    {
        $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);
        $test = \App\Models\Test::with('testParticulars')->findOrFail($test_id);

        // Get all registration IDs for this MR number
        $allPatientIds = LaboratoryPatient::where('mr_no', $labPatient->mr_no)->pluck('id');

        // Get all results for this test across all registrations, sorted by date
        $historyResults = TestResult::with('testParticular')
            ->whereIn('laboratory_patient_id', $allPatientIds)
            ->where('test_id', $test_id)
            ->get()
            ->groupBy('laboratory_patient_id')
            ->sortBy(function($results) {
                return $results->first()->created_at;
            });

        $testImages = TestResultImage::where('laboratory_patient_id', $lab_patient_id)
            ->where('test_id', $test_id)
            ->get();

        return view('laboratory.print_report', compact('labPatient', 'test', 'historyResults', 'testImages'));
    }
}