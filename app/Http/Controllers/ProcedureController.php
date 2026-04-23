<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Models\Department;
use App\Models\Speciality;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    public function index(Request $request)
    {
        $query = Procedure::with(['department', 'speciality']);

        if ($request->has('filter') && in_array($request->filter, ['Major', 'Minor'])) {
            $query->where('type', $request->filter);
        }

        $procedures = $query->paginate(15);

        return view('procedures.index', compact('procedures'));
    }

    public function create(Procedure $procedure = null)
    {
        $departments = Department::all();
        $specialities = Speciality::all();

        return view('procedures.create', compact('procedure', 'departments', 'specialities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Major,Minor',
            'department_id' => 'required|exists:departments,id',
            'speciality_id' => 'nullable|exists:specialities,id',
            'room_location' => 'nullable|string',
            'name' => 'required|string',
            'performed_for' => 'required|in:General,Male,Female',
            'general_normal_fee' => 'required|numeric|min:0',
            'general_emergency_fee' => 'required|numeric|min:0',
            'welfare_normal_fee' => 'required|numeric|min:0',
            'welfare_emergency_fee' => 'required|numeric|min:0',
            'general_normal_doctor_percentage' => 'required|numeric|min:0|max:100',
            'general_emergency_doctor_percentage' => 'required|numeric|min:0|max:100',
            'welfare_normal_doctor_percentage' => 'required|numeric|min:0|max:100',
            'welfare_emergency_doctor_percentage' => 'required|numeric|min:0|max:100',
        ]);

        Procedure::create($request->all());

        return redirect()->route('procedures.index')->with('success', 'Procedure added successfully!');
    }

    public function update(Request $request, Procedure $procedure)
    {
        $request->validate([
            'type' => 'required|in:Major,Minor',
            'department_id' => 'required|exists:departments,id',
            'speciality_id' => 'nullable|exists:specialities,id',
            'room_location' => 'nullable|string',
            'name' => 'required|string',
            'performed_for' => 'required|in:General,Male,Female',
            'general_normal_fee' => 'required|numeric|min:0',
            'general_emergency_fee' => 'required|numeric|min:0',
            'welfare_normal_fee' => 'required|numeric|min:0',
            'welfare_emergency_fee' => 'required|numeric|min:0',
            'general_normal_doctor_percentage' => 'required|numeric|min:0|max:100',
            'general_emergency_doctor_percentage' => 'required|numeric|min:0|max:100',
            'welfare_normal_doctor_percentage' => 'required|numeric|min:0|max:100',
            'welfare_emergency_doctor_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $procedure->update($request->all());

        return redirect()->route('procedures.index')->with('success', 'Procedure updated successfully!');
    }

    public function destroy(Procedure $procedure)
    {
        $procedure->delete();
        return redirect()->route('procedures.index')->with('success', 'Procedure deleted successfully!');
    }
}