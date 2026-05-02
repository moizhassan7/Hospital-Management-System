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
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : null);
        $headQuery = TestHead::query();
        $particularQuery = TestParticular::with('test.testHead');

        if ($category) {
            $headQuery->where('category', $category);
            $particularQuery->whereHas('test', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $testHeads = $headQuery->get();
        $testParticulars = $particularQuery->get();

        return view('laboratory.add_test_particulars', compact('testHeads', 'testParticulars', 'category'));
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

        $test = Test::find($request->test_id);
        $route = $test->category == 'Pathology' ? 'pathology.add_test_particulars' : ($test->category == 'Radiology' ? 'radiology.add_test_particulars' : 'laboratory.add_test_particulars');
        return redirect()->route($route)->with('success', 'Test particular added successfully!');
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
        $tests = Test::where('test_head_id', $testHeadId)->get(['id', 'name']);

        return response()->json($tests);
    }
     public function showDetails()
    {
        $testParticulars = TestParticular::with('test.testHead')->get();
        return view('laboratory.test_particular_details', compact('testParticulars'));
    }
}