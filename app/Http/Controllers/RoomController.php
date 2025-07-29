<?php

namespace App\Http\Controllers;

use App\Models\Room; // Import Room model
use App\Models\Floor; // Import Floor model
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display the form to add a new room and a list of existing rooms.
     * Also handles displaying a room for editing.
     */
    public function add(Room $room = null) // $room is optional for 'add' mode
    {
        // Fetch all floors for the dropdown
        $floors = Floor::all();
        // Fetch all rooms from the database for the list table
        $rooms = Room::with('floor')->get(); // Eager load floor for display

        // If a room model is passed, it means we are in edit mode
        return view('rooms.add', compact('floors', 'rooms', 'room'));
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'floor_id' => 'required|exists:floors,id', // Ensure selected floor exists
            'room_name' => 'required|string|max:255|unique:rooms,name', // Room name must be unique
            'is_ward' => 'boolean',
            'number_of_beds' => 'nullable|integer|min:0', // Nullable if not a ward
            'per_day_rent' => 'nullable|numeric|min:0', // Nullable if not a ward
        ]);

        // Adjust validation based on 'is_ward' checkbox
        if ($request->has('is_ward')) {
            $request->validate([
                'number_of_beds' => 'required|integer|min:1', // Required if ward
                'per_day_rent' => 'required|numeric|min:0', // Required if ward
            ]);
        }

        // Create a new Room instance
        $room = new Room();
        $room->floor_id = $request->input('floor_id');
        $room->name = $request->input('room_name');
        $room->is_ward = $request->has('is_ward');

        // Set beds and rent only if it's a ward
        if ($room->is_ward) {
            $room->number_of_beds = $request->input('number_of_beds');
            $room->per_day_rent = $request->input('per_day_rent');
        } else {
            $room->number_of_beds = null;
            $room->per_day_rent = null;
        }

        $room->save(); // Save the room to the database

        // Redirect back with a success message
        return redirect()->route('rooms.add')->with('success', 'Room added successfully!');
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, Room $room)
    {
        // Validate the incoming request data
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'room_name' => 'required|string|max:255|unique:rooms,name,' . $room->id, // Unique except for itself
            'is_ward' => 'boolean',
            'number_of_beds' => 'nullable|integer|min:0',
            'per_day_rent' => 'nullable|numeric|min:0',
        ]);

        // Adjust validation based on 'is_ward' checkbox
        if ($request->has('is_ward')) {
            $request->validate([
                'number_of_beds' => 'required|integer|min:1',
                'per_day_rent' => 'required|numeric|min:0',
            ]);
        }

        // Update the Room instance
        $room->floor_id = $request->input('floor_id');
        $room->name = $request->input('room_name');
        $room->is_ward = $request->has('is_ward');

        if ($room->is_ward) {
            $room->number_of_beds = $request->input('number_of_beds');
            $room->per_day_rent = $request->input('per_day_rent');
        } else {
            $room->number_of_beds = null;
            $room->per_day_rent = null;
        }

        $room->save(); // Save the updated room

        // Redirect back with a success message
        return redirect()->route('rooms.add')->with('success', 'Room updated successfully!');
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete(); // Delete the room

        // Redirect back with a success message
        return redirect()->route('rooms.add')->with('success', 'Room deleted successfully!');
    }
}