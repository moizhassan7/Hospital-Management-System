<?php

namespace App\Http\Controllers;

use App\Models\Speciality; // Import Speciality model
use App\Models\Department; // Import Department model
use Illuminate\Http\Request;

class SpecialityController extends Controller
{
    /**
     * Display the form to add a new speciality and a list of existing specialities.
     * Also handles displaying a speciality for editing.
     */
    public function add(Speciality $speciality = null) // $speciality is optional for 'add' mode
    {
        // Fetch all departments for the dropdown
        $departments = Department::all();
        // Fetch all specialities from the database for the list table
        $specialities = Speciality::with('department')->get(); // Eager load department for display

        // If a speciality model is passed, it means we are in edit mode
        return view('specialities.add', compact('departments', 'specialities', 'speciality'));
    }

    /**
     * Store a newly created speciality in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'department_id' => 'required|exists:departments,id', // Ensure selected department exists
            'speciality_name' => 'required|string|max:255|unique:specialities,name', // Name must be unique
        ]);

        // Create a new Speciality instance
        $speciality = new Speciality();
        $speciality->department_id = $request->input('department_id');
        $speciality->name = $request->input('speciality_name');
        $speciality->save(); // Save the speciality to the database

        // Redirect back with a success message
        return redirect()->route('specialities.add')->with('success', 'Speciality added successfully!');
    }

    /**
     * Update the specified speciality in storage.
     */
    public function update(Request $request, Speciality $speciality)
    {
        // Validate the incoming request data
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'speciality_name' => 'required|string|max:255|unique:specialities,name,' . $speciality->id, // Unique except for itself
        ]);

        // Update the Speciality instance
        $speciality->department_id = $request->input('department_id');
        $speciality->name = $request->input('speciality_name');
        $speciality->save(); // Save the updated speciality

        // Redirect back with a success message
        return redirect()->route('specialities.add')->with('success', 'Speciality updated successfully!');
    }

    /**
     * Remove the specified speciality from storage.
     */
    public function destroy(Speciality $speciality)
    {
        $speciality->delete(); // Delete the speciality

        // Redirect back with a success message
        return redirect()->route('specialities.add')->with('success', 'Speciality deleted successfully!');
    }
}