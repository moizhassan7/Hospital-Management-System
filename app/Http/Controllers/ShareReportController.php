<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Doctor;

class ShareReportController extends Controller
{
    public function doctorShares(Request $request)
    {
        $procedureId = $request->input('procedure_id');
        
        $query = DB::table('doctor_procedure_shares')
            ->join('doctors as doc', 'doctor_procedure_shares.doctor_id', '=', 'doc.id')
            ->join('doctors as proc', 'doctor_procedure_shares.procedure_id', '=', 'proc.id')
            ->select(
                'doc.name as doctor_name',
                'doc.code as doctor_code',
                'proc.name as procedure_name',
                'doctor_procedure_shares.share_percentage'
            )
            ->where('proc.employee_group', 'Procedure');

        if ($procedureId) {
            $query->where('doctor_procedure_shares.procedure_id', $procedureId);
        }

        $shares = $query->orderBy('proc.name')->orderBy('doc.name')->get();

        $procedures = Doctor::where('employee_group', 'Procedure')->where('is_shareable', true)->get();

        return view('reports.doctor_shares', compact('shares', 'procedures', 'procedureId'));
    }

    public function hospitalShares(Request $request)
    {
        $procedureId = $request->input('procedure_id');
        
        $query = DB::table('doctor_procedure_shares')
            ->join('doctors as doc', 'doctor_procedure_shares.doctor_id', '=', 'doc.id')
            ->join('doctors as proc', 'doctor_procedure_shares.procedure_id', '=', 'proc.id')
            ->select(
                'doc.name as doctor_name',
                'doc.code as doctor_code',
                'proc.name as procedure_name',
                'doctor_procedure_shares.hospital_share'
            )
            ->where('proc.employee_group', 'Procedure');

        if ($procedureId) {
            $query->where('doctor_procedure_shares.procedure_id', $procedureId);
        }

        $shares = $query->orderBy('proc.name')->orderBy('doc.name')->get();

        $procedures = Doctor::where('employee_group', 'Procedure')->where('is_shareable', true)->get();

        return view('reports.hospital_shares', compact('shares', 'procedures', 'procedureId'));
    }
}
