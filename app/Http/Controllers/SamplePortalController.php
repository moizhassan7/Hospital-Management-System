<?php

namespace App\Http\Controllers;

use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\Test;
use App\Services\LabPatientLookupService;
use App\Services\BarcodeLabelPrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SamplePortalController extends Controller
{
    public function __construct(
        private LabPatientLookupService $patientLookup,
        private BarcodeLabelPrintService $labelPrint
    ) {}

    public function index(Request $request)
    {
        $labRegNo = $request->input('lab_reg_no');
        $patientRecord = null;
        $bookedTests = collect();
        $existingVials = collect();
        $sampleStatuses = LabSampleVial::statusOptions();
        $desktopSynced = false;
        $desktopError = null;

        if ($labRegNo) {
            $lookup = $this->patientLookup->findOrImportByLabRegNo($labRegNo);
            $desktopSynced = $lookup['imported'];
            $desktopError = $lookup['error'];
            $patientRecord = $lookup['patient'];

            if ($patientRecord) {
                $patientRecord->syncSampleStatusFromResults();
                $bookedTests = $this->getBookedPathologyTests($patientRecord);
                $existingVials = $patientRecord->sampleVials()->orderBy('vial_type')->orderBy('vial_number')->get();
            }
        }

        return view('laboratory.sample_portal', compact(
            'patientRecord',
            'bookedTests',
            'existingVials',
            'sampleStatuses',
            'labRegNo',
            'desktopSynced',
            'desktopError'
        ));
    }

    public function collectAndPrint(Request $request)
    {
        $request->validate([
            'laboratory_patient_id' => 'required|exists:laboratory_patients,id',
        ]);

        $patientRecord = LaboratoryPatient::findOrFail($request->laboratory_patient_id);
        $pendingTests = $this->getBookedPathologyTests($patientRecord)
            ->filter(fn ($t) => $this->needsCollection($t['sample_status']));

        if ($pendingTests->isEmpty()) {
            return redirect()
                ->route('pathology.sample_portal', ['lab_reg_no' => $patientRecord->lab_registration_no])
                ->withErrors(['error' => 'No pending pathology tests found for sample collection.']);
        }

        return $this->createVialsForTests(
            $patientRecord,
            $pendingTests,
            $pendingTests->pluck('id')->all()
        );
    }

    public function collectAndPrintTest(Request $request)
    {
        $request->validate([
            'laboratory_patient_id' => 'required|exists:laboratory_patients,id',
            'test_id' => 'required|integer',
        ]);

        $patientRecord = LaboratoryPatient::findOrFail($request->laboratory_patient_id);
        $testId = (int) $request->test_id;

        $bookedTests = $this->getBookedPathologyTests($patientRecord);
        $test = $bookedTests->firstWhere('id', $testId);

        if (!$test) {
            return redirect()
                ->route('pathology.sample_portal', ['lab_reg_no' => $patientRecord->lab_registration_no])
                ->withErrors(['error' => 'Test not found for this patient.']);
        }

        if (!$this->needsCollection($test['sample_status'])) {
            $vialIds = $patientRecord->sampleVials()
                ->get()
                ->filter(fn ($v) => in_array($testId, $v->test_ids ?? [], true))
                ->pluck('id');

            if ($vialIds->isNotEmpty()) {
                return redirect()->route('pathology.sample_portal.print', [
                    'laboratory_patient_id' => $patientRecord->id,
                    'vials' => $vialIds->implode(','),
                ]);
            }
        }

        $markCollected = $this->needsCollection($test['sample_status']);

        return $this->createVialsForTests(
            $patientRecord,
            collect([$test]),
            $markCollected ? [$testId] : []
        );
    }

    public function printBarcodes(Request $request, $laboratoryPatientId)
    {
        [$patientRecord, $vials] = $this->resolvePrintVials($request, $laboratoryPatientId);

        return view('laboratory.print_sample_barcodes', [
            'patientRecord' => $patientRecord,
            'vials' => $vials,
            'label' => config('hospital.label'),
        ]);
    }

    public function downloadZplLabels(Request $request, $laboratoryPatientId)
    {
        [$patientRecord, $vials] = $this->resolvePrintVials($request, $laboratoryPatientId);
        $zpl = $this->labelPrint->buildZpl($patientRecord, $vials);
        $filename = 'labels-' . ($patientRecord->lab_registration_no ?? $patientRecord->id) . '.zpl';

        return response($zpl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadTsplLabels(Request $request, $laboratoryPatientId)
    {
        [$patientRecord, $vials] = $this->resolvePrintVials($request, $laboratoryPatientId);
        $tspl = $this->labelPrint->buildTspl($patientRecord, $vials);
        $filename = 'labels-' . ($patientRecord->lab_registration_no ?? $patientRecord->id) . '.tspl';

        return response($tspl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /** @return array{0: LaboratoryPatient, 1: \Illuminate\Support\Collection<int, LabSampleVial>} */
    private function resolvePrintVials(Request $request, $laboratoryPatientId): array
    {
        $patientRecord = LaboratoryPatient::findOrFail($laboratoryPatientId);
        $vialIds = array_filter(explode(',', $request->query('vials', '')));

        $vials = LabSampleVial::where('laboratory_patient_id', $laboratoryPatientId)
            ->when(! empty($vialIds), fn ($q) => $q->whereIn('id', $vialIds))
            ->orderBy('vial_type')
            ->orderBy('vial_number')
            ->get();

        if ($vials->isEmpty()) {
            abort(404, 'No sample vials found to print.');
        }

        return [$patientRecord, $vials];
    }

    public function updateTestSampleStatus(Request $request, int $laboratory_patient_id)
    {
        $request->validate([
            'test_id' => 'required|integer',
            'sample_status' => ['required', Rule::in(array_keys(LabSampleVial::statusOptions()))],
            'lab_reg_no' => 'nullable|string',
        ]);

        $laboratoryPatient = LaboratoryPatient::findOrFail($laboratory_patient_id);
        $laboratoryPatient->updateTestSampleStatus(
            (int) $request->test_id,
            $request->sample_status
        );

        return redirect()
            ->route('pathology.sample_portal', ['lab_reg_no' => $request->lab_reg_no ?? $laboratoryPatient->lab_registration_no])
            ->with('success', 'Sample status updated successfully.');
    }

    public function updateVialStatus(Request $request, LabSampleVial $vial)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(LabSampleVial::statusOptions()))],
            'lab_reg_no' => 'nullable|string',
        ]);

        $vial->status = $request->status;
        if ($request->status === LabSampleVial::STATUS_COLLECTED && !$vial->collected_at) {
            $vial->collected_at = now();
        }
        if ($request->status === LabSampleVial::STATUS_IN_LAB && !$vial->received_in_lab_at) {
            $vial->received_in_lab_at = now();
        }
        if ($request->status === LabSampleVial::STATUS_COMPLETED && !$vial->reported_at) {
            $vial->reported_at = now();
        }
        $vial->save();

        if ($request->status === LabSampleVial::STATUS_IN_LAB) {
            $vial->laboratoryPatient?->markTestsReceivedInLab($vial->test_ids ?? []);
        } elseif ($request->status === LabSampleVial::STATUS_COLLECTED) {
            foreach ($vial->test_ids ?? [] as $testId) {
                $vial->laboratoryPatient?->updateTestSampleStatus((int) $testId, LabSampleVial::STATUS_COLLECTED);
            }
        } else {
            foreach ($vial->test_ids ?? [] as $testId) {
                $vial->laboratoryPatient?->updateTestSampleStatus((int) $testId, $request->status);
            }
        }

        $labRegNo = $request->lab_reg_no ?? $vial->laboratoryPatient?->lab_registration_no;

        return redirect()
            ->route('pathology.sample_portal', ['lab_reg_no' => $labRegNo])
            ->with('success', 'Vial status updated successfully.');
    }

    private function needsCollection(?string $status): bool
    {
        return in_array($status, [null, '', LabSampleVial::STATUS_NOT_COLLECTED], true);
    }

    private function getBookedPathologyTests(LaboratoryPatient $patientRecord)
    {
        $selectedTests = $patientRecord->getSelectedTestsArray();

        return collect($selectedTests)->filter(function ($test) {
            $isBooked = isset($test['carry_out']) && filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN);
            if (!$isBooked) {
                return false;
            }

            $testModel = Test::find($test['id']);

            return $testModel && $testModel->category === 'Pathology';
        })->map(function ($test) {
            $testModel = Test::find($test['id']);
            $sampleStatus = $test['sample_status'] ?? LabSampleVial::STATUS_NOT_COLLECTED;

            return [
                'id' => $test['id'],
                'name' => $test['name'] ?? $testModel?->name,
                'sample_vial' => $testModel?->sample_vial ?? 'General',
                'vials_required' => $testModel?->vials_required ?? 1,
                'sample_expiry_hours' => $testModel?->sample_expiry_hours ?? 24,
                'type' => $testModel?->type,
                'sample_status' => $sampleStatus,
                'sample_collected_at' => $test['sample_collected_at'] ?? null,
                'sample_received_in_lab_at' => $test['sample_received_in_lab_at'] ?? null,
                'result_reported_at' => $test['result_reported_at'] ?? $test['result_completed_at'] ?? null,
            ];
        })->values();
    }

    private function buildVialGroups($pendingTests): array
    {
        $groups = [];

        foreach ($pendingTests as $test) {
            $vialType = $test['sample_vial'] ?: 'General';
            $key = Str::slug($vialType);

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'vial_type' => $vialType,
                    'total_vials' => 0,
                    'test_ids' => [],
                    'test_names' => [],
                    'expiry_hours' => $test['sample_expiry_hours'] ?? 24,
                ];
            }

            $groups[$key]['total_vials'] += $test['vials_required'];
            $groups[$key]['test_ids'][] = $test['id'];
            $groups[$key]['test_names'][] = $test['name'];
            $groups[$key]['expiry_hours'] = min(
                $groups[$key]['expiry_hours'],
                $test['sample_expiry_hours'] ?? 24
            );
        }

        return array_values($groups);
    }

    private function createVialsForTests(LaboratoryPatient $patientRecord, $tests, array $testIdsToMark)
    {
        $vialGroups = $this->buildVialGroups($tests);
        $createdVialIds = [];

        foreach ($vialGroups as $group) {
            for ($i = 1; $i <= $group['total_vials']; $i++) {
                $barcode = $this->generateBarcode($patientRecord->id);
                $expiresAt = now()->addHours($group['expiry_hours'] ?? 24);

                $vial = LabSampleVial::create([
                    'laboratory_patient_id' => $patientRecord->id,
                    'barcode' => $barcode,
                    'vial_type' => $group['vial_type'],
                    'vial_number' => $i,
                    'test_ids' => $group['test_ids'],
                    'collected_at' => now(),
                    'expires_at' => $expiresAt,
                    'status' => LabSampleVial::STATUS_COLLECTED,
                ]);

                $createdVialIds[] = $vial->id;
            }
        }

        if (!empty($testIdsToMark)) {
            $patientRecord->markTestsSampleCollected(array_unique($testIdsToMark));
            $patientRecord->syncVialStatusesForTests();
        }

        return redirect()->route('pathology.sample_portal.print', [
            'laboratory_patient_id' => $patientRecord->id,
            'vials' => implode(',', $createdVialIds),
        ]);
    }

    private function generateBarcode(int $labPatientId): string
    {
        do {
            $barcode = 'SP' . date('ymd') . str_pad($labPatientId, 5, '0', STR_PAD_LEFT) . strtoupper(Str::random(4));
        } while (LabSampleVial::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
