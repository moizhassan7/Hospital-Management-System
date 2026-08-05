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
// use App\Services\WhatsAppService;
use App\Services\LabPatientLookupService;
use App\Support\LabPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultEntryController extends Controller
{
    public function __construct(
        private PathologyReportService $reportService,
        private PathologyFormulaService $formulaService,
        // private WhatsAppService $whatsAppService,
        private LabPatientLookupService $patientLookup
    ) {}
    public function searchPatient(Request $request)
    {
        $labRegNo = $request->input('lab_reg_no');
        $patientId = $request->input('patient_id');
        $category = 'Pathology';
        $patientRecord = null;
        $ambiguousPatients = collect();
        $pendingTests = collect();
        $testHistory = collect();

        if ($labRegNo) {
            $lookup = $this->patientLookup->findOrImportByLabRegNo($labRegNo, $patientId ? (int) $patientId : null);
            $patientRecord = $lookup['patient'];
            $ambiguousPatients = $lookup['ambiguous_patients'];
            
            if ($patientRecord && ($patientRecord->is_returned || $patientRecord->status === 'Cancelled')) {
                session()->now('error', 'This booking has been cancelled or returned. Result entry is not allowed.');
            }

            if ($patientRecord) {
                $selectedTestsArray = $patientRecord->getSelectedTestsArray();
                
                $pendingCandidates = collect($selectedTestsArray)->filter(function ($test) use ($patientRecord) {
                    if ($patientRecord->is_returned || $patientRecord->status === 'Cancelled') {
                        return false;
                    }
                    return isset($test['carry_out']) && filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN) && (!isset($test['status']) || $test['status'] === 'Pending');
                });

                $pendingTestModels = collect();
                if ($category) {
                    $pendingTestModels = Test::pathology()
                        ->whereIn('id', $pendingCandidates->pluck('id')->filter()->unique()->values())
                        ->get()
                        ->keyBy('id');
                }

                $pendingTests = $pendingCandidates->filter(function ($test) use ($category, $pendingTestModels) {
                    if (! $category) {
                        return true;
                    }

                    return $pendingTestModels->has((int) $test['id']);
                })->map(function ($test) {
                    $test['sample_status'] = $test['sample_status'] ?? \App\Models\LabSampleVial::STATUS_NOT_COLLECTED;

                    return $test;
                });

                $historyQuery = TestResult::with(['test:id,name,category', 'testParticular:id,name'])
                    ->where('laboratory_patient_id', $patientRecord->id);

                if ($category) {
                    $historyQuery->whereIn('test_id', Test::pathologyIds());
                }

                $testHistory = $historyQuery->get()
                    ->groupBy(fn ($item) => $item->test_id . '_' . $item->laboratory_patient_id);
            }
        }
        return view('laboratory.result_entry', compact('patientRecord', 'ambiguousPatients', 'pendingTests', 'testHistory', 'category', 'labRegNo'));
    }


public function showResultForm($lab_patient_id, $test_id)
{
    $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);
    $isEdit = request()->routeIs('laboratory.result_entry.edit', 'pathology.result_entry.edit');
    $isReadOnly = request()->routeIs('laboratory.result_entry.view', 'pathology.result_entry.view');

    $isBooked = collect($labPatient->getSelectedTestsArray())
        ->contains(fn ($entry) => (int) ($entry['id'] ?? 0) === (int) $test_id);

    if (! $isBooked) {
        abort(404, 'Test not found for this patient.');
    }

    if ($labPatient->is_returned || $labPatient->status === 'Cancelled') {
        abort(403, 'This booking has been cancelled or returned. Result entry is not allowed.');
    }

    $gender = $labPatient->gender;
    $test = Test::with(['testParticulars' => function ($q) use ($gender) {
        $q->where(function ($query) use ($gender) {
            $query->whereNull('patient_type')
                  ->orWhere('patient_type', '')
                  ->orWhere('patient_type', 'Not specified')
                  ->orWhere('patient_type', 'Both');
            
            if ($gender) {
                $query->orWhere('patient_type', $gender);
            }
        })->orderBy('sort_order');
    }])
        ->pathology()
        ->findOrFail($test_id);

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

    if ($isEdit) {
        if (! auth()->user()->hasPermission(LabPermissions::RESULT_EDIT)) {
            abort(403, 'You do not have permission to edit lab results.');
        }

        if (! $hasExistingResults) {
            return redirect()->route('pathology.result_entry.show_form', [
                'lab_patient_id' => $lab_patient_id,
                'test_id' => $test_id,
            ]);
        }

        $isReadOnly = false;
    }

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
        'labPatient', 'test', 'isReadOnly', 'isEdit', 'existingResults', 'testImages', 'testComment', 'formulaParticulars', 'hasExistingResults'
    ));
}

    // Save the entered results
    public function saveResults(Request $request, $lab_patient_id, $test_id)
    {
        $labPatient = LaboratoryPatient::findOrFail($lab_patient_id);

        if ($labPatient->is_returned || $labPatient->status === 'Cancelled') {
            abort(403, 'This booking has been cancelled or returned. Result entry is not allowed.');
        }

        $hasExistingResults = TestResult::where('laboratory_patient_id', $lab_patient_id)
            ->where('test_id', $test_id)
            ->exists();

        $user = auth()->user();

        if ($hasExistingResults && ! $user->hasPermission(LabPermissions::RESULT_EDIT)) {
            abort(403, 'You do not have permission to edit lab results.');
        }

        if (! $hasExistingResults && ! $user->hasPermission(LabPermissions::RESULT_ENTRY)) {
            abort(403, 'You do not have permission to enter lab results.');
        }

        try {
            DB::beginTransaction();

            $gender = $labPatient->gender;
            $test = Test::with(['testParticulars' => function ($q) use ($gender) {
                $q->where(function ($query) use ($gender) {
                    $query->whereNull('patient_type')
                          ->orWhere('patient_type', '')
                          ->orWhere('patient_type', 'Not specified')
                          ->orWhere('patient_type', 'Both');
                    
                    if ($gender) {
                        $query->orWhere('patient_type', $gender);
                    }
                })->orderBy('sort_order');
            }])->findOrFail($test_id);
            $values = [];

            foreach ($test->testParticulars as $particular) {
                if ($particular->is_calculated) {
                    continue;
                }

                $key = 'result_' . $particular->id;
                if ($request->has($key) && $this->isParticularIncludedOnReport($request, $particular)) {
                    $values[$particular->id] = $request->input($key);
                }
            }

            $values = $this->formulaService->applyFormulas($test, $labPatient, $values);

            foreach ($test->testParticulars as $particular) {
                if (! $this->isParticularIncludedOnReport($request, $particular)) {
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

            $successMessage = $hasExistingResults
                ? 'Results updated successfully!'
                : 'Results saved successfully!';

            // WhatsApp report notification — disabled for now.
            // if (! $hasExistingResults) {
            //     try {
            //         $reportUrl = $this->reportService->getOnlineReportUrl((int) $lab_patient_id, (int) $test_id);
            //         $test = Test::findOrFail($test_id);
            //
            //         if ($this->whatsAppService->sendLabResult($labPatient, $test, $reportUrl)) {
            //             $successMessage .= ' WhatsApp notification sent to patient.';
            //         } elseif ($this->whatsAppService->isEnabled()) {
            //             $successMessage .= ' Online report link ready but WhatsApp could not be sent (check phone number).';
            //         } else {
            //             $successMessage .= ' Online report link is ready.';
            //         }
            //     } catch (\Throwable $notifyException) {
            //         Log::warning('Post-save notification failed: ' . $notifyException->getMessage());
            //         $successMessage .= ' (WhatsApp notification could not be sent.)';
            //     }
            // }

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

    public function printAllReports(Request $request, $lab_patient_id)
    {
        $data = $this->reportService->buildAllReportsData((int) $lab_patient_id);

        if (empty($data['reports'])) {
            return redirect()
                ->route('pathology.result_entry.search', ['lab_reg_no' => $data['labPatient']->lab_registration_no])
                ->with('error', 'No tests with entered results found for this patient.');
        }

        $data['layout'] = $request->input('layout') === 'combined' ? 'combined' : 'separate';

        return view('laboratory.print_all_reports', $data);
    }

    public function downloadPdf($lab_patient_id, $test_id)
    {
        return $this->reportService->downloadPdfResponse((int) $lab_patient_id, (int) $test_id);
    }

    private function isParticularIncludedOnReport(Request $request, $particular): bool
    {
        if (strtolower((string) $particular->name) === 'findings') {
            return true;
        }

        return $request->boolean('include_particular_' . $particular->id);
    }

    /**
     * @return array{critical_doctor: string, abnormal_acknowledged: bool, alerts_reviewed: bool, ack_particular_ids: list<int>}
     */
    private function parseResultAlertAck(Request $request): array
    {
        $decoded = json_decode((string) $request->input('result_alert_ack', ''), true);

        if (! is_array($decoded)) {
            return [
                'critical_doctor' => trim((string) $request->input('critical_reported_doctor', '')),
                'abnormal_acknowledged' => $request->boolean('abnormal_acknowledged'),
                'alerts_reviewed' => $request->boolean('alerts_reviewed'),
                'ack_particular_ids' => [],
            ];
        }

        return [
            'critical_doctor' => trim((string) ($decoded['critical_doctor'] ?? $request->input('critical_reported_doctor', ''))),
            'abnormal_acknowledged' => (bool) ($decoded['abnormal_acknowledged'] ?? $request->boolean('abnormal_acknowledged')),
            'alerts_reviewed' => (bool) ($decoded['alerts_reviewed'] ?? $request->boolean('alerts_reviewed')),
            'ack_particular_ids' => array_values(array_unique(array_map(
                'intval',
                $decoded['ack_particular_ids'] ?? []
            ))),
        ];
    }

    /**
     * @param  array<int, string|float|null>  $values
     */
    private function saveResultAlerts(Request $request, int $labPatientId, int $testId, Test $test, array $values): void
    {
        PathologyResultAlert::where('laboratory_patient_id', $labPatientId)
            ->where('test_id', $testId)
            ->delete();

        $alertAck = $this->parseResultAlertAck($request);

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
                    $doctor = $alertAck['critical_doctor'];
                }
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

            $hasAbnormalAck = $request->boolean('ack_abnormal_' . $particular->id)
                || in_array($particular->id, $alertAck['ack_particular_ids'], true)
                || $alertAck['abnormal_acknowledged']
                || $alertAck['alerts_reviewed'];

            if (! $hasAbnormalAck) {
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