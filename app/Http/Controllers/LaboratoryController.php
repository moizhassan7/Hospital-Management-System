<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Test;
use App\Models\TestHead;
use App\Models\TestParticular;
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
        $category = 'Pathology';
        $doctors = Doctor::all();
        $tests = Test::where('category', 'Pathology')->get();

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
                $test['sample_status'] = \App\Models\LabSampleVial::STATUS_NOT_COLLECTED;
                $test['status'] = $test['status'] ?? 'Pending';
                return $test;
            })->values()->all();

            // Create a new patient registration record.
            $labPatient = LaboratoryPatient::create([
                'mr_no' => $validatedData['mr_no'],
                'lab_registration_no' => LaboratoryPatient::generateLabRegistrationNo(),
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

            return redirect()->route('pathology.patient_registration')->with('success', 'Patient registration saved successfully!');
            
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }
    /**
     * Display a comprehensive catalog of all tests and their particulars.
     *
     * @return \Illuminate\View\View
     */
    public function showTestCatalog(Request $request)
    {
        $category = 'Pathology';

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'test_head_id' => $request->query('test_head_id'),
            'priority' => $request->query('priority'),
            'type' => $request->query('type'),
            'particulars' => (string) $request->query('particulars', ''),
        ];

        $allTestHeads = TestHead::where('category', $category)->orderBy('name')->get();

        $stats = [
            'heads' => $allTestHeads->count(),
            'tests' => Test::where('category', $category)->count(),
            'particulars' => TestParticular::whereHas('test', fn ($q) => $q->where('category', $category))->count(),
        ];

        $filterTypes = Test::where('category', $category)->distinct()->orderBy('type')->pluck('type');
        $filterPriorities = Test::where('category', $category)->distinct()->orderBy('priority')->pluck('priority');

        $testHeadsQuery = TestHead::query()
            ->where('category', $category)
            ->with(['tests' => function ($q) use ($filters, $category) {
                $q->where('category', $category)
                    ->with(['testParticulars' => fn ($pq) => $pq->orderBy('sort_order')->orderBy('id')])
                    ->when(filled($filters['priority']), fn ($query) => $query->where('priority', $filters['priority']))
                    ->when(filled($filters['type']), fn ($query) => $query->where('type', $filters['type']))
                    ->when($filters['particulars'] === 'with', fn ($query) => $query->has('testParticulars'))
                    ->when($filters['particulars'] === 'without', fn ($query) => $query->doesntHave('testParticulars'))
                    ->when($filters['q'] !== '', function ($query) use ($filters) {
                        $term = '%' . $filters['q'] . '%';
                        $query->where(function ($sub) use ($term) {
                            $sub->where('name', 'like', $term)
                                ->orWhereHas('testParticulars', fn ($pq) => $pq
                                    ->where('name', 'like', $term)
                                    ->orWhere('reference_text', 'like', $term)
                                    ->orWhere('unit', 'like', $term));
                        });
                    })
                    ->orderBy('name');
            }])
            ->orderBy('name');

        if ($filters['test_head_id']) {
            $testHeadsQuery->where('id', $filters['test_head_id']);
        }

        $testHeads = $testHeadsQuery->get()->filter(fn ($head) => $head->tests->isNotEmpty())->values();
        $filteredTestsCount = $testHeads->sum(fn ($head) => $head->tests->count());
        $hasActiveFilters = collect($filters)->filter(fn ($value, $key) => $key === 'particulars' ? $value !== '' : filled($value))->isNotEmpty();

        return view('laboratory.test_catalog', compact(
            'testHeads',
            'category',
            'allTestHeads',
            'filters',
            'stats',
            'filterTypes',
            'filterPriorities',
            'filteredTestsCount',
            'hasActiveFilters'
        ));
    }
}
