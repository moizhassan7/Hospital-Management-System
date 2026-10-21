<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiagnosisController extends Controller
{
    /**
     * List diagnoses with the add form; with a model bound, the form edits it.
     */
    public function index(?Diagnosis $diagnosis = null)
    {
        $diagnoses = Diagnosis::orderBy('name')->get();

        return view('diagnoses.index', compact('diagnoses', 'diagnosis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('diagnoses', 'name')],
        ], [], ['name' => 'diagnosis name']);

        Diagnosis::create(['name' => trim($validated['name'])]);

        return redirect()->route('diagnoses.index')->with('success', 'Diagnosis added.');
    }

    public function update(Request $request, Diagnosis $diagnosis)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('diagnoses', 'name')->ignore($diagnosis->id)],
        ], [], ['name' => 'diagnosis name']);

        $diagnosis->update(['name' => trim($validated['name'])]);

        return redirect()->route('diagnoses.index')->with('success', 'Diagnosis updated.');
    }

    public function destroy(Diagnosis $diagnosis)
    {
        $diagnosis->delete();

        return redirect()->route('diagnoses.index')->with('success', 'Diagnosis deleted.');
    }
}
