<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display the form to add a new department and a list of existing departments.
     * Also handles displaying a department for editing.
     */
    public function add(Department $department = null) // $department is optional for 'add' mode
    {
        // Fetch all departments from the database for the list table
        $departments = Department::all();

        // If a department model is passed, it means we are in edit mode
        return view('departments.add', compact('departments', 'department'));
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,name', // Name must be unique
            'number_of_beds' => 'required|integer|min:0',
            'is_active' => 'boolean', // Checkbox value will be 0 or 1
        ]);

        // Create a new Department instance and fill it with validated data
        $department = new Department();
        $department->name = $request->input('department_name');
        $department->number_of_beds = $request->input('number_of_beds');
        $department->is_active = $request->has('is_active'); // Correctly handles checkbox value
        $department->save(); // Save the department to the database

        // Redirect back to the add department page with a success message
        return redirect()->route('departments.add')->with('success', 'Department added successfully!');
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        // Validate the incoming request data
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,name,' . $department->id, // Name must be unique, except for current department
            'number_of_beds' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Update the Department instance with validated data
        $department->name = $request->input('department_name');
        $department->number_of_beds = $request->input('number_of_beds');
        $department->is_active = $request->has('is_active');
        $department->save(); // Save the updated department to the database

        // Redirect back to the add department page with a success message
        return redirect()->route('departments.add')->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete(); // Delete the department from the database

        // Redirect back to the add department page with a success message
        return redirect()->route('departments.add')->with('success', 'Department deleted successfully!');
    }
}