<?php

namespace App\Http\Controllers;

use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SamplePortalController extends Controller
{
    public function index(Request $request)
    {
        $mrNo = $request->input('mr_no');
        $patientRecord = null;
        $pendingTests = collect();
        $existingVials = collect();
        $vialSummary = collect();

        if ($mrNo) {
            $patientRecord = LaboratoryPatient::where('mr_no', $mrNo)
                ->orderByDesc('created_at')
                ->first();

            if ($patientRecord) {
                $pendingTests = $this->getPendingPathologyTests($patientRecord);
                $existingVials = $patientRecord->sampleVials()->orderBy('vial_type')->orderBy('vial_number')->get();
                $vialSummary = $this->calculateVialSummary($pendingTests);
            }
        }

        return view('laboratory.sample_portal', compact(
            'patientRecord',
            'pendingTests',
            'existingVials',
            'vialSummary',
            'mrNo'
        ));
    }

    public function collectAndPrint(Request $request)
    {
        $request->validate([
            'laboratory_patient_id' => 'required|exists:laboratory_patients,id',
        ]);

        $patientRecord = LaboratoryPatient::findOrFail($request->laboratory_patient_id);
        $pendingTests = $this->getPendingPathologyTests($patientRecord);

        if ($pendingTests->isEmpty()) {
            return redirect()
                ->route('pathology.sample_portal', ['mr_no' => $patientRecord->mr_no])
                ->withErrors(['error' => 'No pending pathology tests found for sample collection.']);
        }

        $vialGroups = $this->buildVialGroups($pendingTests);
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
                    'status' => 'collected',
                ]);

                $createdVialIds[] = $vial->id;
            }
        }

        return redirect()->route('pathology.sample_portal.print', [
            'laboratory_patient_id' => $patientRecord->id,
            'vials' => implode(',', $createdVialIds),
        ]);
    }

    public function printBarcodes(Request $request, $laboratoryPatientId)
    {
        $patientRecord = LaboratoryPatient::findOrFail($laboratoryPatientId);
        $vialIds = array_filter(explode(',', $request->query('vials', '')));

        $vials = LabSampleVial::where('laboratory_patient_id', $laboratoryPatientId)
            ->when(!empty($vialIds), fn ($q) => $q->whereIn('id', $vialIds))
            ->orderBy('vial_type')
            ->orderBy('vial_number')
            ->get();

        if ($vials->isEmpty()) {
            abort(404, 'No sample vials found to print.');
        }

        return view('laboratory.print_sample_barcodes', compact('patientRecord', 'vials'));
    }

    private function getPendingPathologyTests(LaboratoryPatient $patientRecord)
    {
        $selectedTests = is_string($patientRecord->selected_tests)
            ? json_decode($patientRecord->selected_tests, true)
            : $patientRecord->selected_tests;

        return collect($selectedTests ?? [])->filter(function ($test) {
            $isPending = isset($test['carry_out'])
                && filter_var($test['carry_out'], FILTER_VALIDATE_BOOLEAN)
                && (!isset($test['status']) || $test['status'] === 'Pending');

            if (!$isPending) {
                return false;
            }

            $testModel = Test::find($test['id']);

            return $testModel && $testModel->category === 'Pathology';
        })->map(function ($test) {
            $testModel = Test::find($test['id']);

            return [
                'id' => $test['id'],
                'name' => $test['name'] ?? $testModel?->name,
                'sample_vial' => $testModel?->sample_vial ?? 'General',
                'vials_required' => $testModel?->vials_required ?? 1,
                'sample_expiry_hours' => $testModel?->sample_expiry_hours ?? 24,
                'type' => $testModel?->type,
            ];
        })->values();
    }

    private function calculateVialSummary($pendingTests)
    {
        return collect($this->buildVialGroups($pendingTests));
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

    private function generateBarcode(int $labPatientId): string
    {
        do {
            $barcode = 'SP' . date('ymd') . str_pad($labPatientId, 5, '0', STR_PAD_LEFT) . strtoupper(Str::random(4));
        } while (LabSampleVial::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
