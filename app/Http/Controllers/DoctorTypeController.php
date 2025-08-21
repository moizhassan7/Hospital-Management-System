<?php

namespace App\Http\Controllers;

use App\Models\DoctorType; // Import DoctorType model
use Illuminate\Http\Request;

class DoctorTypeController extends Controller
{
    /**
     * Display the form to add a new doctor type and a list of existing types.
     * Also handles displaying a doctor type for editing.
     */
    public function add(DoctorType $doctorType = null) // $doctorType is optional for 'add' mode
    {
        // Fetch all doctor types from the database for the list table
        $doctorTypes = DoctorType::all();

        // If a doctorType model is passed, it means we are in edit mode
        return view('doctor_types.add', compact('doctorTypes', 'doctorType'));
    }

    /**
     * Store a newly created doctor type in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'doctor_type_name' => 'required|string|max:255|unique:doctor_types,name', // Name must be unique
        ]);

        // Create a new DoctorType instance
        $doctorType = new DoctorType();
        $doctorType->name = $request->input('doctor_type_name');
        $doctorType->save(); // Save the doctor type to the database

        // Redirect back with a success message
        return redirect()->route('doctor_types.add')->with('success', 'Doctor Type added successfully!');
    }

    /**
     * Update the specified doctor type in storage.
     */
    public function update(Request $request, DoctorType $doctorType)
    {
        // Validate the incoming request data
        $request->validate([
            'doctor_type_name' => 'required|string|max:255|unique:doctor_types,name,' . $doctorType->id, // Unique except for itself
        ]);

        // Update the DoctorType instance
        $doctorType->name = $request->input('doctor_type_name');
        $doctorType->save(); // Save the updated doctor type

        // Redirect back with a success message
        return redirect()->route('doctor_types.add')->with('success', 'Doctor Type updated successfully!');
    }

    /**
     * Remove the specified doctor type from storage.
     */
    public function destroy(DoctorType $doctorType)
    {
        $doctorType->delete(); // Delete the doctor type

        // Redirect back with a success message
        return redirect()->route('doctor_types.add')->with('success', 'Doctor Type deleted successfully!');
    }
}