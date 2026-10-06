<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\OpdAppointment;
use App\Models\EmergencyPatient;
use App\Models\DayCareProcedure;
use Illuminate\Support\Facades\DB;

class RevenueShareReportController extends Controller
{
    public function doctorRevenue(Request $request)
    {
        $doctors = Doctor::where('employee_group', 'Doctor')->get();
        $selectedDoctorId = $request->input('doctor_id');
        $fromDate = $request->input('from_date', date('Y-m-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        $reportData = [];
        $totalDoctorRevenue = 0;
        $totalHospitalRevenue = 0;

        if ($selectedDoctorId) {
            $doctor = Doctor::findOrFail($selectedDoctorId);
            
            // Get all procedures this doctor has a share in
            $shares = DB::table('doctor_procedure_shares')
                ->join('doctors as procedures', 'doctor_procedure_shares.procedure_id', '=', 'procedures.id')
                ->where('doctor_procedure_shares.doctor_id', $selectedDoctorId)
                ->select(
                    'doctor_procedure_shares.procedure_id', 
                    'doctor_procedure_shares.share_percentage', 
                    'doctor_procedure_shares.hospital_share',
                    'procedures.name as procedure_name',
                    'procedures.code as procedure_code'
                )
                ->get();

            foreach ($shares as $share) {
                // 1. OPD Revenue for this procedure
                $opdAppointments = OpdAppointment::where('doctor_code', $share->procedure_code)
                    ->whereBetween('appointment_date', [$fromDate, $toDate])
                    ->get();

                foreach ($opdAppointments as $opd) {
                    $total = $opd->total_amount;
                    $docShareAmt = ($total * $share->share_percentage) / 100;
                    $hospShareAmt = ($total * $share->hospital_share) / 100;

                    $reportData[] = [
                        'date' => $opd->appointment_date,
                        'module' => 'OPD',
                        'patient_name' => $opd->patient_name,
                        'procedure_name' => $share->procedure_name,
                        'total_amount' => $total,
                        'doctor_share_amount' => $docShareAmt,
                        'hospital_share_amount' => $hospShareAmt,
                    ];
                    $totalDoctorRevenue += $docShareAmt;
                    $totalHospitalRevenue += $hospShareAmt;
                }

                // 2. Emergency Revenue for this procedure
                // Need to fetch and decode JSON for Emergency
                // For performance, we can fetch Emergency patients in date range and filter in PHP
                $emergencyPatients = EmergencyPatient::whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])->get();
                
                foreach ($emergencyPatients as $ep) {
                    $consultants = is_string($ep->consultants) ? json_decode($ep->consultants, true) : $ep->consultants;
                    if (is_array($consultants)) {
                        foreach ($consultants as $c) {
                            if ($c['id'] == $share->procedure_id) {
                                $total = $c['total'];
                                $docShareAmt = ($total * $share->share_percentage) / 100;
                                $hospShareAmt = ($total * $share->hospital_share) / 100;

                                $reportData[] = [
                                    'date' => $ep->created_at->format('Y-m-d'),
                                    'module' => 'Emergency',
                                    'patient_name' => $ep->patient_name,
                                    'procedure_name' => $share->procedure_name,
                                    'total_amount' => $total,
                                    'doctor_share_amount' => $docShareAmt,
                                    'hospital_share_amount' => $hospShareAmt,
                                ];
                                $totalDoctorRevenue += $docShareAmt;
                                $totalHospitalRevenue += $hospShareAmt;
                            }
                        }
                    }
                }

                // 3. Day Care Revenue for this procedure
                $dayCareProcedures = DayCareProcedure::whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])->get();
                
                foreach ($dayCareProcedures as $dc) {
                    $consultants = is_string($dc->consultants) ? json_decode($dc->consultants, true) : $dc->consultants;
                    if (is_array($consultants)) {
                        foreach ($consultants as $c) {
                            if ($c['id'] == $share->procedure_id) {
                                $total = $c['total'];
                                $docShareAmt = ($total * $share->share_percentage) / 100;
                                $hospShareAmt = ($total * $share->hospital_share) / 100;

                                // Need patient name for DayCare
                                $patientName = $dc->patient ? $dc->patient->name : $dc->mr_no;

                                $reportData[] = [
                                    'date' => $dc->created_at->format('Y-m-d'),
                                    'module' => 'Day Care',
                                    'patient_name' => $patientName,
                                    'procedure_name' => $share->procedure_name,
                                    'total_amount' => $total,
                                    'doctor_share_amount' => $docShareAmt,
                                    'hospital_share_amount' => $hospShareAmt,
                                ];
                                $totalDoctorRevenue += $docShareAmt;
                                $totalHospitalRevenue += $hospShareAmt;
                            }
                        }
                    }
                }
            }
        }
        
        // Sort report data by date desc
        usort($reportData, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return view('reports.revenue_doctor', compact(
            'doctors', 
            'selectedDoctorId', 
            'fromDate', 
            'toDate', 
            'reportData', 
            'totalDoctorRevenue', 
            'totalHospitalRevenue'
        ));
    }

    public function hospitalRevenue(Request $request)
    {
        // For the hospital, we show all procedures that are shared, and the hospital's cut.
        $fromDate = $request->input('from_date', date('Y-m-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));
        $procedureId = $request->input('procedure_id');

        // Get all procedure configs
        $query = DB::table('doctor_procedure_shares')
            ->join('doctors as procedures', 'doctor_procedure_shares.procedure_id', '=', 'procedures.id')
            ->join('doctors as docs', 'doctor_procedure_shares.doctor_id', '=', 'docs.id')
            ->select(
                'doctor_procedure_shares.procedure_id',
                'doctor_procedure_shares.share_percentage',
                'doctor_procedure_shares.hospital_share',
                'procedures.name as procedure_name',
                'procedures.code as procedure_code',
                'docs.name as doctor_name'
            );
            
        if ($procedureId) {
            $query->where('doctor_procedure_shares.procedure_id', $procedureId);
        }
        
        $shares = $query->get();
        $proceduresList = Doctor::where('employee_group', 'Procedure')->get();

        $reportData = [];
        $grandTotalAmount = 0;
        $grandTotalHospitalRevenue = 0;

        foreach ($shares as $share) {
            // 1. OPD Revenue
            $opdAppointments = OpdAppointment::where('doctor_code', $share->procedure_code)
                ->whereBetween('appointment_date', [$fromDate, $toDate])
                ->get();

            foreach ($opdAppointments as $opd) {
                $total = $opd->total_amount;
                $hospShareAmt = ($total * $share->hospital_share) / 100;

                $reportData[] = [
                    'date' => $opd->appointment_date,
                    'module' => 'OPD',
                    'patient_name' => $opd->patient_name,
                    'procedure_name' => $share->procedure_name,
                    'doctor_name' => $share->doctor_name,
                    'total_amount' => $total,
                    'hospital_share_amount' => $hospShareAmt,
                ];
                $grandTotalAmount += $total;
                $grandTotalHospitalRevenue += $hospShareAmt;
            }

            // 2. Emergency Revenue
            $emergencyPatients = EmergencyPatient::whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])->get();
            
            foreach ($emergencyPatients as $ep) {
                $consultants = is_string($ep->consultants) ? json_decode($ep->consultants, true) : $ep->consultants;
                if (is_array($consultants)) {
                    foreach ($consultants as $c) {
                        if ($c['id'] == $share->procedure_id) {
                            $total = $c['total'];
                            $hospShareAmt = ($total * $share->hospital_share) / 100;

                            $reportData[] = [
                                'date' => $ep->created_at->format('Y-m-d'),
                                'module' => 'Emergency',
                                'patient_name' => $ep->patient_name,
                                'procedure_name' => $share->procedure_name,
                                'doctor_name' => $share->doctor_name,
                                'total_amount' => $total,
                                'hospital_share_amount' => $hospShareAmt,
                            ];
                            $grandTotalAmount += $total;
                            $grandTotalHospitalRevenue += $hospShareAmt;
                        }
                    }
                }
            }

            // 3. Day Care Revenue
            $dayCareProcedures = DayCareProcedure::whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])->get();
            
            foreach ($dayCareProcedures as $dc) {
                $consultants = is_string($dc->consultants) ? json_decode($dc->consultants, true) : $dc->consultants;
                if (is_array($consultants)) {
                    foreach ($consultants as $c) {
                        if ($c['id'] == $share->procedure_id) {
                            $total = $c['total'];
                            $hospShareAmt = ($total * $share->hospital_share) / 100;

                            $patientName = $dc->patient ? $dc->patient->name : $dc->mr_no;

                            $reportData[] = [
                                'date' => $dc->created_at->format('Y-m-d'),
                                'module' => 'Day Care',
                                'patient_name' => $patientName,
                                'procedure_name' => $share->procedure_name,
                                'doctor_name' => $share->doctor_name,
                                'total_amount' => $total,
                                'hospital_share_amount' => $hospShareAmt,
                            ];
                            $grandTotalAmount += $total;
                            $grandTotalHospitalRevenue += $hospShareAmt;
                        }
                    }
                }
            }
        }
        
        usort($reportData, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return view('reports.revenue_hospital', compact(
            'fromDate', 
            'toDate', 
            'procedureId',
            'proceduresList',
            'reportData', 
            'grandTotalAmount',
            'grandTotalHospitalRevenue'
        ));
    }
}
