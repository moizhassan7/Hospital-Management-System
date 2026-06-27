<?php

namespace App\Http\Controllers;

use App\Models\LabSampleVial;
use App\Models\Test;
use Illuminate\Http\Request;

class LabAttendantController extends Controller
{
    public function index(Request $request)
    {
        $lastScan = session('lab_attendant_last_scan');

        return view('laboratory.lab_attendant_scan', compact('lastScan'));
    }

    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:100',
        ]);

        $barcode = strtoupper(trim($request->barcode));

        $vial = LabSampleVial::with('laboratoryPatient')
            ->whereRaw('UPPER(barcode) = ?', [$barcode])
            ->first();

        if (!$vial) {
            return back()
                ->withInput()
                ->withErrors(['barcode' => 'Barcode not found. Please check and try again.']);
        }

        $patient = $vial->laboratoryPatient;

        if (!$patient) {
            return back()->withErrors(['barcode' => 'Patient record not found for this vial.']);
        }

        if ($vial->status === LabSampleVial::STATUS_IN_LAB) {
            return back()->with('lab_attendant_last_scan', $this->buildScanPayload($vial, $patient, 'already_received'));
        }

        if (in_array($vial->status, [LabSampleVial::STATUS_COMPLETED, LabSampleVial::STATUS_REJECTED], true)) {
            return back()->withErrors([
                'barcode' => 'This sample is already ' . LabSampleVial::statusLabel($vial->status) . ' and cannot be received again.',
            ]);
        }

        if (!in_array($vial->status, [LabSampleVial::STATUS_COLLECTED, LabSampleVial::STATUS_NOT_COLLECTED], true)) {
            return back()->withErrors([
                'barcode' => 'Sample must be collected before it can be received in lab. Current status: ' . LabSampleVial::statusLabel($vial->status),
            ]);
        }

        $vial->markReceivedInLab();
        $patient->markTestsReceivedInLab($vial->test_ids ?? []);

        $vial->refresh();

        return back()->with('lab_attendant_last_scan', $this->buildScanPayload($vial, $patient, 'received'));
    }

    private function buildScanPayload(LabSampleVial $vial, $patient, string $result): array
    {
        $testIds = $vial->test_ids ?? [];
        $tests = Test::whereIn('id', $testIds)->pluck('name', 'id');
        $selectedTests = collect($patient->getSelectedTestsArray())->keyBy('id');

        $testDetails = collect($testIds)->map(function ($id) use ($tests, $selectedTests) {
            $entry = $selectedTests->get($id, []);

            return [
                'id' => $id,
                'name' => $tests->get($id) ?? ($entry['name'] ?? 'Unknown Test'),
                'sample_status' => $entry['sample_status'] ?? LabSampleVial::STATUS_NOT_COLLECTED,
                'sample_received_in_lab_at' => $entry['sample_received_in_lab_at'] ?? null,
                'result_reported_at' => $entry['result_completed_at'] ?? null,
            ];
        })->values()->all();

        return [
            'result' => $result,
            'barcode' => $vial->barcode,
            'vial_type' => $vial->vial_type,
            'vial_number' => $vial->vial_number,
            'status' => $vial->status,
            'status_label' => LabSampleVial::statusLabel($vial->status),
            'collected_at' => $vial->collected_at?->format('d-M-Y h:i A'),
            'received_in_lab_at' => $vial->received_in_lab_at?->format('d-M-Y h:i A'),
            'reported_at' => $vial->reported_at?->format('d-M-Y h:i A'),
            'patient_name' => $patient->patient_name,
            'mr_no' => $patient->mr_no,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'tests' => $testDetails,
            'scanned_at' => now()->format('d-M-Y h:i A'),
        ];
    }
}
