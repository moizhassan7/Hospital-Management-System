<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestHead;
use App\Models\TestParticular;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Display a listing of the tests.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : null);
        $query = Test::with('testHead');
        $headQuery = TestHead::query();
        
        if ($category) {
            $query->where('category', $category);
            $headQuery->where('category', $category);
        }
        
        $tests = $query->get();
        $testHeads = $headQuery->get();

        return view('laboratory.manage_test', compact('tests', 'testHeads', 'category'));
    }

    /**
     * Show the form for editing the specified test.
     *
     * @param  \App\Models\Test  $test
     * @return \Illuminate\View\View
     */
    public function edit(Test $test)
    {
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : $test->category);
        $query = Test::with('testHead');
        $headQuery = TestHead::query();
        
        if ($category) {
            $query->where('category', $category);
            $headQuery->where('category', $category);
        }
        
        $tests = $query->get();
        $testHeads = $headQuery->get();

        return view('laboratory.manage_test', compact('tests', 'testHeads', 'test', 'category'));
    }

    /**
     * Store a new test in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'test_id' => 'required|string|max:255|unique:tests,test_id',
            'test_name' => 'required|string|max:255',
            'test_price' => 'required|numeric|min:0',
            'test_type' => 'required|string|max:255',
            'report_format' => 'required|string|in:Quantitative,Radiology,Cardiology',
            'template' => 'nullable|string',
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
            'category' => 'required|string|in:Pathology,Radiology',
            'sample_expiry_hours' => 'nullable|integer|min:1',
            'sample_vial' => 'nullable|string|max:255',
            'vials_required' => 'nullable|integer|min:1|max:10',
        ]);

        $test = Test::create([
            'test_id' => $request->test_id,
            'name' => $request->test_name,
            'price' => $request->test_price,
            'type' => $request->test_type,
            'report_format' => $request->report_format,
            'template' => $request->template,
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
            'category' => $request->category,
            'sample_expiry_hours' => $request->category === 'Pathology' ? $request->sample_expiry_hours : null,
            'sample_vial' => $request->category === 'Pathology' ? $request->sample_vial : null,
            'vials_required' => $request->category === 'Pathology' ? ($request->vials_required ?? 1) : 1,
        ]);

        if ($test->report_format === 'Radiology') {
            $particulars = ['Clinical Indication', 'Technique', 'Findings', 'Impression'];
            foreach ($particulars as $p) {
                TestParticular::create(['test_id' => $test->id, 'name' => $p]);
            }
        } elseif ($test->report_format === 'Cardiology') {
            $particulars = ['Heart Rate', 'Rhythm', 'Intervals', 'ST-T Segment Analysis'];
            foreach ($particulars as $p) {
                TestParticular::create(['test_id' => $test->id, 'name' => $p]);
            }
        }

        $route = $request->category == 'Pathology' ? 'pathology.manage_test' : ($request->category == 'Radiology' ? 'radiology.manage_test' : 'laboratory.manage_test');
        return redirect()->route($route)->with('success', 'Test added successfully!');
    }

    /**
     * Update an existing test in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Test  $test
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Test $test)
    {
        $request->validate([
            'test_id' => 'required|string|max:255|unique:tests,test_id,' . $test->id,
            'test_name' => 'required|string|max:255',
            'test_price' => 'required|numeric|min:0',
            'test_type' => 'required|string|max:255',
            'report_format' => 'required|string|in:Quantitative,Radiology,Cardiology',
            'template' => 'nullable|string',
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
            'category' => 'required|string|in:Pathology,Radiology',
            'sample_expiry_hours' => 'nullable|integer|min:1',
            'sample_vial' => 'nullable|string|max:255',
            'vials_required' => 'nullable|integer|min:1|max:10',
        ]);

        $test->update([
            'test_id' => $request->test_id,
            'name' => $request->test_name,
            'price' => $request->test_price,
            'type' => $request->test_type,
            'report_format' => $request->report_format,
            'template' => $request->template,
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
            'category' => $request->category,
            'sample_expiry_hours' => $request->category === 'Pathology' ? $request->sample_expiry_hours : null,
            'sample_vial' => $request->category === 'Pathology' ? $request->sample_vial : null,
            'vials_required' => $request->category === 'Pathology' ? ($request->vials_required ?? 1) : 1,
        ]);

        $route = $request->category == 'Pathology' ? 'pathology.manage_test' : ($request->category == 'Radiology' ? 'radiology.manage_test' : 'laboratory.manage_test');
        return redirect()->route($route)->with('success', 'Test updated successfully!');
    }

    /**
     * Remove a test from the database.
     *
     * @param  \App\Models\Test  $test
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Test $test)
    {
        $category = $test->category;
        $test->delete();
        $route = $category == 'Pathology' ? 'pathology.manage_test' : ($category == 'Radiology' ? 'radiology.manage_test' : 'laboratory.manage_test');
        return redirect()->route($route)->with('success', 'Test deleted successfully!');
    }
}