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
        return $this->reportService->downloadPdfResponse($labPatientId, $testId);
    }

    public function printReport(int $labPatientId, int $testId)
    {
        $data = $this->reportService->buildReportData($labPatientId, $testId);

        return view('laboratory.print_report', $data);
    }

    public function printAllReports(Request $request)
    {
        $labRegNo = $request->input('lab_reg_no');
        $phone = $request->input('phone');

        $result = $this->reportService->searchCompletedTests($labRegNo, $phone);

        if (! $result['patient'] || $result['completedTests']->isEmpty()) {
            return redirect()
                ->route('pathology.front_desk_print', array_filter([
                    'lab_reg_no' => $labRegNo,
                    'phone' => $phone,
                ]))
                ->with('error', 'No completed tests found to print.');
        }

        $data = $this->reportService->buildAllReportsDataFromItems(
            $result['completedTests'],
            $result['patient']
        );

        $data['layout'] = $request->input('layout') === 'combined' ? 'combined' : 'separate';

        return view('laboratory.print_all_reports', $data);
    }
}
