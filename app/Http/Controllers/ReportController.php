<?php

namespace App\Http\Controllers;

use App\Models\PatientDischarge;
use App\Models\IndoorPatient;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\OpdAppointment;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class ReportController extends Controller
{
    public function index()
    {
        // Total Patients Registered
        $totalPatients = Patient::count();
        
        // Total Indoor Patients Currently Admitted
        $totalIndoorPatients = IndoorPatient::count();

     

        // Total OPD Appointments
        $totalOpdAppointments = OpdAppointment::count();

        // Total Discharged Patients
        $totalDischargedPatients = PatientDischarge::count();

        // Total Doctors
        $totalDoctors = Doctor::count();

        // Revenue from Indoor Patients (as a placeholder)
        $indoorRevenue = IndoorPatient::sum('total_amount');

        // Revenue from Emergency Patients (as a placeholder)
     
        
        $totalRevenue = $indoorRevenue;

        return view('reports.index', compact(
            'totalPatients',
            'totalIndoorPatients',
            'totalOpdAppointments',
            'totalDischargedPatients',
            'totalDoctors',
            'totalRevenue'
        ));
    }
    /**
     * Display a summary report of indoor patients.
     *
     * @return \Illuminate\View\View
     */
    public function indoorPatientSummary(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $indoorPatients = IndoorPatient::whereBetween('indoor_patients.registration_date', [$startDate, $endDate])
            ->join('patients', 'indoor_patients.patient_id', '=', 'patients.id')
            ->join('rooms', 'indoor_patients.ward_id', '=', 'rooms.id')
            ->select('rooms.name as ward_name', 'patients.is_welfare')
            ->get();
            
        $reportData = $indoorPatients->groupBy('ward_name')->map(function ($wardGroup) {
            $generalCount = $wardGroup->where('is_welfare', false)->count();
            $welfareCount = $wardGroup->where('is_welfare', true)->count();
            return [
                'general' => $generalCount,
                'welfare' => $welfareCount,
                'total' => $generalCount + $welfareCount,
            ];
        });

        $grandTotalGeneral = $reportData->sum('general');
        $grandTotalWelfare = $reportData->sum('welfare');
        $grandTotal = $reportData->sum('total');

        return view('reports.indoor_patient_summary', compact(
            'reportData',
            'grandTotalGeneral',
            'grandTotalWelfare',
            'grandTotal',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Download a summary report of indoor patients as a PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadIndoorPatientSummaryPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $indoorPatients = IndoorPatient::whereBetween('indoor_patients.registration_date', [$startDate, $endDate])
            ->join('patients', 'indoor_patients.patient_id', '=', 'patients.id')
            ->join('rooms', 'indoor_patients.ward_id', '=', 'rooms.id')
            ->select('rooms.name as ward_name', 'patients.is_welfare')
            ->get();
            
        $reportData = $indoorPatients->groupBy('ward_name')->map(function ($wardGroup) {
            $generalCount = $wardGroup->where('is_welfare', false)->count();
            $welfareCount = $wardGroup->where('is_welfare', true)->count();
            return [
                'general' => $generalCount,
                'welfare' => $welfareCount,
                'total' => $generalCount + $welfareCount,
            ];
        });

        $grandTotalGeneral = $reportData->sum('general');
        $grandTotalWelfare = $reportData->sum('welfare');
        $grandTotal = $reportData->sum('total');

        $data = [
            'reportData' => $reportData,
            'grandTotalGeneral' => $grandTotalGeneral,
            'grandTotalWelfare' => $grandTotalWelfare,
            'grandTotal' => $grandTotal,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        
        $pdf = Pdf::loadView('reports.indoor_patient_summary_pdf', $data);
        return $pdf->download('Indoor_Patient_Summary_Report_' . $startDate . '_' . $endDate . '.pdf');
    }


public function printIndoorPatientSummary(Request $request)
{
    $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
    $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

    $indoorPatients = IndoorPatient::whereBetween('indoor_patients.registration_date', [$startDate, $endDate])
        ->join('patients', 'indoor_patients.patient_id', '=', 'patients.id')
        ->join('rooms', 'indoor_patients.ward_id', '=', 'rooms.id')
        ->select('rooms.name as ward_name', 'patients.is_welfare')
        ->get();

    $reportData = $indoorPatients->groupBy('ward_name')->map(function ($wardGroup) {
        $generalCount = $wardGroup->where('is_welfare', false)->count();
        $welfareCount = $wardGroup->where('is_welfare', true)->count();
        return [
            'general' => $generalCount,
            'welfare' => $welfareCount,
            'total' => $generalCount + $welfareCount,
        ];
    });

    $grandTotalGeneral = $reportData->sum('general');
    $grandTotalWelfare = $reportData->sum('welfare');
    $grandTotal = $reportData->sum('total');

    $data = [
        'reportData' => $reportData,
        'grandTotalGeneral' => $grandTotalGeneral,
        'grandTotalWelfare' => $grandTotalWelfare,
        'grandTotal' => $grandTotal,
        'startDate' => $startDate,
        'endDate' => $endDate,
    ];

    // Generate PDF content
    $pdf = Pdf::loadView('reports.indoor_patient_summary_pdf', $data)->output();

    // Save PDF temporarily
    $filePath = storage_path('app/public/Indoor_Patient_Summary.pdf');
    file_put_contents($filePath, $pdf);

    // Send to printer
    // For macOS/Linux (CUPS)
    $process = new Process(['lp', $filePath]);
    $process->run();

    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    return back()->with('success', 'Indoor Patient Summary sent to printer successfully!');
}

    /**
     * Display a summary report of OPD appointments by department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function opdSummary(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $opdAppointments = OpdAppointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->join('doctors', 'opd_appointments.doctor_code', '=', 'doctors.code')
            ->join('departments', 'doctors.department_id', '=', 'departments.id')
            ->select('departments.name as department_name', DB::raw('count(*) as total'))
            ->groupBy('departments.name')
            ->get();
        
        $totalAppointments = $opdAppointments->sum('total');

        return view('reports.opd_summary', compact(
            'opdAppointments',
            'totalAppointments',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display a history report of all discharged indoor patients.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function indoorDischargePatientHistory(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $dischargedPatients = PatientDischarge::with(['patient', 'certifyingDoctor'])
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->get();
        
        return view('reports.indoor_discharge_history', compact(
            'dischargedPatients',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Download a history report of all discharged indoor patients as a PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadIndoorDischargeHistoryPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $dischargedPatients = PatientDischarge::with(['patient', 'certifyingDoctor'])
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->get();

        $data = [
            'dischargedPatients' => $dischargedPatients,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        
        $pdf = Pdf::loadView('reports.indoor_discharge_history_pdf', $data);
        return $pdf->download('Indoor_Discharge_History_' . $startDate . '_' . $endDate . '.pdf');
    }
    
    /**
     * Display a payment report for all discharged indoor patients.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function indoorDischargePatientPayment(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $payments = PatientDischarge::with(['patient', 'certifyingDoctor'])
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->get();
        
        // This is a simplified calculation; you may need to adjust to your specific billing logic
        $payments->each(function($payment) {
            $payment->dr_share = ($payment->total_bill * 0.40); // 40% share for doctor
            $payment->hop_share = ($payment->total_bill * 0.60); // 60% share for hospital
            $payment->amount_adj = $payment->total_bill - $payment->advance_fee;
        });

        return view('reports.indoor_discharge_payment', compact(
            'payments',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Download a payment report for all discharged indoor patients as a PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadIndoorDischargePaymentPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $payments = PatientDischarge::with(['patient', 'certifyingDoctor'])
            ->whereBetween('discharge_date', [$startDate, $endDate])
            ->get();

        $payments->each(function($payment) {
            $payment->dr_share = ($payment->total_bill * 0.40);
            $payment->hop_share = ($payment->total_bill * 0.60);
            $payment->amount_adj = $payment->total_bill - $payment->advance_fee;
        });
        
        $data = compact('payments', 'startDate', 'endDate');
        
        $pdf = Pdf::loadView('reports.indoor_discharge_payment_pdf', $data);
        return $pdf->download('Indoor_Discharge_Payment_Report_' . $startDate . '_' . $endDate . '.pdf');
    }
}