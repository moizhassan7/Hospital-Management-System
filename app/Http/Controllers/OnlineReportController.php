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

    public function trackByRegNo(string $regNo)
    {
        // Placeholder for patient report portal
        // In a complete implementation, this would look up the patient by regNo
        // and display a portal with all their available reports.
        
        $query = \App\Models\LaboratoryPatient::query();
        $patient = \App\Support\LabRegistrationNumber::applyToQuery($query, 'lab_registration_no', $regNo)->first();
        
        if (!$patient) {
            abort(404, 'Patient not found for the given registration number.');
        }

        abort(404, 'The online patient portal for viewing all reports is currently under construction.');
    }
}
