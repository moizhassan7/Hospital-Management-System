<?php

namespace App\Services;

use App\Models\LabOrder;
use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\Test;

/**
 * Builds LaboratoryPatient workflow records from synced lab_orders (web DB only).
 */
class LabOrderMaterializer
{
    /**
     * Ensure a LaboratoryPatient exists for the synced lab order.
     * Preserves historical selected_tests prices when the patient already exists.
     */
    public function materialize(LabOrder $order): ?LaboratoryPatient
    {
        $order->loadMissing('orderTests.test');

        if ($order->laboratory_patient_id) {
            $existing = LaboratoryPatient::find($order->laboratory_patient_id);

            if ($existing) {
                $this->mergeOrderIntoPatient($order, $existing);

                return $existing;
            }
        }

        $byReg = LaboratoryPatient::query()
            ->where('lab_registration_no', $order->lab_registration_no)
            ->orWhere('desktop_invoice', (string) $order->desktop_booking_id)
            ->first();

        if ($byReg) {
            $this->mergeOrderIntoPatient($order, $byReg);
            $order->update(['laboratory_patient_id' => $byReg->id]);

            return $byReg;
        }

        $selectedTests = $this->buildSelectedTests($order);

        if ($selectedTests === []) {
            return null;
        }

        $patient = LaboratoryPatient::create([
            'mr_no' => $order->mr_no,
            'lab_registration_no' => $order->lab_registration_no,
            'patient_name' => $order->patient_name,
            'gender' => $order->gender,
            'contact_no' => $order->contact_no,
            'age' => $order->age,
            'file_no' => $order->file_no,
            'priority' => $order->priority ?: 'Routine',
            'self_referred' => $order->self_referred,
            'refer_by_doctor_name' => $order->refer_by_doctor_name,
            'desktop_invoice' => (string) $order->desktop_booking_id,
            'selected_tests' => $selectedTests,
            'sub_total' => $order->sub_total,
            'discount' => $order->discount,
            'grand_total' => $order->grand_total,
            'lab_share_total' => $order->lab_share_total,
            'hospital_share_total' => $order->hospital_share_total,
            'paid_amount' => $order->paid_amount,
            'due_amount' => $order->due_amount,
            'previous_due' => 0,
            'status' => $order->status,
        ]);

        $order->update(['laboratory_patient_id' => $patient->id]);

        return $patient;
    }

    private function mergeOrderIntoPatient(LabOrder $order, LaboratoryPatient $patient): void
    {
        $existingTests = collect($patient->getSelectedTestsArray())->keyBy('id');
        $merged = [];

        foreach ($this->buildSelectedTests($order) as $testRow) {
            $testId = (int) $testRow['id'];
            $previous = $existingTests->get($testId);

            if ($previous) {
                // Keep historical price/name snapshots from the original booking.
                $testRow['price'] = $previous['price'] ?? $testRow['price'];
                $testRow['name'] = $previous['name'] ?? $testRow['name'];
                $testRow['status'] = $previous['status'] ?? $testRow['status'];
                $testRow['sample_status'] = $previous['sample_status'] ?? $testRow['sample_status'];
                $testRow['sample_collected_at'] = $previous['sample_collected_at'] ?? null;
                $testRow['sample_received_in_lab_at'] = $previous['sample_received_in_lab_at'] ?? null;
                $testRow['result_completed_at'] = $previous['result_completed_at'] ?? null;
            }

            $merged[] = $testRow;
        }

        if ($merged === []) {
            return;
        }

        $patient->update([
            'patient_name' => $order->patient_name ?: $patient->patient_name,
            'gender' => $order->gender ?: $patient->gender,
            'contact_no' => $order->contact_no ?: $patient->contact_no,
            'age' => $order->age ?: $patient->age,
            'selected_tests' => $merged,
            'grand_total' => $order->grand_total,
            'paid_amount' => $order->paid_amount,
            'due_amount' => $order->due_amount,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSelectedTests(LabOrder $order): array
    {
        $selected = [];

        foreach ($order->orderTests as $line) {
            if ($line->is_cancelled) {
                continue;
            }

            $webTest = $line->test ?? ($line->test_id ? Test::find($line->test_id) : null);

            if (!$webTest && $line->desktop_test_id) {
                $webTest = Test::query()
                    ->where('desktop_test_id', $line->desktop_test_id)
                    ->orWhere('id', $line->desktop_test_id)
                    ->first();
            }

            if (!$webTest) {
                continue;
            }

            $selected[] = [
                'id' => $webTest->id,
                'name' => $line->test_name_snapshot ?: $webTest->name,
                'price' => (float) $line->price_snapshot,
                'carry_out' => true,
                'status' => $line->status ?: 'Pending',
                'sample_status' => LabSampleVial::STATUS_NOT_COLLECTED,
                'desktop_test_id' => $line->desktop_test_id,
                'desktop_reg_test_id' => $line->desktop_line_id,
            ];
        }

        return $selected;
    }
}
