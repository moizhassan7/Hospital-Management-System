<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Department; // For dropdown
use App\Models\Speciality; // For dropdown
use App\Models\DoctorType; // For dropdown
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AddDoctorController extends Controller
{
    public function index()
    {
        return view('doctors.dashboard');
    }

    public function showAll(Request $request)
    {
        $query = Doctor::with(['department', 'speciality']);
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('code', 'like', '%' . $searchTerm . '%');
        }

        $doctors = $query->paginate(15); // Paginate with 15 records per page
        
        return view('doctors.all', compact('doctors'));
    }

    // This method handles displaying the form (add or edit).
    public function create(Doctor $doctor = null)
    {
        $departments = Department::all();
        $specialities = Speciality::all();
        $doctorTypes = DoctorType::all();
        
        return view('doctors.create', compact('doctor', 'departments', 'specialities', 'doctorTypes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'doctor_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'registration_date' => 'required|date',
            'doctor_type' => 'required|string|max:255',
            'doctor_code' => 'required|string|max:255|unique:doctors,code',
            'department_id' => 'required|exists:departments,id',
            'speciality_id' => 'nullable|exists:specialities,id',
            'room_location' => 'nullable|string|max:255',
            'employee_group' => 'nullable|string|max:255',
            'working_days' => 'nullable|array',
            'doctor_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'mobile_number' => 'nullable|string|max:255',
            'office_phone' => 'nullable|string|max:255',
            'reception_phone' => 'nullable|string|max:255',
            'accounts_of' => 'nullable|string|max:255',
            'fee' => 'required|numeric|min:0',
            // New validation rules
            'general_normal_fee' => 'required|numeric|min:0',
            'general_emergency_fee' => 'required|numeric|min:0',
            'welfare_normal_fee' => 'required|numeric|min:0',
            'welfare_emergency_fee' => 'required|numeric|min:0',
            'general_normal_percentage' => 'required|numeric|min:0|max:100',
            'general_emergency_percentage' => 'required|numeric|min:0|max:100',
            'welfare_normal_percentage' => 'required|numeric|min:0|max:100',
            'welfare_emergency_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('doctor_picture')) {
            $imagePath = $request->file('doctor_picture')->store('doctor_pictures', 'public');
            $validatedData['picture'] = $imagePath;
        }

        $validatedData['working_days'] = $validatedData['working_days'] ?? [];
        $validatedData['is_active'] = $request->has('is_active');
        $validatedData['name'] = $validatedData['doctor_name'];
        $validatedData['code'] = $validatedData['doctor_code'];
        $validatedData['type'] = $validatedData['doctor_type'];

        unset($validatedData['doctor_name'], $validatedData['doctor_code'], $validatedData['doctor_type']);

        Doctor::create($validatedData);

        return redirect()->route('doctors.index')->with('success', 'Doctor added successfully!');
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validatedData = $request->validate([
            'doctor_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'registration_date' => 'required|date',
            'doctor_type' => 'required|string|max:255',
            'doctor_code' => 'required|string|max:255|unique:doctors,code,' . $doctor->id,
            'department_id' => 'required|exists:departments,id',
            'speciality_id' => 'nullable|exists:specialities,id',
            'room_location' => 'nullable|string|max:255',
            'employee_group' => 'nullable|string|max:255',
            'working_days' => 'nullable|array',
            'doctor_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'mobile_number' => 'nullable|string|max:255',
            'office_phone' => 'nullable|string|max:255',
            'reception_phone' => 'nullable|string|max:255',
            'accounts_of' => 'nullable|string|max:255',
            'fee' => 'required|numeric|min:0',
            // New validation rules
            'general_normal_fee' => 'required|numeric|min:0',
            'general_emergency_fee' => 'required|numeric|min:0',
            'welfare_normal_fee' => 'required|numeric|min:0',
            'welfare_emergency_fee' => 'required|numeric|min:0',
            'general_normal_percentage' => 'required|numeric|min:0|max:100',
            'general_emergency_percentage' => 'required|numeric|min:0|max:100',
            'welfare_normal_percentage' => 'required|numeric|min:0|max:100',
            'welfare_emergency_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('doctor_picture')) {
            if ($doctor->picture) {
                Storage::disk('public')->delete($doctor->picture);
            }
            $imagePath = $request->file('doctor_picture')->store('doctor_pictures', 'public');
            $validatedData['picture'] = $imagePath;
        }

        $validatedData['working_days'] = $validatedData['working_days'] ?? [];
        $validatedData['is_active'] = $request->has('is_active');
        $validatedData['name'] = $validatedData['doctor_name'];
        $validatedData['code'] = $validatedData['doctor_code'];
        $validatedData['type'] = $validatedData['doctor_type'];

        unset($validatedData['doctor_name'], $validatedData['doctor_code'], $validatedData['doctor_type']);

        $doctor->update($validatedData);

        return redirect()->route('doctors.index')->with('success', 'Doctor updated successfully!');
    }

    // This method handles deleting a doctor.
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return redirect()->route('doctors.all')->with('success', 'Doctor deleted successfully!');
    }
}