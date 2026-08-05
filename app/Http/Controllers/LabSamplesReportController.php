<?php

namespace App\Http\Controllers;

use App\Models\LabSampleVial;
use App\Models\CollectionCenter;
use App\Services\LabSamplesReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LabSamplesReportController extends Controller
{
    public function __construct(
        private LabSamplesReportService $samplesReportService
    ) {}

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'tests');
        if (! in_array($tab, ['tests', 'vials'], true)) {
            $tab = 'tests';
        }

        $filters = [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
            'lab_reg' => $request->input('lab_reg'),
            'mr' => $request->input('mr'),
            'name' => $request->input('name'),
            'barcode' => $request->input('barcode'),
            'sample_status' => $request->input('sample_status', 'all'),
            'result_status' => $request->input('result_status', 'all'),
            'collection_center_id' => $request->input('collection_center_id'),
            'page' => (int) $request->input('page', 1),
            'vpage' => (int) $request->input('vpage', 1),
        ];

        $report = $this->samplesReportService->buildReport($filters);
        $sampleStatuses = LabSampleVial::statusOptions();
        $collectionCenters = CollectionCenter::orderBy('name')->get();

        return view('laboratory.lab_samples_report', array_merge($report, [
            'tab' => $tab,
            'filters' => $filters,
            'sampleStatuses' => $sampleStatuses,
            'collectionCenters' => $collectionCenters,
        ]));
    }

    public function print(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);

        $filters = $request->only([
            'date_from', 'date_to', 'lab_reg', 'mr', 'name', 'barcode', 'sample_status', 'result_status', 'collection_center_id',
        ]);
        $filters['page'] = 1;
        $filters['vpage'] = 1;

        $report = $this->samplesReportService->buildReport($filters, 10000);
        $report['tab'] = $request->input('tab', 'tests');

        return view('laboratory.lab_samples_report_print', $report);
    }

    public function downloadPdf(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);

        $filters = $request->only([
            'date_from', 'date_to', 'lab_reg', 'mr', 'name', 'barcode', 'sample_status', 'result_status', 'collection_center_id',
        ]);
        $filters['page'] = 1;
        $filters['vpage'] = 1;

        $report = $this->samplesReportService->buildReport($filters, 10000);
        $report['tab'] = $request->input('tab', 'tests');

        $filename = sprintf(
            'Lab_Samples_Report_%s_to_%s.pdf',
            $report['date_from']->format('Y-m-d'),
            $report['date_to']->format('Y-m-d')
        );

        return Pdf::loadView('laboratory.lab_samples_report_print', $report)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
