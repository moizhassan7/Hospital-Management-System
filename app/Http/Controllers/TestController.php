<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestHead;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $category = 'Pathology';
        $tests = Test::with('testHead')->where('category', 'Pathology')->get();
        $testHeads = TestHead::where('category', 'Pathology')->get();

        return view('laboratory.manage_test', compact('tests', 'testHeads', 'category'));
    }

    public function edit(Test $test)
    {
        $category = 'Pathology';
        $tests = Test::with('testHead')->where('category', 'Pathology')->get();
        $testHeads = TestHead::where('category', 'Pathology')->get();

        return view('laboratory.manage_test', compact('tests', 'testHeads', 'test', 'category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:255',
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
            'sample_expiry_hours' => 'nullable|integer|min:1',
            'sample_vial' => 'nullable|string|max:255',
            'vials_required' => 'nullable|integer|min:1|max:10',
            'vial_volume' => 'nullable|string|max:255',
        ]);

        Test::create([
            'test_id' => Test::generateNextTestId('Pathology'),
            'name' => $request->test_name,
            'price' => 0,
            'type' => $request->test_type,
            'report_format' => 'Quantitative',
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
            'category' => 'Pathology',
            'sample_expiry_hours' => $request->sample_expiry_hours,
            'sample_vial' => $request->sample_vial,
            'vials_required' => $request->vials_required ?? 1,
            'vial_volume' => $request->vial_volume,
        ]);

        return redirect()->route('pathology.manage_test')->with('success', 'Test added successfully!');
    }

    public function update(Request $request, Test $test)
    {
        $request->validate([
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:255',
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
            'sample_expiry_hours' => 'nullable|integer|min:1',
            'sample_vial' => 'nullable|string|max:255',
            'vials_required' => 'nullable|integer|min:1|max:10',
            'vial_volume' => 'nullable|string|max:255',
        ]);

        $test->update([
            'name' => $request->test_name,
            'type' => $request->test_type,
            'report_format' => 'Quantitative',
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
            'category' => 'Pathology',
            'sample_expiry_hours' => $request->sample_expiry_hours,
            'sample_vial' => $request->sample_vial,
            'vials_required' => $request->vials_required ?? 1,
            'vial_volume' => $request->vial_volume,
        ]);

        return redirect()->route('pathology.manage_test')->with('success', 'Test updated successfully!');
    }

    public function destroy(Test $test)
    {
        $test->delete();

        return redirect()->route('pathology.manage_test')->with('success', 'Test deleted successfully!');
    }
}
