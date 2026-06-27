<?php

namespace App\Http\Controllers;

use App\Models\TestHead;
use Illuminate\Http\Request;

class TestHeadController extends Controller
{
    public function index()
    {
        $category = 'Pathology';
        $testHeads = TestHead::where('category', 'Pathology')->get();

        return view('laboratory.manage_test_head', compact('testHeads', 'category'));
    }

    public function edit(TestHead $testHead)
    {
        $category = 'Pathology';
        $testHeads = TestHead::where('category', 'Pathology')->get();

        return view('laboratory.manage_test_head', compact('testHeads', 'testHead', 'category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'test_head_name' => 'required|string|max:255',
        ]);

        TestHead::create([
            'name' => $request->test_head_name,
            'category' => 'Pathology',
        ]);

        return redirect()->route('pathology.test_head')->with('success', 'Test Head added successfully!');
    }

    public function update(Request $request, TestHead $testHead)
    {
        $request->validate([
            'test_head_name' => 'required|string|max:255',
        ]);

        $testHead->update([
            'name' => $request->test_head_name,
            'category' => 'Pathology',
        ]);

        return redirect()->route('pathology.test_head')->with('success', 'Test Head updated successfully!');
    }

    public function destroy(TestHead $testHead)
    {
        $testHead->delete();

        return redirect()->route('pathology.test_head')->with('success', 'Test Head deleted successfully!');
    }
}
