<?php

namespace App\Http\Controllers;

use App\Models\Floor; // Import Floor model
use Illuminate\Http\Request;

class FloorController extends Controller
{
    /**
     * Display the form to add a new floor and a list of existing floors.
     * Also handles displaying a floor for editing.
     */
    public function add(Floor $floor = null) // $floor is optional for 'add' mode
    {
        // Fetch all floors from the database for the list table
        $floors = Floor::all();

        // If a floor model is passed, it means we are in edit mode
        return view('floors.add', compact('floors', 'floor'));
    }

    /**
     * Store a newly created floor in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'floor_name' => 'required|string|max:255|unique:floors,name', // Name must be unique
        ]);

        // Create a new Floor instance
        $floor = new Floor();
        $floor->name = $request->input('floor_name');
        $floor->save(); // Save the floor to the database

        // Redirect back with a success message
        return redirect()->route('floors.add')->with('success', 'Floor added successfully!');
    }

    /**
     * Update the specified floor in storage.
     */
    public function update(Request $request, Floor $floor)
    {
        // Validate the incoming request data
        $request->validate([
            'floor_name' => 'required|string|max:255|unique:floors,name,' . $floor->id, // Unique except for itself
        ]);

        // Update the Floor instance
        $floor->name = $request->input('floor_name');
        $floor->save(); // Save the updated floor

        // Redirect back with a success message
        return redirect()->route('floors.add')->with('success', 'Floor updated successfully!');
    }

    /**
     * Remove the specified floor from storage.
     */
    public function destroy(Floor $floor)
    {
        $floor->delete(); // Delete the floor

        // Redirect back with a success message
        return redirect()->route('floors.add')->with('success', 'Floor deleted successfully!');
    }
}