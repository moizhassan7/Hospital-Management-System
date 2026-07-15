<?php

namespace App\Services;

use App\Models\Desktop\DesktopRegTest;
use App\Models\Desktop\DesktopTest;
use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\Test;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DesktopBookingSyncService
{
    private ?string $lastError = null;

    /** @var list<string> Desktop test names that could not be mapped during the last import. */
    private array $unmatchedTestNames = [];

    public function __construct(
        private DesktopTestCatalogSyncService $catalogSync,
        private DesktopTestParticularSyncService $particularSync
    ) {}

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isEnabled(): bool
    {
        if (!\App\Support\DesktopDatabase::isEnabled()) {
            $this->lastError = \App\Support\DesktopDatabase::getDisabledReason();

            return false;
        }

        return true;
    }

    /**
     * @return array{patient: ?LaboratoryPatient, imported: bool}
     */
    public function syncBookingByLabRegNo(string $labRegNo): array
    {
        if (!$this->isEnabled()) {
            return ['patient' => null, 'imported' => false];
        }

        $labRegNo = trim($labRegNo);

        if ($labRegNo === '') {
            return ['patient' => null, 'imported' => false];
        }

        try {
            $existing = LaboratoryPatient::where('lab_registration_no', $labRegNo)
                ->orWhere('desktop_invoice', $labRegNo)
                ->first();

            if ($existing) {
                if (!$existing->lab_registration_no) {
                    $existing->update(['lab_registration_no' => $labRegNo]);
                }

                return ['patient' => $existing, 'imported' => false];
            }

            $rows = $this->fetchBookingRowsByLabRegNo($labRegNo);

            if ($rows->isEmpty()) {
                return ['patient' => null, 'imported' => false];
            }

            $first = $this->resolvePatientRow($rows);
            $mrNo = $this->normalizeMrNo((string) ($first->Mr_No ?? ''));
            $invoice = $this->resolveInvoiceKey($first);

            $created = $this->createLaboratoryPatientFromDesktop($rows, $mrNo, $invoice, $labRegNo);

            if (!$created) {
                $this->lastError = 'Desktop booking found for Lab Reg No ' . $labRegNo
                    . ', but booked tests could not be matched to the web pathology catalog.'
                    . $this->unmatchedTestsSuffix();
            }

            return ['patient' => $created, 'imported' => (bool) $created];
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::error('Desktop booking sync by lab reg failed: ' . $e->getMessage(), [
                'lab_registration_no' => $labRegNo,
            ]);

            return ['patient' => null, 'imported' => false];
        }
    }

    /**
     * @return array{patient: ?LaboratoryPatient, imported: bool}
     */
    public function syncLatestBooking(string $mrNo): array
    {
        if (!$this->isEnabled()) {
            return ['patient' => null, 'imported' => false];
        }

        try {
            $rows = $this->fetchLatestBookingRows($mrNo);

            if ($rows->isEmpty()) {
                return ['patient' => null, 'imported' => false];
            }

            $invoice = $this->resolveInvoiceKey($rows->first());
            $normalizedMr = $this->normalizeMrNo($mrNo);

            $existing = LaboratoryPatient::where('mr_no', $normalizedMr)
                ->where('desktop_invoice', $invoice)
                ->first();

            if ($existing) {
                return ['patient' => $existing, 'imported' => false];
            }

            $created = $this->createLaboratoryPatientFromDesktop($rows, $normalizedMr, $invoice, $invoice);

            if (!$created) {
                $this->lastError = 'Desktop booking found for MR ' . $normalizedMr
                    . ', but booked tests could not be matched to the web pathology catalog. '
                    . 'Check test names in Manage Tests or logs for details.'
                    . $this->unmatchedTestsSuffix();
            }

            return ['patient' => $created, 'imported' => (bool) $created];
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::error('Desktop booking sync failed: ' . $e->getMessage(), [
                'mr_no' => $mrNo,
            ]);

            return ['patient' => null, 'imported' => false];
        }
    }

    public function fetchBookingRowsByLabRegNo(string $labRegNo): Collection
    {
        $labRegNo = trim($labRegNo);

        $rows = DesktopRegTest::query()
            ->where('id', $labRegNo)
            ->orderBy('TestNO')
            ->get();

        if ($rows->isNotEmpty()) {
            return $rows;
        }

        if (is_numeric($labRegNo)) {
            return DesktopRegTest::query()
                ->where('id', (int) $labRegNo)
                ->orderBy('TestNO')
                ->get();
        }

        return collect();
    }

    public function fetchLatestBookingRows(string $mrNo): Collection
    {
        $mrInt = $this->mrNoAsInt($mrNo);

        $query = DesktopRegTest::query();

        if ($mrInt !== null) {
            $query->where('Mr_No', $mrInt);
        } else {
            $query->whereRaw('CAST(Mr_No AS VARCHAR(50)) = ?', [$this->normalizeMrNo($mrNo)]);
        }

        $allRows = $query->orderByDesc('id')->orderByDesc('TestNO')->get();

        if ($allRows->isEmpty()) {
            return collect();
        }

        $latestInvoice = $this->resolveInvoiceKey($allRows->first());

        if (!empty($allRows->first()->id)) {
            return DesktopRegTest::query()
                ->where('id', $allRows->first()->id)
                ->orderBy('TestNO')
                ->get();
        }

        return $allRows->filter(fn ($row) => $this->resolveInvoiceKey($row) === $latestInvoice)->values();
    }

    private function resolvePatientRow(Collection $rows): object
    {
        return $rows->first(fn ($row) => !empty($row->PatientName))
            ?? $rows->first(fn ($row) => !empty($row->Mr_No))
            ?? $rows->first();
    }

    private function createLaboratoryPatientFromDesktop(Collection $rows, string $mrNo, string $invoice, string $labRegistrationNo): ?LaboratoryPatient
    {
        $first = $this->resolvePatientRow($rows);
        $selectedTests = [];
        $subTotal = 0;
        $this->unmatchedTestNames = [];

        foreach ($rows as $row) {
            $webTest = $this->resolveWebTest($row);

            if (!$webTest) {
                $unmatchedName = $this->resolveDesktopTestName($row)
                    ?? ('Test #' . ($row->test_id ?? '?'));
                $this->unmatchedTestNames[] = $unmatchedName;

                Log::warning('Desktop test could not be mapped to web catalog', [
                    'desktop_test_id' => $row->test_id ?? null,
                    'desktop_test_name' => $row->name ?? null,
                    'mr_no' => $mrNo,
                ]);
                continue;
            }

            $price = (float) ($row->test_p ?? $webTest->price ?? 0);
            $subTotal += $price;

            $selectedTests[] = [
                'id' => $webTest->id,
                'name' => $webTest->name,
                'price' => $price,
                'carry_out' => true,
                'status' => 'Pending',
                'sample_status' => LabSampleVial::STATUS_NOT_COLLECTED,
                'desktop_test_id' => $row->test_id ?? null,
                'desktop_reg_test_id' => $row->TestNO ?? null,
            ];
        }

        if (empty($selectedTests)) {
            return null;
        }

        $patientName = trim((string) ($first->PatientName ?? 'Unknown Patient'));

        return LaboratoryPatient::create([
            'mr_no' => $mrNo !== '' ? $mrNo : null,
            'lab_registration_no' => $labRegistrationNo,
            'patient_name' => $patientName,
            'gender' => $this->normalizeGender($first->Gender ?? 'Other'),
            'contact_no' => $first->MobileNo ?? null,
            'age' => $this->normalizeAge($first->Age ?? $first->Sytem_Age ?? 0),
            'file_no' => isset($first->Fil_No) ? (string) $first->Fil_No : null,
            'priority' => 'Routine',
            'self_referred' => empty($first->doctor),
            'refer_by_doctor_name' => $first->doctor ?? null,
            'desktop_invoice' => $invoice,
            'selected_tests' => $selectedTests,
            'sub_total' => $subTotal,
            'discount' => (float) ($first->dis ?? 0),
            'grand_total' => (float) ($first->total ?? $subTotal),
            'lab_share_total' => (float) ($first->Lab_Share ?? 0),
            'hospital_share_total' => (float) ($first->Hosp_share ?? 0),
            'paid_amount' => (float) ($first->paid ?? $first->total ?? $subTotal),
            'due_amount' => (float) ($first->due ?? 0),
            'previous_due' => 0,
        ]);
    }

    public function resolveWebTest(object $row): ?Test
    {
        $desktopTestId = $row->test_id ?? null;

        $byId = $this->findWebTestById($desktopTestId);

        if ($byId) {
            return $byId;
        }

        $desktopTestName = $this->resolveDesktopTestName($row);

        if ($desktopTestName) {
            $byName = $this->findWebTestByName($desktopTestName);

            if ($byName) {
                return $byName;
            }
        }

        // Self-heal: the booked test is not in the web catalog yet. Pull it
        // (and its reportable parameters) straight from the desktop catalog,
        // then use the freshly created web test.
        if ($desktopTestId !== null && is_numeric($desktopTestId)) {
            $synced = $this->catalogSync->syncSingleByDesktopId((int) $desktopTestId);

            if ($synced) {
                $this->particularSync->syncForDesktopTest((int) $desktopTestId);

                return $synced;
            }
        }

        return null;
    }

    /**
     * Match a desktop test id against the web catalog by any of the columns
     * that can hold it (desktop_test_id, the string test_id, or the primary id).
     */
    private function findWebTestById(mixed $desktopTestId): ?Test
    {
        $raw = is_string($desktopTestId) ? trim($desktopTestId) : $desktopTestId;

        if ($raw === null || $raw === '') {
            return null;
        }

        return Test::query()
            ->where('category', 'Pathology')
            ->where(function ($query) use ($raw) {
                $query->where('test_id', (string) $raw);

                if (is_numeric($raw)) {
                    $query->orWhere('desktop_test_id', (int) $raw)
                        ->orWhere('id', (int) $raw);
                }
            })
            ->first();
    }

    private function unmatchedTestsSuffix(): string
    {
        $names = array_values(array_unique(array_filter($this->unmatchedTestNames)));

        if ($names === []) {
            return '';
        }

        return ' Unmatched test(s): ' . implode(', ', $names) . '.';
    }

    public function resolveDesktopTestName(object $row): ?string
    {
        $name = trim((string) ($row->name ?? ''));

        if ($name !== '') {
            return $name;
        }

        if (empty($row->test_id)) {
            return null;
        }

        $desktopTest = DesktopTest::query()->find($row->test_id);

        return $desktopTest?->name ? trim((string) $desktopTest->name) : null;
    }

    private function findWebTestByName(string $name): ?Test
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $lower = Str::lower($name);

        $exact = Test::where('category', 'Pathology')
            ->whereRaw('LOWER(name) = ?', [$lower])
            ->first();

        if ($exact) {
            return $exact;
        }

        $partial = Test::where('category', 'Pathology')
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $lower . '%'])
            ->first();

        if ($partial) {
            return $partial;
        }

        $keywords = preg_split('/\W+/', $lower, -1, PREG_SPLIT_NO_EMPTY);
        $keywords = array_values(array_filter($keywords, fn ($word) => strlen($word) >= 4 && !in_array($word, ['test', 'with', 'panel'], true)));

        foreach ($keywords as $word) {
            $match = Test::where('category', 'Pathology')
                ->whereRaw('LOWER(name) LIKE ?', ['%' . $word . '%'])
                ->first();

            if ($match) {
                return $match;
            }
        }

        if (Str::length($lower) <= 5) {
            return Test::where('category', 'Pathology')
                ->whereRaw('LOWER(name) LIKE ?', [$lower . '%'])
                ->first();
        }

        return null;
    }

    private function resolveInvoiceKey(object $row): string
    {
        if (!empty($row->id)) {
            return (string) $row->id;
        }

        if (!empty($row->date)) {
            return 'date_' . (string) $row->date;
        }

        return 'reg_' . (string) ($row->TestNO ?? 'unknown');
    }

    private function normalizeMrNo(string $mrNo): string
    {
        $mrNo = trim($mrNo);

        if (is_numeric($mrNo)) {
            return (string) (int) $mrNo;
        }

        return $mrNo;
    }

    private function mrNoAsInt(string $mrNo): ?int
    {
        $digits = preg_replace('/\D/', '', trim($mrNo));

        return $digits !== '' ? (int) $digits : null;
    }

    private function normalizeGender(?string $gender): string
    {
        $gender = Str::lower(trim((string) $gender));

        return match (true) {
            str_contains($gender, 'f') => 'Female',
            str_contains($gender, 'm') => 'Male',
            default => 'Other',
        };
    }

    private function normalizeAge(mixed $age): int
    {
        if (is_numeric($age)) {
            return max(0, (int) $age);
        }

        if (preg_match('/(\d+)/', (string) $age, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
