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
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : null);
        $query = TestHead::query();
        if ($category) {
            $query->where('category', $category);
        }
        $testHeads = $query->get();
        return view('laboratory.manage_test_head', compact('testHeads', 'category'));
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
        $category = request()->is('pathology*') ? 'Pathology' : (request()->is('radiology*') ? 'Radiology' : $testHead->category);
        $query = TestHead::query();
        if ($category) {
            $query->where('category', $category);
        }
        $testHeads = $query->get();
        return view('laboratory.manage_test_head', compact('testHeads', 'testHead', 'category'));
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
            'test_head_name' => 'required|string|max:255',
            'category' => 'required|string|in:Pathology,Radiology',
        ]);

        TestHead::create([
            'name' => $request->test_head_name,
            'category' => $request->category,
        ]);

        $route = $request->category == 'Pathology' ? 'pathology.test_head' : ($request->category == 'Radiology' ? 'radiology.test_head' : 'test_head');
        return redirect()->route($route)->with('success', 'Test Head added successfully!');
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
            'test_head_name' => 'required|string|max:255',
            'category' => 'required|string|in:Pathology,Radiology',
        ]);

        $testHead->update([
            'name' => $request->test_head_name,
            'category' => $request->category,
        ]);

        $route = $request->category == 'Pathology' ? 'pathology.test_head' : ($request->category == 'Radiology' ? 'radiology.test_head' : 'test_head');
        return redirect()->route($route)->with('success', 'Test Head updated successfully!');
    }

    /**
     * Remove a test head from the database.
     *
     * @param  \App\Models\TestHead  $testHead
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TestHead $testHead)
    {
        $category = $testHead->category;
        $testHead->delete();
        $route = $category == 'Pathology' ? 'pathology.test_head' : ($category == 'Radiology' ? 'radiology.test_head' : 'test_head');
        return redirect()->route($route)->with('success', 'Test Head deleted successfully!');
    }
}