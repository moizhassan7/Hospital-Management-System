<?php

namespace App\Http\Controllers;

use App\Models\TestHead;
use Illuminate\Http\Request;

class TestHeadController extends Controller
{
    public function index(Request $request)
    {
        $category = 'Pathology';
        $search = trim((string) $request->query('q', ''));
        $testHeads = $this->buildTestHeadsQuery($search)->paginate(15)->withQueryString();

        return view('laboratory.manage_test_head', compact('testHeads', 'category', 'search'));
    }

    public function edit(Request $request, TestHead $testHead)
    {
        $category = 'Pathology';
        $search = trim((string) $request->query('q', ''));
        $testHeads = $this->buildTestHeadsQuery($search)->paginate(15)->withQueryString();

        return view('laboratory.manage_test_head', compact('testHeads', 'testHead', 'category', 'search'));
    }

    /**
     * Build a query for Pathology test heads with an optional name search.
     *
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildTestHeadsQuery(string $search)
    {
        $query = TestHead::where('category', 'Pathology');

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query;
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
