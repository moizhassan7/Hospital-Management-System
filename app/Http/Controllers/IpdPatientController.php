<?php

namespace App\Http\Controllers;

use App\Services\DesktopIpdPatientService;
use Illuminate\Http\Request;

class IpdPatientController extends Controller
{
    public function __construct(
        private DesktopIpdPatientService $ipdService
    ) {}

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'history');
        if (! in_array($tab, ['history', 'detail'], true)) {
            $tab = 'history';
        }

        $filters = [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
            'mr' => $request->input('mr'),
            'name' => $request->input('name'),
            'mobile' => $request->input('mobile'),
            'ward' => $request->input('ward'),
            'bed' => $request->input('bed'),
            'patient_type' => $request->input('patient_type'),
            'slip' => $request->input('slip'),
            'cnic' => $request->input('cnic'),
            'status' => $request->input('status', 'all'),
            'doctor' => $request->input('doctor'),
            'department' => $request->input('department'),
            'active' => $request->input('active', 'all'),
        ];

        $desktopReady = $this->ipdService->testConnection();
        $records = null;
        $summary = null;

        if ($desktopReady) {
            $result = $tab === 'detail'
                ? $this->ipdService->searchDetail($filters)
                : $this->ipdService->searchHistory($filters);

            $records = $result['paginator'];
            $summary = $result['summary'];
        }

        return view('ipd.index', [
            'tab' => $tab,
            'filters' => $filters,
            'records' => $records,
            'summary' => $summary,
            'desktopReady' => $desktopReady,
            'desktopError' => $this->ipdService->getLastError(),
        ]);
    }

    public function showHistory(int $id)
    {
        if (! $this->ipdService->testConnection()) {
            return redirect()->route('ipd.index')->with('error', $this->ipdService->getLastError());
        }

        $record = $this->ipdService->findHistory($id);

        if (! $record) {
            abort(404, 'IPD history record not found.');
        }

        return view('ipd.show', [
            'tab' => 'history',
            'record' => $record,
            'title' => 'IPD History — ' . ($record->PatientName ?? 'Patient'),
        ]);
    }

    public function showDetail(int $id)
    {
        if (! $this->ipdService->testConnection()) {
            return redirect()->route('ipd.index')->with('error', $this->ipdService->getLastError());
        }

        $record = $this->ipdService->findDetail($id);

        if (! $record) {
            abort(404, 'IPD detail record not found.');
        }

        return view('ipd.show', [
            'tab' => 'detail',
            'record' => $record,
            'title' => 'IPD Detail — ' . ($record->PatientName ?? 'Patient'),
        ]);
    }
}
