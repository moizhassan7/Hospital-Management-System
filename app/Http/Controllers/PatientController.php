<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\IndoorPatient; 
use App\Models\Room;
use App\Models\Doctor;
use App\Models\OpdAppointment;
use App\Services\OpdAppointmentService;
use App\Services\OpdTokenService;
use App\Support\OpdStates;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index()
    {
        return view('patients.index');
    }

    public function register(Patient $patient = null)
    {
        // Find the last patient to get the last MR number
        $lastPatient = Patient::orderBy('id', 'desc')->first();
        // If a patient exists, increment the last MR number
        // Otherwise, start from 1
        $nextMrNumber = $lastPatient ? ((int)$lastPatient->mr_number + 1) : 1;
        // Format the new MR number as a 5-digit string with leading zeros
        $formattedMrNumber = str_pad($nextMrNumber, 5, '0', STR_PAD_LEFT);

        return view('patients.patient_registration', compact('patient', 'formattedMrNumber'));
    }

    public function indoorRegister()
    {
        $wards = Room::where('is_ward', true)->get();
        $rooms = Room::where('is_ward', false)->get();
        $doctors = Doctor::all();

        // Get the next sequential number for file/slip numbers
        $lastPatient = IndoorPatient::orderBy('id', 'desc')->first();
        $nextSequentialNumber = $lastPatient ? ((int)substr($lastPatient->file_no, -5) + 1) : 1;
        $formattedNextNumber = str_pad($nextSequentialNumber, 5, '0', STR_PAD_LEFT);

        return view('patients.indoor_register', compact('wards', 'rooms', 'doctors', 'formattedNextNumber'));
    }

    public function outdoorRegister()
    {
        $doctors = Doctor::all();
        $today = now()->toDateString();
        $lastAppointment = OpdAppointment::whereDate('created_at', $today)->orderBy('created_at', 'desc')->first();

        $dailySerial = 1;
        if ($lastAppointment) {
            $parts = explode('-', $lastAppointment->appointment_number);
            if (isset($parts[2])) {
                $dailySerial = (int)$parts[2] + 1;
            } else {
                // Fallback for old formats like 'OPD-1' or 'OPD-xyz'
                $dailySerial = 1;
            }
        }

        return view('patients.outdoor_register', compact('doctors', 'dailySerial'));
    }

    public function storeOutdoor(Request $request)
    {
        $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'mr_number' => 'required|string',
            'patient_name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'doctor_code' => 'required|string',
            'doctor_name' => 'required|string',
            'doctor_fee' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'token_number' => 'required|numeric',
            'referred_by' => 'nullable|string',
            'keep_original_token' => 'sometimes|boolean',
            'payment_method' => 'nullable|in:cash,bank,cheque',
            'idempotency_key' => 'nullable|string|max:80',
        ]);

        $hadBooking = OpdAppointment::query()
            ->where('mr_number', $request->mr_number)
            ->whereDate('appointment_date', $request->appointment_date)
            ->whereIn('status', OpdStates::OPEN_APPOINTMENT)
            ->exists();

        $appointment = app(OpdAppointmentService::class)->registerVisit($request->all(), auth()->id());

        $message = $hadBooking
            ? 'Pre-booked appointment checked in. Token #'.$appointment->token_number.' confirmed.'
            : 'Outdoor patient registered successfully! Your Appointment Number is '.$appointment->appointment_number;

        return response()->json([
            'message' => $message,
            'redirect_url' => route('patients.outdoor_register'),
            'receipt_url' => route('patients.outdoor_receipt', $appointment->appointment_number),
            'appointment' => app(OpdAppointmentController::class)->payload($appointment),
        ], $hadBooking ? 200 : 201);
    }

    public function outdoorReceipt($appointment_number)
    {
        $appointment = OpdAppointment::where('appointment_number', $appointment_number)->firstOrFail();
        return view('patients.outdoor_receipt', compact('appointment'));
    }

    public function latestOpdAppointment($mr_number)
    {
        $appointment = OpdAppointment::where('mr_number', $mr_number)->orderBy('created_at', 'desc')->first();
        if ($appointment) {
            return response()->json(['success' => true, 'appointment_number' => $appointment->appointment_number]);
        }
        return response()->json(['success' => false]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mr_number' => 'required|unique:patients,mr_number',
            'registration_date' => 'required|date',
            'name' => 'required|string|max:255',
            'marital_status' => 'required|string',
            'date_of_birth' => 'required|date',
            'relation_type' => 'nullable|string',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_cnic' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric',
            'gender' => 'required|string',
            'email' => 'nullable|email|max:255',
            'cnic' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'mobile_number' => 'nullable|string|max:20',
            'is_welfare' => 'required|boolean',
        ]);

        // Calculate age from date of birth
        $dateOfBirth = Carbon::parse($request->date_of_birth);
        $age = $dateOfBirth->age;

        // Remove 'age' from the request data as it's auto-calculated
        $data = $request->except(['age']);
        $data['age'] = $age;

        // Create a new patient record with the validated data and calculated age
        Patient::create($data);

        return redirect()->route('patients.all')->with('success', 'Patient registered successfully!');
    }

    public function storeIndoor(Request $request)
    {
        $request->validate([
            'mr_no' => 'required|string|max:255',
            'registration_date' => 'required|date',
            'patient_name' => 'required|string|max:255',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'file_no' => 'required|string|max:255|unique:indoor_patients,file_no',
            'slip_no' => 'required|string|max:255|unique:indoor_patients,slip_no',
            'admission_type' => 'required|in:ward,room',
            'ward_number' => 'nullable|required_if:admission_type,ward|exists:rooms,id',
            'room_id' => 'nullable|required_if:admission_type,room|exists:rooms,id',
            'bed_no' => 'nullable|string|max:255',
            'admission_fee' => 'required|numeric',
            'advance_fee' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'consultant_id' => 'nullable|numeric|exists:doctors,id',
            'is_operation' => 'boolean',
            'operation_date' => 'nullable|date',
        ]);

        // 1. Find the patient record using the MR No.
        $patient = Patient::where('mr_number', $request->mr_no)->first();

        if (!$patient) {
            return redirect()->back()->withErrors(['mr_no' => 'No patient found with this MR Number.']);
        }

        // 2. Prepare the data for the new IndoorPatient record
        $data = $request->only([
            'mr_no',
            'file_no',
            'slip_no',
            'registration_date',
            'admission_fee',
            'advance_fee',
            'total_amount',
            'is_operation',
            'operation_date',
            'bed_no',
            'consultant_id'
        ]);
        
        // Add the patient_id and admission type to the data
        $data['patient_id'] = $patient->id;
        $data['admission_type'] = $request->admission_type;
        $data['received_by'] = auth()->id();
        
        // Find the room/ward and decrement the bed count
        $room = null;
        if ($request->admission_type === 'ward' && $request->ward_number) {
            $room = Room::find($request->ward_number);
            $data['ward_id'] = $request->ward_number;
        } elseif ($request->admission_type === 'room' && $request->room_id) {
            $room = Room::find($request->room_id);
            $data['room_id'] = $request->room_id;
        }

        // Check if the room exists and has beds, if it's a ward.
        if ($room && $room->is_ward) {
            if ($room->number_of_beds <= 0) {
                return redirect()->back()->withErrors(['ward_number' => 'No beds available in this ward.']);
            }
        }
        
        DB::transaction(function () use ($data, $room) {
            // The patient record and its accounting receipt are committed together.
            IndoorPatient::create($data);

            if ($room && $room->is_ward) {
                $room->number_of_beds -= 1;
                $room->save();
            }
        });

        // 5. Redirect with a success message
        return redirect()->route('patients.indoor_register')->with('success', 'Indoor Patient registered successfully!');
    }
    
    public function showAll()
    {
        $patients = Patient::all();
        return view('patients.all', compact('patients'));
    }

    /**
     * Patient profile: demographics plus recent visits across modules.
     */
    public function show(Patient $patient)
    {
        $limit = 10;

        $opd = OpdAppointment::where('mr_number', $patient->mr_number);
        $admissions = IndoorPatient::with(['ward', 'room', 'consultant'])->where('patient_id', $patient->id);
        $emergency = \App\Models\EmergencyPatient::where('mr_number', $patient->mr_number);
        $dayCare = \App\Models\DayCareProcedure::where(function ($query) use ($patient) {
            $query->where('patient_id', $patient->id)->orWhere('mr_no', $patient->mr_number);
        });
        $laboratory = \App\Models\LaboratoryPatient::where('mr_no', $patient->mr_number);

        $history = [
            'opd' => [
                'count' => (clone $opd)->count(),
                'records' => $opd->orderByDesc('appointment_date')->orderByDesc('id')->limit($limit)->get(),
            ],
            'admissions' => [
                'count' => (clone $admissions)->count(),
                'records' => $admissions->orderByDesc('registration_date')->orderByDesc('id')->limit($limit)->get(),
            ],
            'emergency' => [
                'count' => (clone $emergency)->count(),
                'records' => $emergency->latest()->limit($limit)->get(),
            ],
            'day_care' => [
                'count' => (clone $dayCare)->count(),
                'records' => $dayCare->latest()->limit($limit)->get(),
            ],
            'laboratory' => [
                'count' => (clone $laboratory)->count(),
                'records' => $laboratory->latest()->limit($limit)->get(),
            ],
        ];

        return view('patients.show', compact('patient', 'history', 'limit'));
    }

    /**
     * Update a patient's registration details. The MR number cannot change
     * because other modules reference the patient by it.
     */
    public function update(Request $request, Patient $patient)
    {
        $request->validate([
            'registration_date' => 'required|date',
            'name' => 'required|string|max:255',
            'marital_status' => 'required|string',
            'date_of_birth' => 'required|date',
            'relation_type' => 'nullable|string',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_cnic' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric',
            'gender' => 'required|string',
            'email' => 'nullable|email|max:255',
            'cnic' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'mobile_number' => 'nullable|string|max:20',
            'is_welfare' => 'required|boolean',
        ]);

        $data = $request->only([
            'registration_date', 'name', 'marital_status', 'date_of_birth', 'relation_type', 'guardian_name',
            'guardian_cnic', 'weight', 'gender', 'email', 'cnic', 'address', 'mobile_number', 'is_welfare',
        ]);
        $data['age'] = Carbon::parse($request->date_of_birth)->age;

        $patient->update($data);

        return redirect()->route('patients.show', $patient)->with('success', 'Patient details updated.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return redirect()->route('patients.all')->with('success', 'Patient deleted successfully!');
    }

    /**
     * Get patient details by MR number.
     *
     * @param  string  $mr_no
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatientByMrNo($mr_no)
    {
        $patient = Patient::where('mr_number', $mr_no)->first();
        return response()->json($patient);
    }

    public function bookAppointment()
    {
        $doctors = Doctor::with('department')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('patients.book_appointment', compact('doctors'));
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'mr_number' => 'required|string',
            'doctor_code' => 'required|string',
            'referred_by' => 'nullable|string',
            'patient_name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'token_number' => 'required|integer|min:1',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,bank,cheque',
            'intent' => 'nullable|in:save,pay',
            'idempotency_key' => 'nullable|string|max:80',
            'session_key' => 'nullable|string|max:32',
        ]);

        $collectPayment = ($data['intent'] ?? 'save') === 'pay';
        $appointment = app(OpdAppointmentService::class)->book($data, $collectPayment, auth()->id());

        $message = $collectPayment
            ? 'Appointment booked and fee collected. Token #'.$appointment->token_number.' is reserved. Appointment number '.$appointment->appointment_number.'.'
            : 'Appointment booked without payment. Token #'.$appointment->token_number.' is provisional. Appointment number '.$appointment->appointment_number.'.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'appointment' => app(OpdAppointmentController::class)->payload($appointment),
                'receipt_url' => $collectPayment ? route('patients.outdoor_receipt', $appointment->appointment_number) : null,
            ], 201);
        }

        $redirect = redirect()->route('patients.appointments.show', $appointment)->with('success', $message);
        if ($collectPayment) {
            $redirect->with('receipt_url', route('patients.outdoor_receipt', $appointment->appointment_number));
        }

        return $redirect;
    }
    /**
     * Check if a patient has a booked appointment for today.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkBookedAppointment(Request $request)
    {
        $request->validate([
            'mr_number' => 'required|string',
        ]);

        $bookedAppointment = OpdAppointment::where('mr_number', $request->mr_number)
            ->where('appointment_date', now()->toDateString())
            ->whereIn('status', OpdStates::OPEN_APPOINTMENT)
            ->orderBy('id')
            ->first();

        $inspect = $bookedAppointment ? app(OpdTokenService::class)->inspect($bookedAppointment) : null;

        return response()->json([
            'hasBookedAppointment' => ! is_null($bookedAppointment),
            'appointmentDetails' => $bookedAppointment,
            'token' => $inspect,
            'is_paid' => $bookedAppointment?->isPaid() ?? false,
            'outstanding' => $bookedAppointment?->outstandingFee() ?? 0,
        ]);
    }

    /**
     * Search for patients by name, CNIC, or mobile number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPatient(Request $request)
    {
        $query = $request->input('query');
        $patients = Patient::where('name', 'like', "%{$query}%")
                           ->orWhere('cnic', 'like', "%{$query}%")
                           ->orWhere('mobile_number', 'like', "%{$query}%")
                           ->get();
        return response()->json($patients);
    }

    /**
     * Search diagnosis definitions by name (used by the discharge form).
     */
    public function searchDiagnosis(Request $request)
    {
        $query = mb_strtolower(trim((string) $request->input('query')));
        $diagnoses = \App\Models\Diagnosis::whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json($diagnoses);
    }

    /**
     * Search for doctors by name, department, or doctor code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchDoctor(Request $request)
    {
        $query = $request->input('query');
        // This is the line that needs to be updated.
        $doctors = Doctor::with('department')
                         ->where('name', 'like', "%{$query}%")
                         ->orWhere('code', 'like', "%{$query}%")
                         ->orWhereHas('department', function ($q) use ($query) {
                             $q->where('name', 'like', "%{$query}%");
                         })
                         ->get();

        return response()->json($doctors);
    }
    
    public function generateTokenNumber(Request $request)
    {
        $request->validate([
            'doctor_code' => 'required|string',
            'appointment_date' => 'nullable|date',
            'session_key' => 'nullable|string|max:32',
            'appointment_id' => 'nullable|integer',
        ]);

        $date = $request->input('appointment_date', now()->toDateString());
        $tokens = app(OpdTokenService::class);
        $suggestion = $tokens->suggest(
            $request->input('doctor_code'),
            $date,
            $request->input('session_key'),
            $request->integer('appointment_id') ?: null,
        );

        return response()->json([
            'token_number' => $suggestion,
        ]);
    }
    
    /**
     * Get doctor fee based on patient type and doctor ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDoctorFee(Request $request)
    {
        $request->validate([
            'consultant_id' => 'required|exists:doctors,id',
            'is_welfare' => 'required|boolean',
        ]);

        $doctor = Doctor::findOrFail($request->consultant_id);
        $isWelfare = $request->is_welfare;

        // Determine which fee to use
        $doctorFee = $isWelfare ? $doctor->welfare_normal_fee : $doctor->general_normal_fee;

        return response()->json([
            'fee' => $doctorFee,
        ]);
    }


   public function getIndoorPatientByMrNo($mr_no)
    {
        $indoorPatient = IndoorPatient::where('mr_no', $mr_no)->latest()->first();

        if ($indoorPatient) {
            $patient = Patient::where('mr_number', $indoorPatient->mr_no)->first(); // Fetch patient by mr_no from IndoorPatient
            
            return response()->json([
                'found' => true,
                'patient' => $patient, // Return the full patient object
                'registration_date' => $indoorPatient->registration_date,
                'admission_fee' => $indoorPatient->admission_fee,
                'advance_fee' => $indoorPatient->advance_fee,
            ]);
        }
        
        return response()->json(['found' => false]);
    }
}
