<?php

namespace App\Http\Controllers;

use App\Services\PathologyCriticalReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CriticalTestReportController extends Controller
{
    public function __construct(
        private PathologyCriticalReportService $criticalReportService
    ) {}

    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $report = $this->criticalReportService->buildReport($dateFrom, $dateTo);

        return view('laboratory.critical_report', array_merge($report, [
            'dateFromInput' => $dateFrom,
            'dateToInput' => $dateTo,
        ]));
    }

    public function print(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);

        $report = $this->criticalReportService->buildReport(
            $request->input('date_from'),
            $request->input('date_to')
        );

        return view('laboratory.critical_report_print', $report);
    }

    public function downloadPdf(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);

        $report = $this->criticalReportService->buildReport(
            $request->input('date_from'),
            $request->input('date_to')
        );

        $filename = sprintf(
            'Critical_Tests_%s_to_%s.pdf',
            $report['date_from']->format('Y-m-d'),
            $report['date_to']->format('Y-m-d')
        );

        return Pdf::loadView('laboratory.critical_report_print', $report)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
