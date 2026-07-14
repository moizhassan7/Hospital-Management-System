<?php

namespace App\Http\Controllers;

use App\Services\LabFinancialSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LabFinancialSummaryController extends Controller
{
    public function __construct(
        private LabFinancialSummaryService $financialSummaryService
    ) {}

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'patients');
        if (! in_array($tab, ['patients', 'by_test', 'rate_list'], true)) {
            $tab = 'patients';
        }

        $filters = [
            'date_from' => $request->input('date_from', now()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
            'lab_reg' => $request->input('lab_reg'),
            'mr' => $request->input('mr'),
            'name' => $request->input('name'),
            'invoice' => $request->input('invoice'),
            'test_name' => $request->input('test_name'),
            'test_head_id' => $request->input('test_head_id'),
            'payment_status' => $request->input('payment_status', 'all'),
            'page' => (int) $request->input('page', 1),
            'tpage' => (int) $request->input('tpage', 1),
            'rpage' => (int) $request->input('rpage', 1),
        ];

        if ($tab === 'rate_list') {
            $report = $this->financialSummaryService->buildRateList($filters);

            return view('laboratory.lab_financial_summary', array_merge($report, [
                'tab' => $tab,
                'filters' => $filters,
                'date_from' => now(),
                'date_to' => now(),
                'patient_rows' => $this->emptyPaginator('page'),
                'test_rows' => $this->emptyPaginator('tpage'),
            ]));
        }

        $report = $this->financialSummaryService->buildReport($filters);

        return view('laboratory.lab_financial_summary', array_merge($report, [
            'tab' => $tab,
            'filters' => $filters,
            'rate_rows' => $this->emptyPaginator('rpage'),
            'test_heads' => collect(),
        ]));
    }

    public function print(Request $request)
    {
        $tab = $request->input('tab', 'patients');
        if (! in_array($tab, ['patients', 'by_test', 'rate_list'], true)) {
            $tab = 'patients';
        }

        if ($tab === 'rate_list') {
            $filters = $request->only(['test_name', 'test_head_id']);
            $filters['rpage'] = 1;
            $report = $this->financialSummaryService->buildRateList($filters, 10000);
            $report['tab'] = $tab;
            $report['date_from'] = now();
            $report['date_to'] = now();
            $report['patient_rows'] = collect();
            $report['test_rows'] = collect();

            return view('laboratory.lab_financial_summary_print', $report);
        }

        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);

        $filters = $request->only([
            'date_from', 'date_to', 'lab_reg', 'mr', 'name', 'invoice', 'test_name', 'payment_status',
        ]);
        $filters['page'] = 1;
        $filters['tpage'] = 1;

        $report = $this->financialSummaryService->buildReport($filters, 10000);
        $report['tab'] = $tab;
        $report['rate_rows'] = collect();

        return view('laboratory.lab_financial_summary_print', $report);
    }

    public function downloadPdf(Request $request)
    {
        $tab = $request->input('tab', 'patients');
        if (! in_array($tab, ['patients', 'by_test', 'rate_list'], true)) {
            $tab = 'patients';
        }

        if ($tab === 'rate_list') {
            $filters = $request->only(['test_name', 'test_head_id']);
            $filters['rpage'] = 1;
            $report = $this->financialSummaryService->buildRateList($filters, 10000);
            $report['tab'] = $tab;
            $report['date_from'] = now();
            $report['date_to'] = now();
            $report['patient_rows'] = collect();
            $report['test_rows'] = collect();

            return Pdf::loadView('laboratory.lab_financial_summary_print', $report)
                ->setPaper('a4', 'portrait')
                ->download('Lab_Rate_List.pdf');
        }

        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);

        $filters = $request->only([
            'date_from', 'date_to', 'lab_reg', 'mr', 'name', 'invoice', 'test_name', 'payment_status',
        ]);
        $filters['page'] = 1;
        $filters['tpage'] = 1;

        $report = $this->financialSummaryService->buildReport($filters, 10000);
        $report['tab'] = $tab;
        $report['rate_rows'] = collect();

        $filename = sprintf(
            'Lab_Financial_Summary_%s_to_%s.pdf',
            $report['date_from']->format('Y-m-d'),
            $report['date_to']->format('Y-m-d')
        );

        return Pdf::loadView('laboratory.lab_financial_summary_print', $report)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    private function emptyPaginator(string $pageName): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 50, 1, [
            'path' => request()->url(),
            'pageName' => $pageName,
            'query' => request()->query(),
        ]);
    }
}
