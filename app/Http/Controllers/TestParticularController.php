<?php

namespace App\Http\Controllers;

use App\Models\TestHead;
use App\Models\Test;
use App\Models\TestParticular;
use Illuminate\Http\Request;

class TestParticularController extends Controller
{
    /**
     * Display a listing of the test particulars.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $category = 'Pathology';
        $testHeads = TestHead::where('category', 'Pathology')->get();
        $testParticulars = TestParticular::with('test.testHead')
            ->whereHas('test', fn ($q) => $q->where('category', 'Pathology'))
            ->get();

        return view('laboratory.add_test_particulars', compact('testHeads', 'testParticulars', 'category'));
    }

    public function edit(TestParticular $testParticular)
    {
        $testParticular->load('test.testHead');

        if ($testParticular->test?->category !== 'Pathology') {
            abort(404);
        }

        $category = 'Pathology';
        $testHeads = TestHead::where('category', 'Pathology')->get();
        $testParticulars = TestParticular::with('test.testHead')
            ->whereHas('test', fn ($q) => $q->where('category', 'Pathology'))
            ->get();
        $particular = $testParticular;

        return view('laboratory.add_test_particulars', compact('testHeads', 'testParticulars', 'category', 'particular'));
    }

    /**
     * Store a new test particular in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'test_id' => 'required|exists:tests,id',
            'particular_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'normal_range_min' => 'nullable|numeric',
            'normal_range_max' => 'nullable|numeric|gte:normal_range_min',
            'reference_text' => 'nullable|string',
        ]);

        TestParticular::create([
            'test_id' => $request->test_id,
            'name' => $request->particular_name,
            'unit' => $request->unit,
            'normal_range_min' => $request->normal_range_min,
            'normal_range_max' => $request->normal_range_max,
            'reference_text' => $request->reference_text,
        ]);

        return redirect()->route('pathology.add_test_particulars')->with('success', 'Test particular added successfully!');
    }

    public function update(Request $request, TestParticular $testParticular)
    {
        $request->validate([
            'test_id' => 'required|exists:tests,id',
            'particular_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'normal_range_min' => 'nullable|numeric',
            'normal_range_max' => 'nullable|numeric|gte:normal_range_min',
            'reference_text' => 'nullable|string',
        ]);

        $testParticular->update([
            'test_id' => $request->test_id,
            'name' => $request->particular_name,
            'unit' => $request->unit,
            'normal_range_min' => $request->normal_range_min,
            'normal_range_max' => $request->normal_range_max,
            'reference_text' => $request->reference_text,
        ]);

        return redirect()->route('pathology.add_test_particulars')->with('success', 'Test particular updated successfully!');
    }

    public function destroy(TestParticular $testParticular)
    {
        $testParticular->delete();

        return redirect()->route('pathology.add_test_particulars')->with('success', 'Test particular deleted successfully!');
    }

    /**
     * Get tests by a specific Test Head ID.
     * This is an API endpoint for the AJAX request.
     *
     * @param  int  $testHeadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTestsByHead($testHeadId)
    {
        $tests = Test::where('test_head_id', $testHeadId)
            ->where('category', 'Pathology')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($tests);
    }
     public function showDetails()
    {
        $testParticulars = TestParticular::with('test.testHead')->get();
        return view('laboratory.test_particular_details', compact('testParticulars'));
    }
}