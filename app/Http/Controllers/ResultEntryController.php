<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryPatient;
use App\Models\PathologyResultAlert;
use App\Models\PathologyTestComment;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\TestResultImage;
use App\Services\PathologyReportService;
use App\Services\PathologyFormulaService;
use App\Services\WhatsAppService;
use App\Services\LabPatientLookupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultEntryController extends Controller
{
    public function __construct(
        private PathologyReportService $reportService,
        private PathologyFormulaService $formulaService,
        private WhatsAppService $whatsAppService,
        private LabPatientLookupService $patientLookup
    ) {}
       public function searchPatient(Request $request)
    {
        $labRegNo = $request->input('lab_reg_no');
        $category = 'Pathology';
        $patientRecord = null;
        $pendingTests = collect();
        $testHistory = collect();
        $desktopSynced = false;
        $desktopError = null;

        if ($labRegNo) {
            $lookup = $this->patientLookup->findOrImportByLabRegNo($labRegNo);
            $desktopSynced = $lookup['imported'];
            $desktopError = $lookup['error'];
            $patientRecord = $lookup['patient'];

            if ($patientRecord) {
                $allPatientIds = collect([$patientRecord->id]);

                if (is_string($patientRecord->selected_tests)) {
                    $selectedTestsArray = json_decode($patientRecord->selected_tests, true);
                } else {
                    $selectedTestsArray = $patientRecord->selected_tests;
                }
                
                $pendingTests = collect($selectedTestsArray)->filter(function ($test) use ($category) {
                    $isPending = isset($test['carry_out']) && filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN) && (!isset($test['status']) || $test['status'] === 'Pending');
                    if ($category && $isPending) {
                        $testModel = \App\Models\Test::find($test['id']);
                        return $testModel && $testModel->category === $category;
                    }
                    return $isPending;
                })->map(function ($test) {
                    $test['sample_status'] = $test['sample_status'] ?? \App\Models\LabSampleVial::STATUS_NOT_COLLECTED;
                    return $test;
                });

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
        return view('laboratory.result_entry', compact('patientRecord', 'pendingTests', 'testHistory', 'category', 'desktopSynced', 'desktopError', 'labRegNo'));
    }


public function showResultForm($lab_patient_id, $test_id)
{
    $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);
    $isReadOnly = request()->routeIs('laboratory.result_entry.view', 'pathology.result_entry.view');
    
    // Get the collection of all tests for this patient
    $testsCollection = $labPatient->tests(); 
    
    // Use the Collection's filter method to find the specific test
    $test = $testsCollection->firstWhere('id', '==', $test_id);

    // If the test is not found, throw a 404 exception
    if (!$test) {
        abort(404, 'Test not found for this patient.');
    }

    $test->load(['testParticulars' => fn ($q) => $q->orderBy('sort_order')]);

    $existingResults = TestResult::where('laboratory_patient_id', $lab_patient_id)
                                 ->where('test_id', $test_id)
                                 ->get()
                                 ->pluck('result_value', 'test_particular_id');

    $testComment = PathologyTestComment::where('laboratory_patient_id', $lab_patient_id)
        ->where('test_id', $test_id)
        ->value('comment');

    $testImages = TestResultImage::where('laboratory_patient_id', $lab_patient_id)
                                 ->where('test_id', $test_id)
                                 ->get();

    $hasExistingResults = $existingResults->isNotEmpty();

    $formulaParticulars = $test->testParticulars->map(function ($particular) {
        return [
            'id' => $particular->id,
            'name' => $particular->name,
            'unit' => $particular->unit,
            'key' => $particular->result_key,
            'formula' => $particular->formula,
            'is_calculated' => (bool) $particular->is_calculated,
            'min' => $particular->normal_range_min,
            'max' => $particular->normal_range_max,
            'critical_min' => $particular->critical_range_min,
            'critical_max' => $particular->critical_range_max,
        ];
    })->values();

    return view('laboratory.result_entry_form', compact(
        'labPatient', 'test', 'isReadOnly', 'existingResults', 'testImages', 'testComment', 'formulaParticulars', 'hasExistingResults'
    ));
}

    // Save the entered results
    public function saveResults(Request $request, $lab_patient_id, $test_id)
    {
        $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);

        try {
            DB::beginTransaction();

            $test = Test::with(['testParticulars' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($test_id);
            $values = [];

            foreach ($test->testParticulars as $particular) {
                if ($particular->is_calculated) {
                    continue;
                }

                $key = 'result_' . $particular->id;
                if ($request->has($key)) {
                    $values[$particular->id] = $request->input($key);
                }
            }

            $values = $this->formulaService->applyFormulas($test, $labPatient, $values);

            foreach ($test->testParticulars as $particular) {
                if ($particular->is_calculated && ! $request->boolean('include_calculated_' . $particular->id)) {
                    unset($values[$particular->id]);
                }
            }

            TestResult::where('laboratory_patient_id', $lab_patient_id)
                ->where('test_id', $test_id)
                ->delete();

            $enteredById = auth()->id();

            foreach ($values as $particularId => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                TestResult::create([
                    'laboratory_patient_id' => $lab_patient_id,
                    'test_id' => $test_id,
                    'test_particular_id' => $particularId,
                    'result_value' => $value,
                    'entered_by_user_id' => $enteredById,
                ]);
            }

            $this->saveResultAlerts($request, (int) $lab_patient_id, (int) $test_id, $test, $values);

            PathologyTestComment::updateOrCreate(
                [
                    'laboratory_patient_id' => $lab_patient_id,
                    'test_id' => $test_id,
                ],
                ['comment' => $request->input('test_comment')]
            );

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

            // Update test result + sample status
            $labPatient->markTestResultCompleted((int) $test_id, auth()->user());

            DB::commit();

            $successMessage = 'Results saved successfully!';
            try {
                $reportUrl = $this->reportService->getOnlineReportUrl((int) $lab_patient_id, (int) $test_id);
                $test = Test::findOrFail($test_id);

                if ($this->whatsAppService->sendLabResult($labPatient, $test, $reportUrl)) {
                    $successMessage .= ' WhatsApp notification sent to patient.';
                } elseif ($this->whatsAppService->isEnabled()) {
                    $successMessage .= ' Online report link ready but WhatsApp could not be sent (check phone number).';
                } else {
                    $successMessage .= ' Online report link is ready.';
                }
            } catch (\Throwable $notifyException) {
                Log::warning('Post-save notification failed: ' . $notifyException->getMessage());
                $successMessage .= ' (WhatsApp notification could not be sent.)';
            }

            return redirect()
                ->route('pathology.result_entry.search', ['lab_reg_no' => $labPatient->lab_registration_no])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving lab result: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to save results. Please try again. Error: ' . $e->getMessage());
        }
    }

    public function printReport($lab_patient_id, $test_id)
    {
        $data = $this->reportService->buildReportData((int) $lab_patient_id, (int) $test_id);

        return view('laboratory.print_report', $data);
    }

    public function downloadPdf($lab_patient_id, $test_id)
    {
        return $this->reportService->downloadPdfResponse((int) $lab_patient_id, (int) $test_id);
    }

    /**
     * @param  array<int, string|float|null>  $values
     */
    private function saveResultAlerts(Request $request, int $labPatientId, int $testId, Test $test, array $values): void
    {
        PathologyResultAlert::where('laboratory_patient_id', $labPatientId)
            ->where('test_id', $testId)
            ->delete();

        foreach ($test->testParticulars as $particular) {
            $raw = $values[$particular->id] ?? null;
            if ($raw === null || $raw === '' || ! is_numeric($raw)) {
                continue;
            }

            $numeric = (float) $raw;
            $classification = $this->formulaService->classifyValue(
                $numeric,
                $particular->normal_range_min,
                $particular->normal_range_max,
                $particular->critical_range_min,
                $particular->critical_range_max
            );

            if ($classification === null || $classification === 'normal') {
                continue;
            }

            $flag = $this->formulaService->resultFlag(
                $numeric,
                $particular->normal_range_min,
                $particular->normal_range_max
            );

            if ($classification === 'critical') {
                $doctor = trim((string) $request->input('critical_doctor_' . $particular->id, ''));
                if ($doctor === '') {
                    throw new \InvalidArgumentException(
                        'Doctor name is required for critical value on parameter: ' . $particular->name
                    );
                }

                PathologyResultAlert::create([
                    'laboratory_patient_id' => $labPatientId,
                    'test_id' => $testId,
                    'test_particular_id' => $particular->id,
                    'result_value' => (string) $raw,
                    'alert_type' => PathologyResultAlert::TYPE_CRITICAL,
                    'flag' => $flag,
                    'reported_doctor_name' => $doctor,
                    'acknowledged_by' => auth()->id(),
                ]);

                continue;
            }

            if (! $request->boolean('ack_abnormal_' . $particular->id)) {
                throw new \InvalidArgumentException(
                    'Acknowledgement required for abnormal value on parameter: ' . $particular->name
                );
            }

            PathologyResultAlert::create([
                'laboratory_patient_id' => $labPatientId,
                'test_id' => $testId,
                'test_particular_id' => $particular->id,
                'result_value' => (string) $raw,
                'alert_type' => PathologyResultAlert::TYPE_ABNORMAL,
                'flag' => $flag,
                'reported_doctor_name' => null,
                'acknowledged_by' => auth()->id(),
            ]);
        }
    }
}