<?php

namespace App\Http\Controllers;

use App\Models\EmergencyCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmergencyChargeController extends Controller
{
    public function add(EmergencyCharge $emergencyCharge = null)
    {
        $emergencyCharges = EmergencyCharge::all();

        return view('emergency_charges.add', compact('emergencyCharges', 'emergencyCharge'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_hour_charges' => 'required|numeric|min:0',
            'other_hours_charges' => 'required|numeric|min:0',
        ]);

        $lastChargeId = EmergencyCharge::orderBy('id', 'desc')->value('charge_id');
        $newId = $lastChargeId ? (int) substr($lastChargeId, 3) + 1 : 1;
        $chargeId = 'EC' . str_pad($newId, 3, '0', STR_PAD_LEFT);

        EmergencyCharge::create([
            'charge_id' => $chargeId,
            'first_hour_charges' => $request->input('first_hour_charges'),
            'other_hours_charges' => $request->input('other_hours_charges'),
        ]);

        return redirect()->route('emergency_charges.add')->with('success', 'Emergency Charge added successfully!');
    }

    public function update(Request $request, EmergencyCharge $emergencyCharge)
    {
        $request->validate([
            'first_hour_charges' => 'required|numeric|min:0',
            'other_hours_charges' => 'required|numeric|min:0',
        ]);

        $emergencyCharge->update([
            'first_hour_charges' => $request->input('first_hour_charges'),
            'other_hours_charges' => $request->input('other_hours_charges'),
        ]);

        return redirect()->route('emergency_charges.add')->with('success', 'Emergency Charge updated successfully!');
    }

    public function destroy(EmergencyCharge $emergencyCharge)
    {
        $emergencyCharge->delete();
        return redirect()->route('emergency_charges.add')->with('success', 'Emergency Charge deleted successfully!');
    }
}