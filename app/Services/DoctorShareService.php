<?php

namespace App\Services;

use App\Models\DayCareProcedure;
use App\Models\Doctor;
use App\Models\EmergencyPatient;
use App\Models\OpdAppointment;
use App\Models\PatientDischarge;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class DoctorShareService
{
    /**
     * Doctors eligible for payment ledger (exclude Procedure/Service catalog rows).
     */
    public function payableDoctors()
    {
        return Doctor::query()
            ->where(function ($q) {
                $q->whereNull('employee_group')
                    ->orWhereNotIn('employee_group', ['Procedure', 'Service']);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Calculate doctor revenue share.
     * - Procedure shares (doctor_procedure_shares): OPD / Emergency / Day Care / Discharge procedure lines
     * - Direct doctor consultations: Emergency / Day Care / Discharge using doctor's own % from Fees & Shares
     *
     * @return array{rows: array<int, array>, total_doctor_share: float, total_hospital_share: float}
     */
    public function calculateShare(int $doctorId, ?string $from = null, ?string $to = null): array
    {
        $from = $from ?: '1970-01-01';
        $to = $to ?: now()->toDateString();

        $doctor = Doctor::find($doctorId);
        if (! $doctor) {
            return ['rows' => [], 'total_doctor_share' => 0.0, 'total_hospital_share' => 0.0];
        }

        $reportData = [];
        $totalDoctorRevenue = 0.0;
        $totalHospitalRevenue = 0.0;

        $shares = DB::table('doctor_procedure_shares')
            ->join('doctors as procedures', 'doctor_procedure_shares.procedure_id', '=', 'procedures.id')
            ->where('doctor_procedure_shares.doctor_id', $doctorId)
            ->select(
                'doctor_procedure_shares.procedure_id',
                'doctor_procedure_shares.share_percentage',
                'doctor_procedure_shares.hospital_share',
                'procedures.name as procedure_name',
                'procedures.code as procedure_code'
            )
            ->get();

        // ---- 1. OPD via procedure shares ----
        foreach ($shares as $share) {
            $opdAppointments = OpdAppointment::where('doctor_code', $share->procedure_code)
                ->whereBetween('appointment_date', [$from, $to])
                ->where(function ($query) {
                    $query->where('amount_paid', '>', 0)->orWhere('status', 'completed');
                })
                ->get();

            foreach ($opdAppointments as $opd) {
                $total = (float) $opd->total_amount;
                $docShareAmt = ($total * $share->share_percentage) / 100;
                $hospShareAmt = ($total * $share->hospital_share) / 100;

                $reportData[] = $this->row(
                    $opd->appointment_date,
                    'OPD',
                    $opd->patient_name,
                    $share->procedure_name,
                    $total,
                    $docShareAmt,
                    $hospShareAmt
                );
                $totalDoctorRevenue += $docShareAmt;
                $totalHospitalRevenue += $hospShareAmt;
            }
        }

        // ---- 1b. OPD direct (appointment booked under this doctor's own code) ----
        if ($doctor->code) {
            $ownOpd = OpdAppointment::where('doctor_code', $doctor->code)
                ->whereBetween('appointment_date', [$from, $to])
                ->where(function ($query) {
                    $query->where('amount_paid', '>', 0)->orWhere('status', 'completed');
                })
                ->get();

            // Skip if already counted via procedure share with same code
            $procedureCodes = $shares->pluck('procedure_code')->filter()->all();

            foreach ($ownOpd as $opd) {
                if (in_array($doctor->code, $procedureCodes, true)) {
                    continue;
                }
                $total = (float) $opd->total_amount;
                $pct = (float) ($doctor->general_normal_percentage ?? 0);
                $docShareAmt = ($total * $pct) / 100;
                $hospShareAmt = $total - $docShareAmt;

                $reportData[] = $this->row(
                    $opd->appointment_date,
                    'OPD',
                    $opd->patient_name,
                    $doctor->name,
                    $total,
                    $docShareAmt,
                    $hospShareAmt
                );
                $totalDoctorRevenue += $docShareAmt;
                $totalHospitalRevenue += $hospShareAmt;
            }
        }

        // ---- 2. Emergency consultants ----
        $emergencyPatients = EmergencyPatient::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->get();
        foreach ($emergencyPatients as $ep) {
            $consultants = $this->parseConsultants($ep->consultants);
            foreach ($consultants as $c) {
                $total = (float) ($c['total'] ?? 0);
                if ($total <= 0) {
                    continue;
                }
                $cid = $c['id'] ?? null;

                // Direct doctor consultation
                if ((int) $cid === (int) $doctorId) {
                    $pct = (float) ($doctor->general_emergency_percentage ?? 0);
                    $docShareAmt = ($total * $pct) / 100;
                    $hospShareAmt = $total - $docShareAmt;
                    $reportData[] = $this->row(
                        $ep->created_at->format('Y-m-d'),
                        'Emergency',
                        $ep->patient_name,
                        $doctor->name,
                        $total,
                        $docShareAmt,
                        $hospShareAmt
                    );
                    $totalDoctorRevenue += $docShareAmt;
                    $totalHospitalRevenue += $hospShareAmt;

                    continue;
                }

                // Procedure share
                foreach ($shares as $share) {
                    if ((int) $cid === (int) $share->procedure_id) {
                        $docShareAmt = ($total * $share->share_percentage) / 100;
                        $hospShareAmt = ($total * $share->hospital_share) / 100;
                        $reportData[] = $this->row(
                            $ep->created_at->format('Y-m-d'),
                            'Emergency',
                            $ep->patient_name,
                            $share->procedure_name,
                            $total,
                            $docShareAmt,
                            $hospShareAmt
                        );
                        $totalDoctorRevenue += $docShareAmt;
                        $totalHospitalRevenue += $hospShareAmt;
                    }
                }
            }
        }

        // ---- 3. Day Care consultants ----
        $dayCareProcedures = DayCareProcedure::with('patient')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->get();

        foreach ($dayCareProcedures as $dc) {
            $consultants = $this->parseConsultants($dc->consultants);
            $patientName = $dc->patient ? $dc->patient->name : $dc->mr_no;
            $isWelfare = (bool) ($dc->patient->is_welfare ?? false);

            foreach ($consultants as $c) {
                $total = (float) ($c['total'] ?? 0);
                if ($total <= 0) {
                    continue;
                }
                $cid = $c['id'] ?? null;

                if ((int) $cid === (int) $doctorId) {
                    $pct = $isWelfare
                        ? (float) ($doctor->welfare_normal_percentage ?? 0)
                        : (float) ($doctor->general_normal_percentage ?? 0);
                    $docShareAmt = ($total * $pct) / 100;
                    $hospShareAmt = $total - $docShareAmt;
                    $reportData[] = $this->row(
                        $dc->created_at->format('Y-m-d'),
                        'Day Care',
                        $patientName,
                        $doctor->name,
                        $total,
                        $docShareAmt,
                        $hospShareAmt
                    );
                    $totalDoctorRevenue += $docShareAmt;
                    $totalHospitalRevenue += $hospShareAmt;

                    continue;
                }

                foreach ($shares as $share) {
                    if ((int) $cid === (int) $share->procedure_id) {
                        $docShareAmt = ($total * $share->share_percentage) / 100;
                        $hospShareAmt = ($total * $share->hospital_share) / 100;
                        $reportData[] = $this->row(
                            $dc->created_at->format('Y-m-d'),
                            'Day Care',
                            $patientName,
                            $share->procedure_name,
                            $total,
                            $docShareAmt,
                            $hospShareAmt
                        );
                        $totalDoctorRevenue += $docShareAmt;
                        $totalHospitalRevenue += $hospShareAmt;
                    }
                }
            }
        }

        // ---- 4. IPD Discharge consultants (THIS was missing) ----
        $discharges = PatientDischarge::with('patient')
            ->whereBetween('discharge_date', [$from, $to])
            ->get();

        foreach ($discharges as $discharge) {
            $consultants = $this->parseConsultants($discharge->consultants);
            $patientName = $discharge->patient->name ?? ('Patient #'.$discharge->patient_id);
            $isWelfare = (bool) ($discharge->patient->is_welfare ?? false);
            $date = (string) ($discharge->discharge_date instanceof \Carbon\Carbon
                ? $discharge->discharge_date->format('Y-m-d')
                : $discharge->discharge_date);

            foreach ($consultants as $c) {
                $total = (float) ($c['total'] ?? 0);
                if ($total <= 0) {
                    continue;
                }
                $cid = $c['id'] ?? null;

                // Direct doctor consultation on discharge bill
                if ((int) $cid === (int) $doctorId) {
                    $pct = $isWelfare
                        ? (float) ($doctor->welfare_normal_percentage ?? 0)
                        : (float) ($doctor->general_normal_percentage ?? 0);
                    $docShareAmt = ($total * $pct) / 100;
                    $hospShareAmt = $total - $docShareAmt;
                    $reportData[] = $this->row(
                        $date,
                        'IPD Discharge',
                        $patientName,
                        $c['name'] ?? $doctor->name,
                        $total,
                        $docShareAmt,
                        $hospShareAmt
                    );
                    $totalDoctorRevenue += $docShareAmt;
                    $totalHospitalRevenue += $hospShareAmt;

                    continue;
                }

                // Shareable procedure on discharge (e.g. ECG)
                foreach ($shares as $share) {
                    if ((int) $cid === (int) $share->procedure_id) {
                        $docShareAmt = ($total * $share->share_percentage) / 100;
                        $hospShareAmt = ($total * $share->hospital_share) / 100;
                        $reportData[] = $this->row(
                            $date,
                            'IPD Discharge',
                            $patientName,
                            $share->procedure_name,
                            $total,
                            $docShareAmt,
                            $hospShareAmt
                        );
                        $totalDoctorRevenue += $docShareAmt;
                        $totalHospitalRevenue += $hospShareAmt;
                    }
                }
            }
        }

        usort($reportData, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return [
            'rows' => $reportData,
            'total_doctor_share' => round($totalDoctorRevenue, 2),
            'total_hospital_share' => round($totalHospitalRevenue, 2),
        ];
    }

    public function totalShareToDate(int $doctorId): float
    {
        return $this->calculateShare($doctorId)['total_doctor_share'];
    }

    public function totalPaid(int $doctorId): float
    {
        return (float) Voucher::query()
            ->where('status', 'posted')
            ->where('party_type', 'doctor')
            ->where('party_id', $doctorId)
            ->whereHas('category', fn ($q) => $q->where('code', 'doctor-payment'))
            ->sum('amount');
    }

    public function pendingBalance(int $doctorId): float
    {
        return round(max(0, $this->totalShareToDate($doctorId) - $this->totalPaid($doctorId)), 2);
    }

    /**
     * @return array{total_share: float, total_paid: float, pending: float}
     */
    public function balanceSummary(int $doctorId): array
    {
        $totalShare = $this->totalShareToDate($doctorId);
        $totalPaid = $this->totalPaid($doctorId);

        return [
            'total_share' => $totalShare,
            'total_paid' => $totalPaid,
            'pending' => round(max(0, $totalShare - $totalPaid), 2),
        ];
    }

    private function parseConsultants($consultants): array
    {
        if (is_string($consultants)) {
            $consultants = json_decode($consultants, true);
        }
        if (! is_array($consultants)) {
            return [];
        }
        // Support both list and id-keyed map
        $list = [];
        foreach ($consultants as $key => $c) {
            if (! is_array($c)) {
                continue;
            }
            if (! isset($c['id'])) {
                $c['id'] = is_numeric($key) ? (int) $key : null;
            }
            $list[] = $c;
        }

        return $list;
    }

    private function row(
        $date,
        string $module,
        $patientName,
        $procedureName,
        float $total,
        float $docShare,
        float $hospShare
    ): array {
        return [
            'date' => $date,
            'module' => $module,
            'patient_name' => $patientName,
            'procedure_name' => $procedureName,
            'total_amount' => $total,
            'doctor_share_amount' => $docShare,
            'hospital_share_amount' => $hospShare,
        ];
    }
}
