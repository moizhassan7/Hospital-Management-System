<?php

namespace App\Http\Controllers;

use App\Models\TestHead;
use Illuminate\Http\Request;

class TestHeadController extends Controller
{
    /**
     * Display a listing of the test heads.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $testHeads = TestHead::all();
        return view('laboratory.manage_test_head', compact('testHeads'));
    }
    
    /**
     * Show the form for editing the specified test head.
     * This method passes the testHead object to the same 'manage_test_head' view.
     *
     * @param  \App\Models\TestHead  $testHead
     * @return \Illuminate\View\View
     */
    public function edit(TestHead $testHead)
    {
        $testHeads = TestHead::all();
        return view('laboratory.manage_test_head', compact('testHeads', 'testHead'));
    }

    /**
     * Store a new test head in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'test_head_name' => 'required|string|max:255|unique:test_heads,name',
        ]);

        TestHead::create([
            'name' => $request->test_head_name,
        ]);

        return redirect()->route('test_head')->with('success', 'Test Head added successfully!');
    }

    /**
     * Update an existing test head in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TestHead  $testHead
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TestHead $testHead)
    {
        $request->validate([
            'test_head_name' => 'required|string|max:255|unique:test_heads,name,' . $testHead->id,
        ]);

        $testHead->update([
            'name' => $request->test_head_name,
        ]);

        return redirect()->route('test_head')->with('success', 'Test Head updated successfully!');
    }

    /**
     * Remove a test head from the database.
     *
     * @param  \App\Models\TestHead  $testHead
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TestHead $testHead)
    {
        $testHead->delete();
        return redirect()->route('test_head')->with('success', 'Test Head deleted successfully!');
    }
}