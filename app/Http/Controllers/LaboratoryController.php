<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Test;
use App\Models\TestHead;
use App\Models\LaboratoryPatient;
use Illuminate\Validation\ValidationException;

class LaboratoryController extends Controller
{
    /**
     * Display the laboratory patient registration form with dynamic data.
     *
     * @return \Illuminate\View\View
     */
    public function showPatientRegistration()
    {
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : null);
        $doctors = Doctor::all();
        $query = Test::query();
        if ($category) {
            $query->where('category', $category);
        }
        $tests = $query->get();

        return view('laboratory.patient_registration', compact('doctors', 'tests', 'category'));
    }

    /**
     * Search for a patient by MR No.
     *
     * @param  string  $mr_no
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatientByMrNo($mr_no)
    {
        $patient = Patient::where('mr_number', $mr_no)->first();
        return response()->json($patient);
    }

    /**
     * Store a new patient registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLabRegistration(Request $request)
    {
        try {
            // Validate the request data.
            $validatedData = $request->validate([
                'mr_no' => 'nullable|string|max:255',
                'patient_name' => 'required|string|max:255',
                'gender' => 'required|string|in:Male,Female,Other',
                'contact_no' => 'nullable|string|max:255',
                'age' => 'required|integer|min:0',
                'file_no' => 'nullable|string|max:255',
                'priority' => 'required|string|in:Routine,Urgent,STAT',
                'self_referred' => 'nullable|boolean',
                'refer_by_doctor_name' => 'nullable|string|max:255',
                'tests' => 'required|json', // Validate 'tests' as a JSON string
                'sub_total' => 'required|numeric',
                'discount' => 'nullable|numeric|min:0',
                'grand_total' => 'required|numeric',
                'paid_amount' => 'nullable|numeric|min:0',
                'due_amount' => 'required|numeric',
                'previous_due' => 'nullable|numeric|min:0',
            ]);

            // Decode the JSON string back into a PHP array
            $selectedTests = json_decode($validatedData['tests'], true);
            
            // Transform the `carry_out` string to a boolean for the database.
            $selectedTestsWithBoolean = collect($selectedTests)->map(function ($test) {
                $test['carry_out'] = filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN);
                return $test;
            })->values()->all();

            // Create a new patient registration record.
            $labPatient = LaboratoryPatient::create([
                'mr_no' => $validatedData['mr_no'],
                'patient_name' => $validatedData['patient_name'],
                'gender' => $validatedData['gender'],
                'contact_no' => $validatedData['contact_no'],
                'age' => $validatedData['age'],
                'file_no' => $validatedData['file_no'],
                'priority' => $validatedData['priority'],
                'self_referred' => $request->has('self_referred'),
                'refer_by_doctor_name' => $validatedData['refer_by_doctor_name'],
                'selected_tests' => json_encode($selectedTestsWithBoolean),
                'sub_total' => $validatedData['sub_total'],
                'discount' => $validatedData['discount'],
                'grand_total' => $validatedData['grand_total'],
                'lab_share_total' => 0,
                'hospital_share_total' => 0,
                'paid_amount' => $validatedData['paid_amount'],
                'due_amount' => $validatedData['due_amount'],
                'previous_due' => $validatedData['previous_due'],
            ]);

            // Determine redirect route based on tests' category
            $firstTestId = collect($selectedTests)->first()['id'] ?? null;
            $category = 'Pathology';
            if ($firstTestId) {
                $category = Test::find($firstTestId)->category ?? 'Pathology';
            }
            
            $route = $category == 'Radiology' ? 'radiology.patient_registration' : 'pathology.patient_registration';
            return redirect()->route($route)->with('success', 'Patient registration saved successfully!');
            
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }
    /**
     * Display a comprehensive catalog of all tests and their particulars.
     *
     * @return \Illuminate\View\View
     */
    public function showTestCatalog()
    {
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : null);
        $query = TestHead::with(['tests' => function($q) use ($category) {
            if ($category) {
                $q->where('category', $category);
            }
            $q->with('testParticulars');
        }]);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        $testHeads = $query->get();
        return view('laboratory.test_catalog', compact('testHeads', 'category'));
    }
}
