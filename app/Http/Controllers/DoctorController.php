<?php

namespace App\Http\Controllers;

use App\Models\Abstain;
use App\Models\Diagnosis;
use App\Models\Dosage;
use App\Models\Medicine;
use App\Models\MedicineGroup;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DoctorController extends Controller
{
    public function dashboard()
    {
        // Check if user has permission instead of hardcoded role
        if (!Auth::user()->hasPermission('View Dashboard')) {
            // But specifically for Doctor Dashboard, we might want a separate permission
        }
        
        $drafts = Prescription::where('is_draft', true)->with('patient')->latest()->get();
        return view('doctor-portal.dashboard', compact('drafts'));
    }

    public function registerPatient()
    {
        return view('doctor-portal.register-patient');
    }

    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'age' => 'required|integer|min:1',
            'gender' => 'required|string',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'patient_type' => 'required|string',
            'family_name' => 'nullable|string|max:255',
            'family_relation' => 'nullable|string|max:255',
        ]);

        $mrNumber = str_pad(Patient::count() + 1, 4, '0', STR_PAD_LEFT);

        Patient::create([
            'mr_number' => $mrNumber,
            'name' => $validated['patient_name'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'patient_type' => $validated['patient_type'],
            'mobile_number' => $validated['contact_number'],
            'address' => $validated['address'],
            'family_name' => $validated['family_name'] ?? null,
            'family_relation' => $validated['family_relation'] ?? null,
            'registration_date' => now()->toDateString(),
            'marital_status' => 'Unknown', // Default value to satisfy migration
            'date_of_birth' => now()->subYears($validated['age'])->toDateString(), // Approximate
        ]);

        return redirect()->route('doctors.register-patient')->with('success', "Patient registered successfully! MR Number: $mrNumber");
    }

    public function writePrescription(Request $request)
    {
        $patient = null;
        $latestPrescription = null;
        $draftPrescription = null;

        if ($request->has('prescription_id')) {
            $draftPrescription = Prescription::with('patient')->find($request->get('prescription_id'));
            if ($draftPrescription) {
                $patient = $draftPrescription->patient;
                $patient->load('prescriptions');
                $latestPrescription = $patient->prescriptions()->where('is_draft', false)->latest()->first();
            }
        } elseif ($request->has('mr_number')) {
            $term = $request->get('mr_number');
            $patient = Patient::where('mr_number', $term)
                ->orWhere('name', 'like', '%' . $term . '%')
                ->orWhere('mobile_number', 'like', '%' . $term . '%')
                ->first();
                
            if ($patient) {
                $patient->load('prescriptions');
                $latestPrescription = $patient->prescriptions()->where('is_draft', false)->latest()->first();
            }
        }
        
        $drafts = Prescription::where('is_draft', true)->with('patient')->latest()->get();
        $diagnosesList = Diagnosis::all();
        $reportsList = Report::all();
        $medicinesList = Medicine::with('dosages')->get();
        $medicineGroups = MedicineGroup::with('medicines')->get();
        $abstainsList = Abstain::with('items')->get();
        $dosagesList = Dosage::all();
        return view('doctor-portal.write-prescription', compact('patient', 'latestPrescription', 'draftPrescription', 'drafts', 'diagnosesList', 'reportsList', 'medicinesList', 'medicineGroups', 'abstainsList', 'dosagesList'));
    }

    public function storePrescription(Request $request)
    {
        $validated = $request->validate([
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'mr_number' => 'required|exists:patients,mr_number',
            'is_draft' => 'nullable|boolean',
            'complaints' => 'nullable|string',
            'bp' => 'nullable|string',
            'pulse' => 'nullable|integer',
            'temperature' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'oxygen' => 'nullable|integer',
            'diagnoses' => 'nullable|array',
            'reports' => 'nullable|array',
            'medicines_data' => 'nullable|json',
            'abstains' => 'nullable|array',
            'notes' => 'nullable|string',
            'next_visit_date' => 'nullable|date',
        ]);
        
        $patient = Patient::where('mr_number', $validated['mr_number'])->first();
        $isDraft = $request->has('is_draft') ? (bool)$validated['is_draft'] : false;

        $prescriptionData = [
            'patient_id' => $patient->id,
            'is_draft' => $isDraft,
            'complaints' => $validated['complaints'],
            'bp' => $validated['bp'],
            'pulse' => $validated['pulse'],
            'temperature' => $validated['temperature'],
            'weight' => $validated['weight'],
            'oxygen' => $validated['oxygen'],
            'diagnoses' => $validated['diagnoses'] ?? [],
            'reports' => $validated['reports'] ?? [],
            'medicines' => json_decode($validated['medicines_data'], true) ?? [],
            'abstains' => $validated['abstains'] ?? [],
            'notes' => $validated['notes'],
            'next_visit_date' => $validated['next_visit_date'],
        ];

        if (!empty($validated['prescription_id'])) {
            $prescription = Prescription::findOrFail($validated['prescription_id']);
            $prescription->update($prescriptionData);
        } else {
            $prescription = Prescription::create($prescriptionData);
        }

        $message = $isDraft ? 'Prescription saved as temporary!' : 'Prescription saved successfully!';
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => $message,
                'prescription_id' => $prescription->id,
                'redirect_url' => $isDraft ? null : route('doctors.dashboard')
            ]);
        }

        if ($isDraft) {
            return redirect()->route('doctors.write-prescription', [
                'mr_number' => $patient->mr_number,
                'prescription_id' => $prescription->id
            ])->with('success', $message);
        }

        return redirect()->route('doctors.dashboard')->with('success', $message);
    }

    public function reports(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $reportType = $request->input('report_type', 'visits');
        $searchPatient = $request->input('search_patient');
        $perPage = $request->input('per_page', 10);
        
        if (!$startDate && !$endDate && $reportType === 'visits') {
            $startDate = Carbon::today()->subDays(30)->toDateString();
            $endDate = Carbon::today()->toDateString();
        }
        
        $query = Prescription::with('patient');
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        if ($searchPatient) {
            $query->whereHas('patient', function($q) use ($searchPatient) {
                $q->where('name', 'LIKE', "%{$searchPatient}%")
                  ->orWhere('mr_number', 'LIKE', "%{$searchPatient}%");
            });
        }
        
        $prescriptions = $query->latest()->paginate($perPage);
        $totalVisits = $query->count();
        
        $dailyVisitsQuery = Prescription::selectRaw('DATE(created_at) as visit_date, COUNT(*) as visit_count')
            ->groupBy('visit_date');
        
        if ($startDate) {
            $dailyVisitsQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $dailyVisitsQuery->whereDate('created_at', '<=', $endDate);
        }
        
        $dailyVisits = $dailyVisitsQuery->orderBy('visit_date', 'desc')->get();
        
        $todaysVisits = Prescription::with('patient')
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->get();
        
        $thisMonthVisits = Prescription::with('patient')
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->latest()
            ->get();
        
        $monthlyVisits = Prescription::selectRaw('EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month, COUNT(*) as visit_count')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        $hourlyStats = [];
        if ($reportType === 'analytics') {
            $hourlyData = Prescription::selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as visit_count')
                ->whereBetween('created_at', [
                    Carbon::now()->subDays(30),
                    Carbon::now()
                ])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
            
            for ($i = 0; $i < 24; $i++) {
                $hourlyStats[str_pad($i, 2, '0', STR_PAD_LEFT)] = 0;
            }
            
            foreach ($hourlyData as $data) {
                $hourlyStats[str_pad($data->hour, 2, '0', STR_PAD_LEFT)] = $data->visit_count;
            }
            
            arsort($hourlyStats);
            $hourlyStats = array_slice($hourlyStats, 0, 8, true);
        }
        
        return view('doctor-portal.reports', compact(
            'prescriptions', 
            'totalVisits', 
            'startDate', 
            'endDate', 
            'reportType',
            'searchPatient',
            'perPage',
            'dailyVisits',
            'todaysVisits',
            'thisMonthVisits',
            'monthlyVisits',
            'hourlyStats'
        ));
    }

    public function printPrescription($prescriptionId)
    {
        $prescription = Prescription::with(['patient'])->findOrFail($prescriptionId);
        
        $diagnosesList = Diagnosis::all();
        $reportsList = Report::all();
        
        $formattedDiagnoses = [];
        if ($prescription->diagnoses) {
            foreach ($prescription->diagnoses as $diagnosisId) {
                $diagnosis = $diagnosesList->find($diagnosisId);
                if ($diagnosis) {
                    $formattedDiagnoses[] = $diagnosis->name;
                }
            }
        }
        
        $formattedReports = [];
        if ($prescription->reports) {
            foreach ($prescription->reports as $reportId) {
                $report = $reportsList->find($reportId);
                if ($report) {
                    $formattedReports[] = $report->name;
                }
            }
        }

        $formattedAbstains = [];
        if ($prescription->abstains) {
            $abstainItemsList = \App\Models\AbstainItem::all();
            foreach ($prescription->abstains as $abstainId) {
                if (is_numeric($abstainId)) {
                    $item = $abstainItemsList->find($abstainId);
                    if ($item) {
                        $formattedAbstains[] = $item->item . ($item->duration ? " ({$item->duration})" : "");
                    }
                } else if (is_array($abstainId) && isset($abstainId['item'])) {
                    $formattedAbstains[] = $abstainId['item'] . (!empty($abstainId['duration']) ? " ({$abstainId['duration']})" : "");
                }
            }
        }
        
        return view('doctor-portal.print-prescription', compact('prescription', 'formattedDiagnoses', 'formattedReports', 'formattedAbstains'));
    }

    public function getLastVisitPrescription($mrNumber)
    {
        $lastPrescription = Prescription::with(['patient'])
            ->whereHas('patient', function($query) use ($mrNumber) {
                $query->where('mr_number', $mrNumber);
            })
            ->latest()
            ->first();
        
        if (!$lastPrescription) {
            return redirect()->back()->with('error', 'No prescription found for this patient.');
        }
        
        $diagnosesList = Diagnosis::all();
        $reportsList = Report::all();
        
        $formattedDiagnoses = [];
        if ($lastPrescription->diagnoses) {
            foreach ($lastPrescription->diagnoses as $diagnosisId) {
                $diagnosis = $diagnosesList->find($diagnosisId);
                if ($diagnosis) {
                    $formattedDiagnoses[] = $diagnosis->name;
                }
            }
        }
        
        $formattedReports = [];
        if ($lastPrescription->reports) {
            foreach ($lastPrescription->reports as $reportId) {
                $report = $reportsList->find($reportId);
                if ($report) {
                    $formattedReports[] = $report->name;
                }
            }
        }
        
        return view('doctor-portal.print-prescription', compact('lastPrescription', 'formattedDiagnoses', 'formattedReports'));
    }

    public function searchPatients(Request $request)
    {
        $term = $request->get('term');
        $patients = Patient::where('name', 'like', "%$term%")
            ->orWhere('mr_number', 'like', "%$term%")
            ->orWhere('mobile_number', 'like', "%$term%")
            ->limit(10)
            ->get();
            
        return response()->json($patients);
    }

    public function getPrescription($id)
    {
        $prescription = Prescription::with('patient')->findOrFail($id);
        
        // Format names for display
        $diagnosesList = Diagnosis::all();
        $reportsList = Report::all();
        $abstainItemsList = \App\Models\AbstainItem::all();
        
        $prescription->formatted_diagnoses = collect($prescription->diagnoses)->map(function($id) use ($diagnosesList) {
            $d = $diagnosesList->find($id);
            return $d ? $d->name : null;
        })->filter()->values();
        
        $prescription->formatted_reports = collect($prescription->reports)->map(function($id) use ($reportsList) {
            $r = $reportsList->find($id);
            return $r ? $r->name : null;
        })->filter()->values();

        $prescription->abstains_details = collect($prescription->abstains)->map(function($id) use ($abstainItemsList) {
            if (is_numeric($id)) {
                $item = $abstainItemsList->find($id);
                return $item ? ['item' => $item->item, 'duration' => $item->duration] : null;
            }
            return is_array($id) ? $id : null;
        })->filter()->values();
        
        return response()->json($prescription);
    }

    public function storeDiagnosis(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:diagnoses,name']);
        $diagnosis = Diagnosis::create($validated);
        return response()->json($diagnosis);
    }

    public function storeReport(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:reports,name',
            'content' => 'nullable|string'
        ]);
        $report = Report::create($validated);
        return response()->json($report);
    }

    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:medicines,name',
            'unit' => 'nullable|string'
        ]);
        $medicine = Medicine::create($validated);
        return response()->json($medicine);
    }

    public function storeMedicineGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:medicine_groups,name',
            'medicines' => 'required|array'
        ]);

        return DB::transaction(function() use ($validated) {
            $group = MedicineGroup::create(['name' => $validated['name']]);
            foreach ($validated['medicines'] as $med) {
                $group->medicines()->attach($med['medicine_id'], [
                    'dosage_frequency' => $med['dosage_frequency'],
                    'duration_days' => $med['duration_days']
                ]);
            }
            return response()->json($group->load('medicines'));
        });
    }

    public function getFamilyMembers($familyName)
    {
        // This assumes family_name is a field in Patient model. 
        // In HMS it might not exist yet. Let's check Patient model.
        $members = Patient::where('family_name', $familyName)->get();
        return response()->json($members);
    }
}