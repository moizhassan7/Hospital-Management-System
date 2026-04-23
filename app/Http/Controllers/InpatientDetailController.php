<?php

namespace App\Http\Controllers;

use App\Models\InpatientDetail;
use App\Models\IndoorPatient;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InpatientDetailController extends Controller
{
    /**
     * Show the inpatient details form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('inpatient_details.inpatient_details_form');
    }

    /**
     * Store consultant visit and payment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'mr_no' => 'required|string',
            'consultant_visits' => 'nullable|json',
            'payment_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Find the indoor patient to link the record
            $indoorPatient = IndoorPatient::where('mr_no', $request->mr_no)->latest()->firstOrFail();

            // Find or create the InpatientDetails record
            $inpatientDetail = InpatientDetail::firstOrNew(['indoor_patient_id' => $indoorPatient->id]);

            // Update consultant visits
            if ($request->filled('consultant_visits')) {
                $newVisits = json_decode($request->consultant_visits, true);
                $existingVisits = $inpatientDetail->consultant_visits ?? [];
                
                // Merge new visits into existing ones
                $inpatientDetail->consultant_visits = array_merge($existingVisits, $newVisits);
            }

            // Update payment history
            if ($request->filled('payment_amount') && $request->payment_amount > 0) {
                $newPayment = [
                    'amount' => $request->payment_amount,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                    'description' => 'Advance payment by patient.'
                ];
                $paymentHistory = $inpatientDetail->payment_history ?? [];
                $paymentHistory[] = $newPayment;
                $inpatientDetail->payment_history = $paymentHistory;
            }

            $inpatientDetail->patient_id = $indoorPatient->patient_id;
            $inpatientDetail->save();
            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully!',
                'inpatient_detail' => $inpatientDetail
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save record.', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get inpatient details for a given MR number.
     *
     * @param  string  $mr_no
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailsByMrNo($mr_no)
    {
        $indoorPatient = IndoorPatient::where('mr_no', $mr_no)->latest()->first();
        if (!$indoorPatient) {
            return response()->json(['found' => false]);
        }

        $patient = Patient::where('mr_number', $mr_no)->first();
        $inpatientDetail = InpatientDetail::where('indoor_patient_id', $indoorPatient->id)->first();

        return response()->json([
            'found' => true,
            'patient' => $patient,
            'indoor_patient' => $indoorPatient,
            'inpatient_detail' => $inpatientDetail,
        ]);
    }
}
