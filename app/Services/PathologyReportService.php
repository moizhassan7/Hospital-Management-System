<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Models\PathologyReportAccess;
use App\Models\PathologyTestComment;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\TestResultImage;
use App\Services\HospitalBrandingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PathologyReportService
{
    public function __construct(
        private HospitalBrandingService $branding
    ) {}

    public function buildReportData(int $labPatientId, int $testId): array
    {
        $labPatient = LaboratoryPatient::findOrFail($labPatientId);
        $test = Test::with([
            'testHead',
            'testParticulars' => fn ($q) => $q->orderBy('sort_order'),
        ])->findOrFail($testId);

        $relatedPatientIds = $labPatient->mr_no
            ? LaboratoryPatient::where('mr_no', $labPatient->mr_no)->pluck('id')
            : collect([$labPatient->id]);

        $allResults = TestResult::with(['testParticular', 'enteredBy'])
            ->whereIn('laboratory_patient_id', $relatedPatientIds)
            ->where('test_id', $testId)
            ->get();

        $historyResults = $allResults
            ->groupBy('laboratory_patient_id')
            ->sortBy(fn ($results) => $results->first()->created_at);

        $currentPatientResults = $allResults->where('laboratory_patient_id', $labPatientId);
        $hasResults = $currentPatientResults->isNotEmpty();

        $testImages = TestResultImage::where('laboratory_patient_id', $labPatientId)
            ->where('test_id', $testId)
            ->get();

        $reportViewUrl = null;
        $qrCodeDataUri = null;

        if ($hasResults) {
            $reportViewUrl = $this->getOnlineReportUrl($labPatientId, $testId);
            $qrCodeDataUri = $this->getQrCodeDataUri($reportViewUrl);
        }

        $testComment = PathologyTestComment::where('laboratory_patient_id', $labPatientId)
            ->where('test_id', $testId)
            ->value('comment');

        $hasRemarksPage = $this->hasRemarksPage($test, $historyResults, $testComment);
        $hasTroponinInterpretation = $this->hasTroponinHsInterpretationPage($test);
        $hormoneReferenceType = $this->getHormoneReferenceRangeType($test);
        $referenceTables = $this->getReferenceTables($test);

        $labReportDoctors = $this->branding->activeReportDoctors();

        $reportEnteredBy = $this->resolveReportEnteredBy($labPatient, $testId, $currentPatientResults);

        return compact('labPatient', 'test', 'historyResults', 'testImages', 'reportViewUrl', 'qrCodeDataUri', 'hasResults', 'testComment', 'hasRemarksPage', 'hasTroponinInterpretation', 'hormoneReferenceType', 'referenceTables', 'labReportDoctors', 'reportEnteredBy');
    }

    /**
     * @return list<int>
     */
    public function getTestIdsWithResults(int $labPatientId): array
    {
        return TestResult::query()
            ->where('laboratory_patient_id', $labPatientId)
            ->whereIn('test_id', Test::pathologyIds())
            ->distinct()
            ->orderBy('test_id')
            ->pluck('test_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array{labPatient: LaboratoryPatient, reports: list<array<string, mixed>>}
     */
    public function buildAllReportsData(int $labPatientId): array
    {
        $labPatient = LaboratoryPatient::findOrFail($labPatientId);
        $testIds = $this->getTestIdsWithResults($labPatientId);

        $reports = collect($testIds)
            ->map(fn (int $testId) => $this->buildReportData($labPatientId, $testId))
            ->filter(fn (array $report) => $report['hasResults'])
            ->sortBy(fn (array $report) => $report['test']->name)
            ->values()
            ->all();

        return compact('labPatient', 'reports');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{lab_patient_id: int, test_id: int, test_name: string, completed_at: \Illuminate\Support\Carbon, registration_date: \Illuminate\Support\Carbon}>  $completedTests
     * @return array{labPatient: LaboratoryPatient, reports: list<array<string, mixed>>}
     */
    public function buildAllReportsDataFromItems(Collection $completedTests, LaboratoryPatient $patient): array
    {
        $reports = $completedTests
            ->map(fn (array $item) => $this->buildReportData((int) $item['lab_patient_id'], (int) $item['test_id']))
            ->filter(fn (array $report) => $report['hasResults'])
            ->sortBy(fn (array $report) => $report['test']->name)
            ->values()
            ->all();

        $labPatient = $patient;

        return compact('labPatient', 'reports');
    }

    private function resolveReportEnteredBy(LaboratoryPatient $labPatient, int $testId, ?\Illuminate\Support\Collection $prefetchedResults = null): ?string
    {
        $testData = collect($labPatient->getSelectedTestsArray())->firstWhere('id', $testId);

        if (! empty($testData['result_entered_by_name'])) {
            return (string) $testData['result_entered_by_name'];
        }

        $enteredBy = ($prefetchedResults ?? TestResult::query()
            ->with('enteredBy')
            ->where('laboratory_patient_id', $labPatient->id)
            ->where('test_id', $testId)
            ->whereNotNull('entered_by_user_id')
            ->latest('id')
            ->get())
            ->whereNotNull('entered_by_user_id')
            ->sortByDesc('id')
            ->first();

        return $enteredBy?->enteredBy?->name;
    }

    public function hasRemarksPage(Test $test, Collection $historyResults, ?string $testComment): bool
    {
        foreach ($test->testParticulars as $particular) {
            if (empty(trim((string) $particular->remarks))) {
                continue;
            }

            $hasSavedResult = false;
            foreach ($historyResults as $results) {
                $saved = $results->where('test_particular_id', $particular->id)->first();
                if ($saved && $saved->result_value !== '' && $saved->result_value !== null) {
                    $hasSavedResult = true;
                    break;
                }
            }

            if ($particular->is_calculated && ! $hasSavedResult) {
                continue;
            }

            if ($hasSavedResult) {
                return true;
            }
        }

        return false;
    }

    public function hasTroponinHsInterpretationPage(Test $test): bool
    {
        $name = strtolower(preg_replace('/\s+/', ' ', trim($test->name)));

        return str_contains($name, 'troponin') && str_contains($name, 'high sensitive');
    }

    /**
     * Returns 'fsh', 'lh', or null when the printed report should show phase reference ranges.
     */
    public function getHormoneReferenceRangeType(Test $test): ?string
    {
        $name = strtolower(preg_replace('/\s+/', ' ', trim($test->name)));

        if ($name === 'fsh' || str_starts_with($name, 'fsh ') || str_contains($name, 'follicle stimulating')) {
            return 'fsh';
        }

        if ($name === 'lh' || str_starts_with($name, 'lh ') || str_contains($name, 'luteinizing')) {
            return 'lh';
        }

        return null;
    }

    /**
     * All reference/normal-value tables to print for a test: client-defined
     * tables (from Manage Test) plus built-in FSH/LH ranges for backwards
     * compatibility. Each table is { title, columns[], rows[][] }.
     *
     * @return list<array{title: string, columns: list<string>, rows: list<list<string>>}>
     */
    public function getReferenceTables(Test $test): array
    {
        $tables = $test->referenceTablesArray();

        // Built-in FSH/LH ranges are only a fallback: they apply when the test
        // has no client-defined tables, so a custom table always wins (no dupes).
        if ($tables === []) {
            $hormoneType = $this->getHormoneReferenceRangeType($test);

            if ($hormoneType !== null) {
                $builtIn = $this->hormoneReferenceTable($hormoneType);

                if ($builtIn !== null) {
                    $tables[] = $builtIn;
                }
            }
        }

        return $tables;
    }

    /**
     * Built-in FSH / LH phase reference table (kept for tests configured only by name).
     *
     * @return array{title: string, columns: list<string>, rows: list<list<string>>}|null
     */
    private function hormoneReferenceTable(string $type): ?array
    {
        $rows = match ($type) {
            'fsh' => [
                ['Follicular Phase', '2.9 - 12.0'],
                ['Ovulation Peak', '5.8 - 21.0'],
                ['Luteal Phase', '1.5 - 7.0'],
                ['Menopausal', '17.0 - 95.0'],
                ['Male', '1.7 - 12.0'],
            ],
            'lh' => [
                ['Follicular Phase', '1.5 - 8.0'],
                ['Ovulation Peak', '9.6 - 80.0'],
                ['Luteal Phase', '0.2 - 6.5'],
                ['Post Menopause Female', '8.0 - 33.0'],
                ['Male', '1.0 - 7.0'],
            ],
            default => [],
        };

        if ($rows === []) {
            return null;
        }

        return [
            'title' => 'Normal Range',
            'columns' => ['Phase', 'Normal Range'],
            'rows' => $rows,
        ];
    }

    public function getOnlineReportUrl(int $labPatientId, int $testId): string
    {
        $access = PathologyReportAccess::ensureToken($labPatientId, $testId);

        return route('pathology.online_report', $access->access_token);
    }

    public function getQrCodeDataUri(string $url): string
    {
        $svg = QrCode::format('svg')->size(100)->margin(1)->generate($url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function generatePdf(int $labPatientId, int $testId)
    {
        return Pdf::loadView('laboratory.print_report_pdf', $this->buildReportData($labPatientId, $testId))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'margin_top' => 0,
                'margin_right' => 15,
                'margin_bottom' => 15,
                'margin_left' => 15,
            ]);
    }

    public function storePdf(int $labPatientId, int $testId): array
    {
        $data = $this->buildReportData($labPatientId, $testId);
        $labPatient = $data['labPatient'];
        $test = $data['test'];

        $filename = sprintf(
            '%s_%s_%s.pdf',
            Str::slug($labPatient->mr_no ?: 'patient'),
            Str::slug($test->name),
            now()->format('Ymd_His')
        );

        $directory = 'lab_reports';
        Storage::disk('public')->makeDirectory($directory);

        $relativePath = $directory . '/' . $filename;
        $pdfContent = $this->renderPdfOutput($labPatientId, $testId);
        Storage::disk('public')->put($relativePath, $pdfContent);

        return [
            'path' => Storage::disk('public')->path($relativePath),
            'relative_path' => $relativePath,
            'filename' => $filename,
            'url' => Storage::disk('public')->url($relativePath),
        ];
    }

    /**
     * Render PDF bytes with a temporary memory bump — dompdf needs more than 128M on some hosts.
     */
    public function renderPdfOutput(int $labPatientId, int $testId): string
    {
        $previousLimit = ini_get('memory_limit');

        try {
            if ($this->parseMemoryLimitBytes($previousLimit) < 256 * 1024 * 1024) {
                ini_set('memory_limit', '256M');
            }

            return $this->generatePdf($labPatientId, $testId)->output();
        } finally {
            if ($previousLimit !== false) {
                ini_set('memory_limit', (string) $previousLimit);
            }
        }
    }

    private function parseMemoryLimitBytes(string|false $limit): int
    {
        if ($limit === false || $limit === '-1') {
            return PHP_INT_MAX;
        }

        $limit = trim($limit);
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    public function downloadPdfResponse(int $labPatientId, int $testId, ?string $filename = null)
    {
        $data = $this->buildReportData($labPatientId, $testId);
        $filename ??= sprintf(
            'Report_%s_%s.pdf',
            $data['labPatient']->mr_no ?? 'patient',
            str_replace(' ', '_', $data['test']->name)
        );

        return response($this->renderPdfOutput($labPatientId, $testId), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function searchCompletedTests(?string $labRegNo, ?string $phone, ?string $mrNo = null): array
    {
        $labRegNo = trim((string) $labRegNo);
        $phone = trim((string) $phone);
        $mrNo = trim((string) $mrNo);

        if ($labRegNo === '' && $phone === '' && $mrNo === '') {
            return [
                'patient' => null,
                'registrations' => collect(),
                'completedTests' => collect(),
            ];
        }

        $query = LaboratoryPatient::query();

        if ($labRegNo !== '') {
            $hasDesktopInvoice = \Illuminate\Support\Facades\Schema::hasColumn('laboratory_patients', 'desktop_invoice');
            $query->where(function ($q) use ($labRegNo, $hasDesktopInvoice) {
                $q->where('lab_registration_no', $labRegNo);
                if ($hasDesktopInvoice) {
                    $q->orWhere('desktop_invoice', $labRegNo);
                }

                if (is_numeric($labRegNo)) {
                    $q->orWhere('lab_registration_no', (string) (int) $labRegNo);
                    if ($hasDesktopInvoice) {
                        $q->orWhere('desktop_invoice', (string) (int) $labRegNo);
                    }
                }
            });
        } elseif ($mrNo !== '') {
            $query->where('mr_no', $mrNo);
        } else {
            $digits = preg_replace('/\D/', '', $phone);
            $query->where(function ($q) use ($phone, $digits) {
                $q->where('contact_no', 'like', '%' . $phone . '%');
                if ($digits !== '') {
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(contact_no, '-', ''), ' ', ''), '+', ''), '(', '') LIKE ?",
                        ['%' . $digits . '%']
                    );
                }
            });
        }

        $registrations = $query->orderByDesc('created_at')->get();

        if ($registrations->isEmpty()) {
            return [
                'patient' => null,
                'registrations' => collect(),
                'completedTests' => collect(),
            ];
        }

        $patient = $registrations->first();
        $allIds = $registrations->pluck('id');

        $completedTests = TestResult::with(['test:id,name', 'laboratoryPatient:id,created_at'])
            ->whereIn('laboratory_patient_id', $allIds)
            ->whereIn('test_id', Test::pathologyIds())
            ->get()
            ->groupBy(fn ($item) => $item->test_id . '_' . $item->laboratory_patient_id)
            ->map(function (Collection $results) {
                $first = $results->first();

                return [
                    'lab_patient_id' => $first->laboratory_patient_id,
                    'test_id' => $first->test_id,
                    'test_name' => $first->test->name,
                    'completed_at' => $first->created_at,
                    'registration_date' => $first->laboratoryPatient->created_at,
                ];
            })
            ->sortByDesc('completed_at')
            ->values();

        return [
            'patient' => $patient,
            'registrations' => $registrations,
            'completedTests' => $completedTests,
        ];
    }
}
