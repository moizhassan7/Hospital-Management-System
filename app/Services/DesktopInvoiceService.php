<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Support\DesktopDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DesktopInvoiceService
{
    /**
     * Mark desktop invoice as sample collected (IsSimpleCollect = 1).
     * Invoice id matches lab registration / desktop_invoice from the booking view.
     */
    public function markSampleCollected(LaboratoryPatient $patient): bool
    {
        if (!DesktopDatabase::isEnabled()) {
            return false;
        }

        $invoiceId = $this->resolveInvoiceId($patient);

        if ($invoiceId === null) {
            Log::warning('Desktop invoice update skipped — no invoice id on patient', [
                'laboratory_patient_id' => $patient->id,
                'lab_registration_no' => $patient->lab_registration_no,
            ]);

            return false;
        }

        try {
            $table = config('desktop_sync.sources.invoice.table', 'invoice');
            $column = config('desktop_sync.sources.invoice.columns.is_sample_collected', 'IsSimpleCollect');
            $idColumn = config('desktop_sync.sources.invoice.columns.id', 'id');

            $updated = DB::connection('desktop')
                ->table($table)
                ->where($idColumn, $invoiceId)
                ->update([$column => 1]);

            if ($updated === 0) {
                Log::warning('Desktop invoice not found for sample collected update', [
                    'invoice_id' => $invoiceId,
                    'laboratory_patient_id' => $patient->id,
                ]);

                return false;
            }

            Log::info('Desktop invoice marked sample collected', [
                'invoice_id' => $invoiceId,
                'laboratory_patient_id' => $patient->id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to update desktop invoice IsSimpleCollect: ' . $e->getMessage(), [
                'laboratory_patient_id' => $patient->id,
            ]);

            return false;
        }
    }

    private function resolveInvoiceId(LaboratoryPatient $patient): ?int
    {
        foreach ([$patient->desktop_invoice, $patient->lab_registration_no] as $value) {
            $value = trim((string) $value);

            if ($value !== '' && ctype_digit($value)) {
                return (int) $value;
            }
        }

        return null;
    }
}
