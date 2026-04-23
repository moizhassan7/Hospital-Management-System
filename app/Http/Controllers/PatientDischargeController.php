<?php

namespace App\Http\Controllers;

use App\Models\PatientDischarge;
use App\Models\IndoorPatient;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Diagnosis;
use App\Models\Room;
use App\Models\InpatientDetail; // NEW: Import InpatientDetail model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PatientDischargeController extends Controller
{
    /**
     * Show the patient discharge form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $doctors = Doctor::all();
        $diagnoses = Diagnosis::all();
        return view('patients.discharge', compact('doctors', 'diagnoses'));
    }

    /**
     * Store patient discharge details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
{
    $request->validate([
        'mr_no' => 'required|string',
        'discharge_date' => 'required|date',
        'discharge_time' => 'required',
        'discharge_status' => 'required|string',
        'discharge_summary' => 'nullable|string',
        'medication_at_discharge' => 'nullable|string',
        'follow_up_instructions' => 'nullable|string',
        'certifying_doctor_id' => 'required|exists:doctors,id',
        'payment_clearance_status' => 'required|string',
        'cause' => 'nullable|string',
        'is_anesthesia' => 'nullable|boolean',
        'anesthesia_type' => 'nullable|string',
        'diagnoses' => 'nullable|string',
        'consultants' => 'nullable|string',
        'total_bill' => 'required|numeric',
        'admission_fee' => 'required|numeric',
        'advance_fee' => 'required|numeric',
        'discount' => 'nullable|numeric',
        'amount_receivable' => 'required|numeric',
        'amount_paid' => 'required|numeric',
        'current_remaining' => 'required|numeric',
    ]);

    DB::beginTransaction();

    try {
        // Find the patient and indoor record
        $patient = Patient::where('mr_number', $request->mr_no)->firstOrFail();
        $indoorPatient = IndoorPatient::where('patient_id', $patient->id)
            ->latest()
            ->firstOrFail();

        // Create PatientDischarge record
        PatientDischarge::create([
            'indoor_patient_id' => $indoorPatient->id,
            'patient_id' => $patient->id,
            'discharge_date' => $request->discharge_date,
            'discharge_time' => $request->discharge_time,
            'discharge_status' => $request->discharge_status,
            'discharge_summary' => $request->discharge_summary,
            'medication_at_discharge' => $request->medication_at_discharge,
            'follow_up_instructions' => $request->follow_up_instructions,
            'certifying_doctor_id' => $request->certifying_doctor_id,
            'payment_clearance_status' => $request->payment_clearance_status,
            'cause' => $request->cause,
            'is_anesthesia' => (bool)$request->is_anesthesia,
            'anesthesia_type' => $request->anesthesia_type,
            'diagnoses' => $request->diagnoses,
            'consultants' => $request->consultants,
            'total_bill' => $request->total_bill,
            'admission_fee' => $request->admission_fee,
            'advance_fee' => $request->advance_fee,
            'discount' => $request->discount,
            'amount_receivable' => $request->amount_receivable,
            'amount_paid' => $request->amount_paid,
            'current_remaining' => $request->current_remaining,
        ]);

        // ✅ Update IndoorPatient status (instead of deleting)
        $indoorPatient->update([
            'is_discharged' => true,
            'discharge_date' => $request->discharge_date,
            'discharge_time' => $request->discharge_time,
            'discharge_status' => $request->discharge_status,
        ]);

        // ✅ Free up resources
        if ($indoorPatient->admission_type === 'ward' && $indoorPatient->ward_id) {
            $ward = Room::find($indoorPatient->ward_id);
            if ($ward) {
                $ward->increment('number_of_beds');
            }
        } elseif ($indoorPatient->admission_type === 'room' && $indoorPatient->room_id) {
            $room = Room::find($indoorPatient->room_id);
            if ($room) {
                $room->update(['is_occupied' => false]);
            }
        }

        DB::commit();

        return redirect()->route('patients.discharge')
            ->with('success', 'Patient successfully discharged!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors(['error' => 'An error occurred during discharge. Please try again. ' . $e->getMessage()]);
    }
}

    
    /**
     * Get indoor patient details for discharge.
     * This method is now **obsolete** and is replaced by the API call to `InpatientDetailController@getDetailsByMrNo`
     */
    public function getIndoorPatientByMrNo($mr_no)
{
    $indoorPatient = IndoorPatient::where('mr_no', $mr_no)
        ->where('is_discharged', true) // ✅ only active patients
        ->latest()
        ->first();

    if ($indoorPatient) {
        $patient = Patient::where('mr_number', $indoorPatient->mr_no)->first();
        
        return response()->json([
            'found' => true,
            'patient' => $patient,
            'registration_date' => $indoorPatient->registration_date,
            'admission_fee' => $indoorPatient->admission_fee,
            'advance_fee' => $indoorPatient->advance_fee,
        ]);
    }
    
    return response()->json(['found' => false]);
}

    // Function that remove the patient from opd
   /**
 * Update indoor patient details or remove from OPD after discharge.
 *
 * @param \Illuminate\Http\Request $request
 * @param string $mr_no
 * @return \Illuminate\Http\JsonResponse
 */
public function updateIndoorPatientDetails(Request $request, $mr_no)
{
    DB::beginTransaction();
    try {
        // Find the patient by MR number
        $patient = Patient::where('mr_number', $mr_no)->firstOrFail();

        // Find the latest indoor patient record
        $indoorPatient = IndoorPatient::where('patient_id', $patient->id)->latest()->firstOrFail();

        // Mark inpatient detail as discharged
        $inpatientDetail = InpatientDetail::where('indoor_patient_id', $indoorPatient->id)->first();
        if ($inpatientDetail) {
            $indoorPatient->update([
    'is_discharged' => true,
    'discharge_date' => now()->toDateString(),
    'discharge_time' => now()->toTimeString(),
    'discharge_status' => 'Discharged',
]);
        }

        // Free up the room/ward resources
        if ($indoorPatient->admission_type === 'ward' && $indoorPatient->ward_id) {
            $ward = Room::find($indoorPatient->ward_id);
            if ($ward) {
                $ward->increment('number_of_beds');
            }
        } elseif ($indoorPatient->admission_type === 'room' && $indoorPatient->room_id) {
            $room = Room::find($indoorPatient->room_id);
            if ($room) {
                $room->update(['is_occupied' => false]);
            }
        }

        // Optionally: remove indoor patient record (if you really want deletion)
        // $indoorPatient->delete();

        DB::commit();
        return response()->json(['message' => 'Patient successfully removed from OPD and marked as discharged.']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Failed to update patient details: ' . $e->getMessage()], 500);
    }
}

}