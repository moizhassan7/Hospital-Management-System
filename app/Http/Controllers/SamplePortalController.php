<?php

namespace App\Http\Controllers;

use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\LimsSample;
use App\Models\Test;
use App\Services\BarcodeLabelPrintService;
use App\Services\LabPatientLookupService;
use App\Services\Lims\LimsSampleSync;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SamplePortalController extends Controller
{
    public function __construct(
        private LabPatientLookupService $patientLookup,
        private BarcodeLabelPrintService $labelPrint,
        private LimsSampleSync $limsSampleSync,
    ) {}

    public function index(Request $request)
    {
        $labRegNo = $request->input('lab_reg_no');
        $patientRecord = null;
        $bookedTests = collect();
        $existingVials = collect();
        $collectorByVialId = collect();
        $sampleStatuses = LabSampleVial::statusOptions();
        $desktopSynced = false;
        $desktopError = null;

        if ($labRegNo) {
            $lookup = $this->patientLookup->findOrImportByLabRegNo($labRegNo);
            $desktopSynced = $lookup['imported'];
            $desktopError = $lookup['error'];
            $patientRecord = $lookup['patient'];

            if ($patientRecord) {
                $patientRecord->load('sampleVials');
                $patientRecord->syncSampleStatusFromResults();
                $bookedTests = $this->getBookedPathologyTests($patientRecord);
                $existingVials = $patientRecord->sampleVials()->orderBy('vial_type')->orderBy('vial_number')->get();

                $vialIds = $existingVials->pluck('id')->all();
                if ($vialIds !== []) {
                    $collectorByVialId = LimsSample::withoutGlobalScopes()
                        ->whereIn('lab_sample_vial_id', $vialIds)
                        ->get(['lab_sample_vial_id', 'collected_by_name', 'received_by_name'])
                        ->keyBy('lab_sample_vial_id');
                }
            }
        }

        return view('laboratory.sample_portal', compact(
            'patientRecord',
            'bookedTests',
            'existingVials',
            'sampleStatuses',
            'labRegNo',
            'desktopSynced',
            'desktopError',
            'collectorByVialId'
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
            $pendingTests->pluck('id')->all(),
            false
        );
    }

    public function updateCollectionType(Request $request, LaboratoryPatient $laboratoryPatient)
    {
        $request->validate([
            'collection_type' => ['required', Rule::in(['taken_in_lab', 'brought_to_lab'])],
        ]);

        $laboratoryPatient->update(['collection_type' => $request->collection_type]);

        return redirect()
            ->route('pathology.sample_portal', ['lab_reg_no' => $laboratoryPatient->lab_registration_no])
            ->with('success', 'Collection type updated.');
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

        $vialType = $test['sample_vial'] ?: 'General';
        $sameVialTests = $bookedTests->filter(
            fn ($t) => ($t['sample_vial'] ?: 'General') === $vialType
        );

        if (!$this->needsCollection($test['sample_status'])) {
            $vialIds = $patientRecord->sampleVials()
                ->orderBy('vial_type')
                ->orderBy('vial_number')
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

        $testIdsToMark = $sameVialTests
            ->filter(fn ($t) => $this->needsCollection($t['sample_status']))
            ->pluck('id')
            ->all();

        return $this->createVialsForTests(
            $patientRecord,
            $sameVialTests,
            $testIdsToMark
        );
    }

    public function printBarcodes(Request $request, $laboratoryPatientId)
    {
        [$patientRecord, $vials] = $this->resolvePrintVials($request, $laboratoryPatientId);

        return response()
            ->view('laboratory.print_sample_barcodes', [
                'patientRecord' => $patientRecord,
                'labelRows' => $this->labelPrint->buildLabelRows($patientRecord, $vials),
                'label' => config('hospital.label'),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
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

        // Dual-write to lims_samples (additive; never blocks Sample Portal)
        if (in_array($request->status, [
            LabSampleVial::STATUS_COLLECTED,
            LabSampleVial::STATUS_IN_LAB,
            LabSampleVial::STATUS_REJECTED,
            LabSampleVial::STATUS_EXPIRED,
            LabSampleVial::STATUS_PROCESSING,
            LabSampleVial::STATUS_COMPLETED,
        ], true)) {
            $this->limsSampleSync->syncQuietly($vial->fresh(), $request->user());
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
        $selectedTests = collect($patientRecord->getSelectedTestsArray());

        $bookedTests = $selectedTests->filter(function ($test) {
            return isset($test['carry_out']) && filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN);
        });

        $testModels = Test::whereIn('id', $bookedTests->pluck('id')->filter()->unique()->values())
            ->get()
            ->keyBy('id');

        return $bookedTests->filter(function ($test) use ($testModels) {
            $testModel = $testModels->get((int) $test['id']);

            return $testModel && $testModel->category === 'Pathology';
        })->map(function ($test) use ($testModels) {
            $testModel = $testModels->get((int) $test['id']);
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
        $slots = [];
        $typeMaxNumber = [];
        $typeTests = [];

        foreach ($pendingTests as $test) {
            $vialStr = trim((string) ($test['sample_vial'] ?: 'General'));

            if ($this->hasMultipleVialTypes($vialStr)) {
                foreach ($this->resolveVialRequirements($test) as $requirement) {
                    $this->addVialSlot($slots, $requirement, $test);
                }

                continue;
            }

            $typeMaxNumber[$vialStr] = max(
                $typeMaxNumber[$vialStr] ?? 0,
                max(1, (int) ($test['vials_required'] ?? 1))
            );
            $typeTests[$vialStr][] = $test;
        }

        foreach ($typeMaxNumber as $vialType => $maxNumber) {
            $testsForType = $typeTests[$vialType] ?? [];

            for ($vialNumber = 1; $vialNumber <= $maxNumber; $vialNumber++) {
                foreach ($testsForType as $test) {
                    $this->addVialSlot($slots, [
                        'vial_type' => $vialType,
                        'vial_number' => $vialNumber,
                    ], $test);
                }
            }
        }

        return array_values(array_map(function (array $slot) {
            $slot['test_ids'] = array_values(array_unique($slot['test_ids']));
            $slot['test_names'] = array_values(array_unique($slot['test_names']));

            return $slot;
        }, $slots));
    }

    private function hasMultipleVialTypes(string $vialStr): bool
    {
        return str_contains($vialStr, ',');
    }

    /**
     * @return array<int, array{vial_type: string, vial_number: int}>
     */
    private function resolveVialRequirements(array $test): array
    {
        $vialStr = trim((string) ($test['sample_vial'] ?: 'General'));

        if ($this->hasMultipleVialTypes($vialStr)) {
            $requirements = [];
            $typeCounts = [];

            foreach (array_values(array_filter(array_map('trim', explode(',', $vialStr)))) as $vialType) {
                $typeCounts[$vialType] = ($typeCounts[$vialType] ?? 0) + 1;
                $requirements[] = [
                    'vial_type' => $vialType,
                    'vial_number' => $typeCounts[$vialType],
                ];
            }

            return $requirements;
        }

        $count = max(1, (int) ($test['vials_required'] ?? 1));
        $requirements = [];

        for ($vialNumber = 1; $vialNumber <= $count; $vialNumber++) {
            $requirements[] = [
                'vial_type' => $vialStr,
                'vial_number' => $vialNumber,
            ];
        }

        return $requirements;
    }

    private function vialSlotKey(string $vialType, int $vialNumber): string
    {
        return Str::slug($vialType) . '|' . $vialNumber;
    }

    private function addVialSlot(array &$slots, array $requirement, array $test): void
    {
        $key = $this->vialSlotKey($requirement['vial_type'], $requirement['vial_number']);

        if (! isset($slots[$key])) {
            $slots[$key] = [
                'vial_type' => $requirement['vial_type'],
                'vial_number' => $requirement['vial_number'],
                'test_ids' => [],
                'test_names' => [],
                'expiry_hours' => $test['sample_expiry_hours'] ?? 24,
            ];
        }

        $slots[$key]['test_ids'][] = $test['id'];
        $slots[$key]['test_names'][] = $test['name'];
        $slots[$key]['expiry_hours'] = min(
            $slots[$key]['expiry_hours'],
            $test['sample_expiry_hours'] ?? 24
        );
    }

    private function createVialsForTests(LaboratoryPatient $patientRecord, $tests, array $testIdsToMark, bool $redirectsToPrint = true)
    {
        $vialGroups = $this->buildVialGroups($tests);
        $allBookedByVialType = $this->getBookedPathologyTests($patientRecord)
            ->groupBy(fn ($t) => $t['sample_vial'] ?: 'General');

        foreach ($vialGroups as &$group) {
            $allForType = $allBookedByVialType
                ->get($group['vial_type'], collect())
                ->pluck('id')
                ->all();
            $group['test_ids'] = array_values(array_unique(array_merge($group['test_ids'], $allForType)));
        }
        unset($group);

        $createdVialIds = [];
        $markingCollected = !empty($testIdsToMark);

        foreach ($vialGroups as $group) {
            $existingVial = $patientRecord->sampleVials()
                ->where('vial_type', $group['vial_type'])
                ->where('vial_number', $group['vial_number'])
                ->first();

            $expiresAt = now()->addHours($group['expiry_hours'] ?? 24);

            if ($existingVial) {
                $existingVial->test_ids = array_values(array_unique(array_merge(
                    $existingVial->test_ids ?? [],
                    $group['test_ids']
                )));

                if ($existingVial->expires_at === null || $expiresAt->lt($existingVial->expires_at)) {
                    $existingVial->expires_at = $expiresAt;
                }

                if ($markingCollected) {
                    if (!$existingVial->collected_at) {
                        $existingVial->collected_at = now();
                    }
                    if ($existingVial->status === LabSampleVial::STATUS_NOT_COLLECTED) {
                        $existingVial->status = LabSampleVial::STATUS_COLLECTED;
                    }
                }

                $existingVial->save();
                $createdVialIds[] = $existingVial->id;

                if ($markingCollected) {
                    $this->limsSampleSync->syncQuietly($existingVial->fresh(), auth()->user());
                }

                continue;
            }

            $vial = LabSampleVial::create([
                'laboratory_patient_id' => $patientRecord->id,
                'barcode' => $this->labelPrint->generateBarcode($patientRecord->id, $group['vial_number']),
                'vial_type' => $group['vial_type'],
                'vial_number' => $group['vial_number'],
                'test_ids' => $group['test_ids'],
                'collected_at' => $markingCollected ? now() : null,
                'expires_at' => $expiresAt,
                'status' => $markingCollected ? LabSampleVial::STATUS_COLLECTED : LabSampleVial::STATUS_NOT_COLLECTED,
            ]);

            $createdVialIds[] = $vial->id;

            if ($markingCollected) {
                $this->limsSampleSync->syncQuietly($vial, auth()->user());
            }
        }

        if ($markingCollected) {
            $patientRecord->markTestsSampleCollected(array_unique($testIdsToMark));
            $patientRecord->syncVialStatusesForTests();
        }

        if (!$redirectsToPrint) {
            return redirect()
                ->route('pathology.sample_portal', ['lab_reg_no' => $patientRecord->lab_registration_no])
                ->with('success', 'All pending samples marked as collected successfully.');
        }

        return redirect()->route('pathology.sample_portal.print', [
            'laboratory_patient_id' => $patientRecord->id,
            'vials' => implode(',', $createdVialIds),
        ]);
    }

}
