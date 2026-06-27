<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Models\PathologyReportAccess;
use App\Models\PathologyTestComment;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\TestResultImage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PathologyReportService
{
    public function buildReportData(int $labPatientId, int $testId): array
    {
        $labPatient = LaboratoryPatient::findOrFail($labPatientId);
        $test = Test::with(['testParticulars' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($testId);

        $allPatientIds = LaboratoryPatient::where('mr_no', $labPatient->mr_no)->pluck('id');

        $historyResults = TestResult::with('testParticular')
            ->whereIn('laboratory_patient_id', $allPatientIds)
            ->where('test_id', $testId)
            ->get()
            ->groupBy('laboratory_patient_id')
            ->sortBy(fn ($results) => $results->first()->created_at);

        $testImages = TestResultImage::where('laboratory_patient_id', $labPatientId)
            ->where('test_id', $testId)
            ->get();

        $hasResults = TestResult::where('laboratory_patient_id', $labPatientId)
            ->where('test_id', $testId)
            ->exists();

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

        return compact('labPatient', 'test', 'historyResults', 'testImages', 'reportViewUrl', 'qrCodeDataUri', 'hasResults', 'testComment', 'hasRemarksPage');
    }

    public function hasRemarksPage(Test $test, Collection $historyResults, ?string $testComment): bool
    {
        if (! empty(trim((string) $testComment))) {
            return true;
        }

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

    public function getOnlineReportUrl(int $labPatientId, int $testId): string
    {
        $access = PathologyReportAccess::ensureToken($labPatientId, $testId);

        return route('pathology.online_report', $access->access_token);
    }

    public function getQrCodeDataUri(string $url): string
    {
        $svg = QrCode::format('svg')->size(140)->margin(1)->generate($url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function generatePdf(int $labPatientId, int $testId)
    {
        $data = $this->buildReportData($labPatientId, $testId);

        return Pdf::loadView('laboratory.print_report_pdf', $data)
            ->setPaper('a4');
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
        $pdfContent = Pdf::loadView('laboratory.print_report_pdf', $data)->setPaper('a4')->output();
        Storage::disk('public')->put($relativePath, $pdfContent);

        return [
            'path' => Storage::disk('public')->path($relativePath),
            'relative_path' => $relativePath,
            'filename' => $filename,
            'url' => Storage::disk('public')->url($relativePath),
        ];
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
            $query->where(function ($q) use ($labRegNo) {
                $q->where('lab_registration_no', $labRegNo)
                    ->orWhere('desktop_invoice', $labRegNo);

                if (is_numeric($labRegNo)) {
                    $q->orWhere('lab_registration_no', (string) (int) $labRegNo)
                        ->orWhere('desktop_invoice', (string) (int) $labRegNo);
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

        $completedTests = TestResult::with('test', 'laboratoryPatient')
            ->whereIn('laboratory_patient_id', $allIds)
            ->whereHas('test', fn ($q) => $q->where('category', 'Pathology'))
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
