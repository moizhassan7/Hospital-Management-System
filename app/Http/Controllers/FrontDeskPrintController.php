<?php

namespace App\Http\Controllers;

use App\Services\PathologyReportService;
use Illuminate\Http\Request;

class FrontDeskPrintController extends Controller
{
    public function __construct(
        private PathologyReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $labRegNo = $request->input('lab_reg_no');
        $phone = $request->input('phone');

        $result = $this->reportService->searchCompletedTests($labRegNo, $phone);

        return view('laboratory.front_desk_print', [
            'labRegNo' => $labRegNo,
            'phone' => $phone,
            'patient' => $result['patient'],
            'registrations' => $result['registrations'],
            'completedTests' => $result['completedTests'],
        ]);
    }

    public function downloadPdf(int $labPatientId, int $testId)
    {
        $data = $this->reportService->buildReportData($labPatientId, $testId);
        $filename = sprintf(
            'Report_%s_%s.pdf',
            $data['labPatient']->mr_no ?? 'patient',
            str_replace(' ', '_', $data['test']->name)
        );

        return $this->reportService->generatePdf($labPatientId, $testId)->download($filename);
    }

    public function printReport(int $labPatientId, int $testId)
    {
        $data = $this->reportService->buildReportData($labPatientId, $testId);

        return view('laboratory.print_report', $data);
    }
}
