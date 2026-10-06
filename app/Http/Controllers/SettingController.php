<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Must be admin, middleware handles this or we can check here
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        $setting = \App\Models\Setting::first();
        return view('settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'hospital_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'currency_symbol' => 'required|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $setting = \App\Models\Setting::first();
        if (!$setting) {
            $setting = new \App\Models\Setting();
        }

        $setting->hospital_name = $request->hospital_name;
        $setting->address = $request->address;
        $setting->currency_symbol = $request->currency_symbol;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $setting->logo = $logoPath;
        }

        $setting->save();

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('hospital_settings');

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }
}
