<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\IndoorPatient; 
use App\Models\Room;
use App\Models\Doctor;
use App\Models\OpdAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        ]);

        $preBookedAppointment = OpdAppointment::where('mr_number', $request->mr_number)
            ->where('appointment_date', $request->appointment_date)
            ->where('status', 'booked')
            ->first();

        if ($preBookedAppointment) {
            $preBookedAppointment->update([
                'appointment_time' => $request->appointment_time,
                'doctor_code' => $request->doctor_code,
                'doctor_name' => $request->doctor_name,
                'doctor_fee' => $request->doctor_fee,
                'total_amount' => $request->total_amount,
                'hospital_share' => $request->hospital_share,
                'status' => 'completed',
            ]);

            return response()->json([
                'message' => 'Patient has a pre-booked appointment. Details updated successfully!',
                'redirect_url' => route('patients.outdoor_register')
            ], 200);
        }

        $today = now()->toDateString();
        $lastAppointment = OpdAppointment::whereDate('created_at', $today)->orderBy('created_at', 'desc')->first();

        $dailySerial = 1;
        if ($lastAppointment) {
            $parts = explode('-', $lastAppointment->appointment_number);
            if (isset($parts[2])) {
                $dailySerial = (int)$parts[2] + 1;
            } else {
                $dailySerial = 1;
            }
        }
        $appointmentNumber = "AP-" . now()->format('Ymd') . "-" . $dailySerial;

        OpdAppointment::create([
        'appointment_number' => $appointmentNumber,
        'appointment_date' => $request->appointment_date,
        'appointment_time' => $request->appointment_time,
        'mr_number' => $request->mr_number,
        'patient_name' => $request->patient_name,
        'age' => $request->age,
        'gender' => $request->gender,
        'doctor_code' => $request->doctor_code,
        'doctor_name' => $request->doctor_name,
        'doctor_fee' => $request->doctor_fee,
        'total_amount' => $request->total_amount,
        'token_number' => $request->token_number, // Add this line
        'referred_by' => $request->referred_by,
        'status' => 'completed',
    ]);

    return response()->json([
        'message' => 'Outdoor patient registered successfully! Your Appointment Number is ' . $appointmentNumber,
        'redirect_url' => route('patients.outdoor_register')
    ], 201);
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
        
        // 3. Create the new indoor patient record in the database
        IndoorPatient::create($data);

        // 4. Decrement the bed count if it's a ward.
        if ($room && $room->is_ward) {
            $room->number_of_beds -= 1;
            $room->save();
        }

        // 5. Redirect with a success message
        return redirect()->route('patients.indoor_register')->with('success', 'Indoor Patient registered successfully!');
    }
    
    public function showAll()
    {
        $patients = Patient::all();
        return view('patients.all', compact('patients'));
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
        $doctors = Doctor::all();
        return view('patients.book_appointment', compact('doctors'));
    }

    public function storeAppointment(Request $request)
    {
        $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'mr_number' => 'required|string',
            'doctor_code' => 'required|string',
            'referred_by' => 'nullable|string',
            'patient_name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
        ]);

        $appointmentDate = $request->appointment_date;
        $lastAppointment = OpdAppointment::where('appointment_date', $appointmentDate)->orderBy('created_at', 'desc')->first();

        $dailySerial = 1;
        if ($lastAppointment) {
            $parts = explode('-', $lastAppointment->appointment_number);
            if (isset($parts[2])) {
                $dailySerial = (int)$parts[2] + 1;
            } else {
                $dailySerial = 1;
            }
        }
        $appointmentNumber = "AP-" . date('Ymd', strtotime($appointmentDate)) . "-" . $dailySerial;

        $doctor = Doctor::where('code', $request->doctor_code)->firstOrFail();

        OpdAppointment::create([
            'appointment_number' => $appointmentNumber,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'mr_number' => $request->mr_number,
            'patient_name' => $request->patient_name,
            'age' => $request->age,
            'gender' => $request->gender,
            'doctor_code' => $request->doctor_code,
            'doctor_name' => $doctor->name,
            'doctor_fee' => $doctor->fee,
            'referred_by' => $request->referred_by,
            'total_amount' => $doctor->fee,
            'hospital_share' => $doctor->fee * 0.40,
            'status' => 'booked',
        ]);

        return redirect()->route('patients.book_appointment')->with('success', 'Appointment booked successfully! Your Appointment Number is ' . $appointmentNumber);
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
            ->where('status', 'booked')
            ->first();

        return response()->json([
            'hasBookedAppointment' => !is_null($bookedAppointment),
            'appointmentDetails' => $bookedAppointment,
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
        $doctorCode = $request->input('doctor_code');
        $today = now()->toDateString();
        
        // Find the last appointment for this doctor on the current day
        $lastAppointment = OpdAppointment::where('doctor_code', $doctorCode)
                                        ->whereDate('created_at', $today)
                                        ->orderBy('token_number', 'desc')
                                        ->first();
        
        $nextToken = 1;
        if ($lastAppointment) {
            $nextToken = $lastAppointment->token_number + 1;
        }

        return response()->json([
            'token_number' => $nextToken,
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