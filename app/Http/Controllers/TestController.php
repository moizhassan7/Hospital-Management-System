<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestHead;
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
        $tests = Test::with('testHead')->get();
        $testHeads = TestHead::all();

        return view('laboratory.manage_test', compact('tests', 'testHeads'));
    }

    /**
     * Show the form for editing the specified test.
     *
     * @param  \App\Models\Test  $test
     * @return \Illuminate\View\View
     */
    public function edit(Test $test)
    {
        $tests = Test::with('testHead')->get();
        $testHeads = TestHead::all();

        return view('laboratory.manage_test', compact('tests', 'testHeads', 'test'));
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
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
        ]);

        Test::create([
            'test_id' => $request->test_id,
            'name' => $request->test_name,
            'price' => $request->test_price,
            'type' => $request->test_type,
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
        ]);

        return redirect()->route('laboratory.manage_test')->with('success', 'Test added successfully!');
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
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
        ]);

        $test->update([
            'test_id' => $request->test_id,
            'name' => $request->test_name,
            'price' => $request->test_price,
            'type' => $request->test_type,
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
        ]);

        return redirect()->route('laboratory.manage_test')->with('success', 'Test updated successfully!');
    }

    /**
     * Remove a test from the database.
     *
     * @param  \App\Models\Test  $test
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Test $test)
    {
        $test->delete();
        return redirect()->route('laboratory.manage_test')->with('success', 'Test deleted successfully!');
    }
}