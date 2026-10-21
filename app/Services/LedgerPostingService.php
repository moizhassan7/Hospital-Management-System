<?php

namespace App\Services;

use App\Models\AccountMapping;
use App\Models\DayCareProcedure;
use App\Models\Doctor;
use App\Models\EmergencyPatient;
use App\Models\FinancialAccount;
use App\Models\IndoorPatient;
use App\Models\IndoorPatientAdvance;
use App\Models\LaboratoryPatient;
use App\Models\LedgerEntry;
use App\Models\OpdAppointment;
use App\Models\PatientDischarge;
use App\Models\Voucher;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LedgerPostingService
{
    /** @var array<string, FinancialAccount> */
    private array $accounts = [];

    public function mappedAccount(string $mappingKey): FinancialAccount
    {
        if (! isset($this->accounts[$mappingKey])) {
            $mapping = AccountMapping::with('account')->where('mapping_key', $mappingKey)->first();
            if (! $mapping || ! $mapping->account || ! $mapping->account->is_active || ! $mapping->account->is_postable) {
                throw ValidationException::withMessages([
                    'account' => "Accounting mapping [{$mappingKey}] is missing or not postable.",
                ]);
            }
            $this->accounts[$mappingKey] = $mapping->account;
        }

        return $this->accounts[$mappingKey];
    }

    public function postVoucher(Voucher $voucher): void
    {
        $voucher->loadMissing(['category.account', 'paymentAccount']);
        $amount = $voucher->effective_amount;
        $narration = $voucher->category->name.($voucher->party_name ? ' - '.$voucher->party_name : '');

        if ($voucher->isCashHandover()) {
            $debit = $this->mappedAccount('hospital_cash');
            $credit = $this->mappedAccount('staff_cash');
        } elseif ($voucher->voucher_type === 'credit') {
            $debit = $voucher->paymentAccount;
            $credit = $voucher->category->account;
        } else {
            $debit = $voucher->category->account;
            $credit = $voucher->paymentAccount;
        }

        $this->sync(
            sourceType: 'voucher',
            sourceId: $voucher->id,
            date: $voucher->voucher_date,
            documentType: $voucher->accounting_type_code,
            reference: $voucher->voucher_no,
            narration: $narration,
            lines: [
                ['account_id' => $debit->id, 'entry_type' => 'debit', 'amount' => $amount],
                ['account_id' => $credit->id, 'entry_type' => 'credit', 'amount' => $amount],
            ],
            context: [
                'voucher_id' => $voucher->id,
                'party_type' => $voucher->party_type,
                'party_id' => $voucher->party_id,
                'party_name' => $voucher->party_name,
                'custodian_user_id' => $voucher->isCashHandover() ? $voucher->party_id : null,
                'created_by' => $voucher->posted_by ?: $voucher->created_by,
            ],
        );
    }

    public function syncModel(Model $model): void
    {
        match (true) {
            $model instanceof OpdAppointment => $this->syncOpd($model),
            $model instanceof IndoorPatient => $this->syncIndoorAdvance($model),
            $model instanceof IndoorPatientAdvance => $this->syncAdvanceInstallment($model),
            $model instanceof EmergencyPatient => $this->syncEmergency($model),
            $model instanceof DayCareProcedure => $this->syncDayCare($model),
            $model instanceof LaboratoryPatient => $this->syncLaboratory($model),
            $model instanceof PatientDischarge => $this->syncDischarge($model),
            default => null,
        };
    }

    public function reverseModel(Model $model, ?int $userId = null): void
    {
        $sourceType = $this->sourceType($model);
        if ($sourceType) {
            $this->reverseSource($sourceType, (int) $model->getKey(), $userId);
        }
        $doctorSource = $this->doctorSourceType($model);
        if ($doctorSource) {
            $this->reverseSource($doctorSource, (int) $model->getKey(), $userId);
        }
    }

    public function reverseSource(string $sourceType, int $sourceId, ?int $userId = null, CarbonInterface|string|null $date = null): void
    {
        DB::transaction(function () use ($sourceType, $sourceId, $userId, $date) {
            $entries = LedgerEntry::query()
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->where('is_reversal', false)
                ->whereNull('reversed_at')
                ->lockForUpdate()
                ->get();

            if ($entries->isEmpty()) {
                return;
            }

            $revision = ((int) LedgerEntry::where('source_type', $sourceType)->where('source_id', $sourceId)->max('revision')) + 1;
            $reversalDate = $this->dateString($date ?: now());

            foreach ($entries as $index => $entry) {
                LedgerEntry::create([
                    'voucher_id' => $entry->voucher_id,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'source_line' => 'r'.$revision.'-reversal-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'revision' => $revision,
                    'document_type' => 'RV',
                    'transaction_date' => $reversalDate,
                    'fiscal_year' => $this->fiscalYear($reversalDate),
                    'reference_no' => $entry->reference_no,
                    'account_id' => $entry->account_id,
                    'entry_type' => $entry->entry_type === 'debit' ? 'credit' : 'debit',
                    'amount' => $entry->amount,
                    'party_type' => $entry->party_type,
                    'party_id' => $entry->party_id,
                    'party_name' => $entry->party_name,
                    'custodian_user_id' => $entry->custodian_user_id,
                    'narration' => 'Reversal: '.$entry->narration,
                    'is_reversal' => true,
                    'reversal_of_id' => $entry->id,
                    'created_by' => $userId,
                    'posted_at' => now(),
                ]);

                $entry->update(['reversed_at' => now(), 'reversed_by' => $userId]);
            }
        });
    }

    /**
     * @param array<int, array{account_id:int, entry_type:string, amount:float|int|string}> $lines
     * @param array<string, mixed> $context
     */
    public function sync(
        string $sourceType,
        int $sourceId,
        CarbonInterface|string $date,
        string $documentType,
        ?string $reference,
        string $narration,
        array $lines,
        array $context = [],
    ): void {
        $lines = collect($lines)
            ->map(fn (array $line) => [
                'account_id' => (int) $line['account_id'],
                'entry_type' => $line['entry_type'],
                'amount' => round((float) $line['amount'], 2),
            ])
            ->filter(fn (array $line) => $line['amount'] > 0)
            ->values()
            ->all();

        if ($lines === []) {
            $this->reverseSource($sourceType, $sourceId, $context['created_by'] ?? null);

            return;
        }

        $debit = round((float) collect($lines)->where('entry_type', 'debit')->sum('amount'), 2);
        $credit = round((float) collect($lines)->where('entry_type', 'credit')->sum('amount'), 2);
        if ($debit <= 0 || abs($debit - $credit) > 0.009) {
            throw ValidationException::withMessages([
                'ledger' => 'Journal is not balanced. Debit and Credit totals must be equal.',
            ]);
        }

        DB::transaction(function () use ($sourceType, $sourceId, $date, $documentType, $reference, $narration, $lines, $context) {
            $existing = LedgerEntry::query()
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->where('is_reversal', false)
                ->whereNull('reversed_at')
                ->lockForUpdate()
                ->get();

            if ($existing->isNotEmpty() && $this->samePosting($existing, $lines)) {
                return;
            }

            if ($existing->isNotEmpty()) {
                $this->reverseSource($sourceType, $sourceId, $context['created_by'] ?? null);
            }

            $revision = ((int) LedgerEntry::where('source_type', $sourceType)->where('source_id', $sourceId)->max('revision')) + 1;
            $transactionDate = $this->dateString($date);
            foreach ($lines as $index => $line) {
                LedgerEntry::create(array_merge($context, $line, [
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'source_line' => 'r'.$revision.'-line-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'revision' => $revision,
                    'document_type' => $documentType,
                    'transaction_date' => $transactionDate,
                    'fiscal_year' => $this->fiscalYear($transactionDate),
                    'reference_no' => $reference,
                    'narration' => $narration,
                    'is_reversal' => false,
                    'posted_at' => now(),
                ]));
            }
        });
    }

    private function syncOpd(OpdAppointment $row): void
    {
        $amount = $row->collectedAmount();
        $this->postCollection(
            row: $row,
            sourceType: 'opd_appointment',
            date: $row->appointment_date,
            revenueKey: 'opd_revenue',
            amount: $amount,
            reference: $row->appointment_number,
            narration: 'OPD receipt - '.($row->patient_name ?: $row->mr_number),
            partyType: 'patient',
            partyId: null,
            partyName: $row->patient_name,
            custodianId: $row->received_by ?: $row->payment_collected_by,
        );

        $this->postDoctorAccrual(
            'doctor_share_opd', $row->id, $row->appointment_date, $row->appointment_number,
            'Doctor share accrual - OPD '.$row->patient_name,
            $amount > 0 ? $this->opdDoctorShare($row, $amount) : 0,
        );
    }

    private function syncIndoorAdvance(IndoorPatient $row): void
    {
        $this->postCollection(
            row: $row,
            sourceType: 'ipd_admission_advance',
            date: $row->registration_date ?: $row->created_at,
            revenueKey: 'patient_advance',
            amount: (float) $row->advance_fee,
            reference: $row->file_no ?: 'IPD-'.$row->id,
            narration: 'IPD admission advance - MR '.$row->mr_no,
            partyType: 'patient',
            partyId: $row->patient_id,
            partyName: $row->patient?->name,
            custodianId: $row->received_by,
        );
    }

    private function syncAdvanceInstallment(IndoorPatientAdvance $row): void
    {
        $row->loadMissing('indoorPatient.patient');
        $this->postCollection(
            row: $row,
            sourceType: 'ipd_advance_installment',
            date: $row->date,
            revenueKey: 'patient_advance',
            amount: (float) $row->amount,
            reference: $row->receipt_no ?: 'ADV-'.$row->id,
            narration: 'IPD advance installment - MR '.($row->indoorPatient?->mr_no ?: ''),
            partyType: 'patient',
            partyId: $row->indoorPatient?->patient_id,
            partyName: $row->indoorPatient?->patient?->name,
            custodianId: $row->created_by,
        );
    }

    private function syncEmergency(EmergencyPatient $row): void
    {
        $this->postCollection($row, 'emergency_receipt', $row->created_at, 'emergency_revenue', (float) $row->amount_paid,
            'EMR-'.$row->id, 'Emergency receipt - '.$row->patient_name, 'patient', null, $row->patient_name, $row->received_by);
        $this->postDoctorAccrual(
            'doctor_share_emergency', $row->id, $row->created_at, 'EMR-'.$row->id,
            'Doctor share accrual - Emergency '.$row->patient_name,
            $this->consultantDoctorShare($row->consultants, 'emergency', false),
        );
    }

    private function syncDayCare(DayCareProcedure $row): void
    {
        $row->loadMissing('patient');
        $this->postCollection($row, 'day_care_receipt', $row->created_at, 'day_care_revenue', (float) $row->amount_paid,
            'DC-'.$row->id, 'Day care receipt - MR '.$row->mr_no, 'patient', $row->patient_id, $row->patient?->name, $row->received_by);
        $this->postDoctorAccrual(
            'doctor_share_day_care', $row->id, $row->created_at, 'DC-'.$row->id,
            'Doctor share accrual - Day Care MR '.$row->mr_no,
            $this->consultantDoctorShare($row->consultants, 'normal', (bool) ($row->patient?->is_welfare ?? false)),
        );
    }

    private function syncLaboratory(LaboratoryPatient $row): void
    {
        $this->postCollection($row, 'laboratory_receipt', $row->created_at, 'laboratory_revenue', (float) $row->paid_amount,
            'LAB-'.$row->id, 'Laboratory receipt - '.$row->patient_name, 'patient', null, $row->patient_name, $row->received_by);
    }

    private function syncDischarge(PatientDischarge $row): void
    {
        $row->loadMissing('patient');
        $netBill = max(0, round((float) $row->total_bill - (float) $row->discount, 2));
        $advance = max(0, round((float) $row->advance_fee, 2));
        $cash = max(0, round((float) $row->amount_paid, 2));
        $receivable = max(0, round($netBill - $advance - $cash, 2));
        $overpayment = max(0, round($advance + $cash - $netBill, 2));

        $lines = [];
        if ($advance > 0) {
            $lines[] = ['account_id' => $this->mappedAccount('patient_advance')->id, 'entry_type' => 'debit', 'amount' => $advance];
        }
        if ($cash > 0) {
            $lines[] = ['account_id' => $this->cashAccount($row->received_by)->id, 'entry_type' => 'debit', 'amount' => $cash];
        }
        if ($receivable > 0) {
            $lines[] = ['account_id' => $this->mappedAccount('patient_receivable')->id, 'entry_type' => 'debit', 'amount' => $receivable];
        }
        if ($netBill > 0) {
            $lines[] = ['account_id' => $this->mappedAccount('ipd_revenue')->id, 'entry_type' => 'credit', 'amount' => $netBill];
        }
        if ($overpayment > 0) {
            $lines[] = ['account_id' => $this->mappedAccount('patient_advance')->id, 'entry_type' => 'credit', 'amount' => $overpayment];
        }

        $this->sync(
            sourceType: 'ipd_discharge',
            sourceId: $row->id,
            date: $row->discharge_date ?: $row->created_at,
            documentType: 'JV',
            reference: 'DIS-'.$row->id,
            narration: 'IPD discharge settlement - '.($row->patient?->name ?: 'Patient #'.$row->patient_id),
            lines: $lines,
            context: [
                'party_type' => 'patient',
                'party_id' => $row->patient_id,
                'party_name' => $row->patient?->name,
                'custodian_user_id' => $row->received_by,
                'created_by' => $row->received_by,
            ],
        );

        $this->postDoctorAccrual(
            'doctor_share_ipd', $row->id, $row->discharge_date ?: $row->created_at, 'DIS-'.$row->id,
            'Doctor share accrual - IPD discharge '.($row->patient?->name ?: 'Patient #'.$row->patient_id),
            $this->consultantDoctorShare($row->consultants, 'normal', (bool) ($row->patient?->is_welfare ?? false)),
        );
    }

    private function postDoctorAccrual(
        string $sourceType,
        int $sourceId,
        CarbonInterface|string|null $date,
        ?string $reference,
        string $narration,
        float $amount,
    ): void {
        $lines = $amount > 0 ? [
            ['account_id' => $this->mappedAccount('doctor_fee_expense')->id, 'entry_type' => 'debit', 'amount' => $amount],
            ['account_id' => $this->mappedAccount('doctor_payable')->id, 'entry_type' => 'credit', 'amount' => $amount],
        ] : [];

        $this->sync($sourceType, $sourceId, $date ?: now(), 'JV', $reference, $narration, $lines);
    }

    private function opdDoctorShare(OpdAppointment $row, ?float $base = null): float
    {
        $doctor = Doctor::where('code', $row->doctor_code)->first();
        if (! $doctor) {
            return 0.0;
        }

        $amount = $base ?? $row->collectedAmount();

        if (! in_array($doctor->employee_group, ['Procedure', 'Service'], true)) {
            return round($amount * (float) ($doctor->general_normal_percentage ?? 0) / 100, 2);
        }

        $percentage = (float) DB::table('doctor_procedure_shares')
            ->where('procedure_id', $doctor->id)
            ->sum('share_percentage');

        return round($amount * $percentage / 100, 2);
    }

    private function consultantDoctorShare(mixed $consultants, string $feeType, bool $isWelfare): float
    {
        if (is_string($consultants)) {
            $consultants = json_decode($consultants, true);
        }
        if (! is_array($consultants)) {
            return 0.0;
        }

        $share = 0.0;
        foreach ($consultants as $key => $consultant) {
            if (! is_array($consultant)) {
                continue;
            }
            $id = (int) ($consultant['id'] ?? (is_numeric($key) ? $key : 0));
            $total = (float) ($consultant['total'] ?? 0);
            if ($id <= 0 || $total <= 0) {
                continue;
            }

            $doctor = Doctor::find($id);
            if ($doctor && ! in_array($doctor->employee_group, ['Procedure', 'Service'], true)) {
                $percentage = $feeType === 'emergency'
                    ? (float) ($doctor->general_emergency_percentage ?? 0)
                    : (float) ($isWelfare ? ($doctor->welfare_normal_percentage ?? 0) : ($doctor->general_normal_percentage ?? 0));
                $share += $total * $percentage / 100;

                continue;
            }

            $percentage = (float) DB::table('doctor_procedure_shares')
                ->where('procedure_id', $id)
                ->sum('share_percentage');
            $share += $total * $percentage / 100;
        }

        return round($share, 2);
    }

    private function postCollection(
        Model $row,
        string $sourceType,
        CarbonInterface|string|null $date,
        string $revenueKey,
        float $amount,
        ?string $reference,
        string $narration,
        ?string $partyType,
        ?int $partyId,
        ?string $partyName,
        ?int $custodianId,
    ): void {
        $lines = $amount > 0 ? [
            ['account_id' => $this->cashAccount($custodianId)->id, 'entry_type' => 'debit', 'amount' => $amount],
            ['account_id' => $this->mappedAccount($revenueKey)->id, 'entry_type' => 'credit', 'amount' => $amount],
        ] : [];

        $this->sync($sourceType, (int) $row->getKey(), $date ?: now(), 'CR', $reference, $narration, $lines, [
            'party_type' => $partyType,
            'party_id' => $partyId,
            'party_name' => $partyName,
            'custodian_user_id' => $custodianId,
            'created_by' => $custodianId,
        ]);
    }

    private function cashAccount(?int $custodianId): FinancialAccount
    {
        return $this->mappedAccount($custodianId ? 'staff_cash' : 'hospital_cash');
    }

    private function sourceType(Model $model): ?string
    {
        return match (true) {
            $model instanceof OpdAppointment => 'opd_appointment',
            $model instanceof IndoorPatient => 'ipd_admission_advance',
            $model instanceof IndoorPatientAdvance => 'ipd_advance_installment',
            $model instanceof EmergencyPatient => 'emergency_receipt',
            $model instanceof DayCareProcedure => 'day_care_receipt',
            $model instanceof LaboratoryPatient => 'laboratory_receipt',
            $model instanceof PatientDischarge => 'ipd_discharge',
            default => null,
        };
    }

    private function doctorSourceType(Model $model): ?string
    {
        return match (true) {
            $model instanceof OpdAppointment => 'doctor_share_opd',
            $model instanceof EmergencyPatient => 'doctor_share_emergency',
            $model instanceof DayCareProcedure => 'doctor_share_day_care',
            $model instanceof PatientDischarge => 'doctor_share_ipd',
            default => null,
        };
    }

    private function samePosting($existing, array $lines): bool
    {
        $current = $existing->map(fn (LedgerEntry $entry) => $entry->account_id.'|'.$entry->entry_type.'|'.number_format((float) $entry->amount, 2, '.', ''))->sort()->values()->all();
        $wanted = collect($lines)->map(fn (array $line) => $line['account_id'].'|'.$line['entry_type'].'|'.number_format((float) $line['amount'], 2, '.', ''))->sort()->values()->all();

        return $current === $wanted;
    }

    private function dateString(CarbonInterface|string $date): string
    {
        return $date instanceof CarbonInterface ? $date->toDateString() : Carbon::parse($date)->toDateString();
    }

    public function fiscalYear(CarbonInterface|string $date): string
    {
        $date = $date instanceof CarbonInterface ? $date : Carbon::parse($date);
        $start = $date->month >= 7 ? $date->year : $date->year - 1;

        return $start.'-'.substr((string) ($start + 1), -2);
    }
}
