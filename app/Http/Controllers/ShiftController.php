<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the shifts and the form to add/edit.
     */
    public function add(Shift $shift = null)
    {
        $shifts = Shift::all();
        return view('shifts.add', compact('shifts', 'shift'));
    }

    /**
     * Store a newly created shift in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        Shift::create($request->all());

        return redirect()->route('shifts.add')->with('success', 'Shift added successfully!');
    }

    /**
     * Update the specified shift in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $shift->update($request->all());

        return redirect()->route('shifts.add')->with('success', 'Shift updated successfully!');
    }

    /**
     * Remove the specified shift from storage.
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()->route('shifts.add')->with('success', 'Shift deleted successfully!');
    }
}
