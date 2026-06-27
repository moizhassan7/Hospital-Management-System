<?php

namespace App\Http\Controllers;

use App\Models\PathologyReportAccess;
use App\Services\PathologyReportService;
use Illuminate\View\View;

class OnlineReportController extends Controller
{
    public function __construct(
        private PathologyReportService $reportService
    ) {}

    public function show(string $token): View
    {
        $access = PathologyReportAccess::where('access_token', $token)->firstOrFail();

        $data = $this->reportService->buildReportData(
            (int) $access->laboratory_patient_id,
            (int) $access->test_id
        );

        if (empty($data['hasResults'])) {
            abort(404, 'Report is not available yet.');
        }

        $data['isOnlineView'] = true;

        return view('laboratory.print_report', $data);
    }
}
