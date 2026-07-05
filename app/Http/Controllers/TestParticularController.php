<?php

namespace App\Http\Controllers;

use App\Models\TestHead;
use App\Models\Test;
use App\Models\TestParticular;
use App\Support\CaseInsensitiveSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestParticularController extends Controller
{
    /**
     * Display a listing of the test particulars.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $category = 'Pathology';
        $testHeads = TestHead::where('category', 'Pathology')->get();
        $search = trim((string) $request->query('q', ''));
        $testParticulars = $this->buildParticularsQuery($search)
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('laboratory.partials.test_particulars_list', compact('testParticulars', 'search'));
        }

        return view('laboratory.add_test_particulars', compact('testHeads', 'testParticulars', 'category', 'search'));
    }

    public function edit(Request $request, TestParticular $testParticular)
    {
        $testParticular->load('test.testHead');

        if ($testParticular->test?->category !== 'Pathology') {
            abort(404);
        }

        $category = 'Pathology';
        $testHeads = TestHead::where('category', 'Pathology')->get();
        $search = trim((string) $request->query('q', ''));
        $testParticulars = $this->buildParticularsQuery($search)
            ->paginate(15)
            ->withQueryString();
        $particular = $testParticular;

        if ($request->ajax()) {
            return view('laboratory.partials.test_particulars_list', compact('testParticulars', 'search'));
        }

        return view('laboratory.add_test_particulars', compact('testHeads', 'testParticulars', 'category', 'particular', 'search'));
    }

    /**
     * Build a query for Pathology test particulars with an optional multi-term search.
     *
     * Each whitespace-separated term must match (AND); within a term any searchable
     * field may match (OR): particular name, unit, reference text, normal ranges,
     * report order, test name, or test head name.
     *
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildParticularsQuery(string $search)
    {
        $query = TestParticular::with('test.testHead')
            ->whereHas('test', fn ($q) => $q->where('category', 'Pathology'));

        if ($search !== '') {
            $terms = preg_split('/\s+/', $search) ?: [];

            foreach ($terms as $term) {
                if ($term === '') {
                    continue;
                }

                $pattern = CaseInsensitiveSearch::pattern($term);

                $query->where(function ($q) use ($pattern, $term) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(unit) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(reference_text) LIKE ?', [$pattern])
                        ->orWhere('normal_range_min', 'like', '%' . $term . '%')
                        ->orWhere('normal_range_max', 'like', '%' . $term . '%')
                        ->orWhere('sort_order', 'like', '%' . $term . '%')
                        ->orWhereHas('test', fn ($t) => $t->whereRaw('LOWER(name) LIKE ?', [$pattern]))
                        ->orWhereHas('test.testHead', fn ($th) => $th->whereRaw('LOWER(name) LIKE ?', [$pattern]));
                });
            }
        }

        return $query->orderBy('sort_order')->orderBy('id');
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
            'sort_order' => 'nullable|integer|min:0',
        ]);

        TestParticular::create([
            'test_id' => $request->test_id,
            'name' => $request->particular_name,
            'unit' => $request->unit,
            'normal_range_min' => $request->normal_range_min,
            'normal_range_max' => $request->normal_range_max,
            'reference_text' => $request->reference_text,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('pathology.add_test_particulars')->with('success', 'Test particular added successfully!');
    }

    /**
     * Persist multiple test particulars for a single test in one action (cart save).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'test_id' => 'required|exists:tests,id',
            'particulars' => 'required|array|min:1',
            'particulars.*.particular_name' => 'required|string|max:255',
            'particulars.*.unit' => 'nullable|string|max:255',
            'particulars.*.normal_range_min' => 'nullable|numeric',
            'particulars.*.normal_range_max' => 'nullable|numeric|gte:particulars.*.normal_range_min',
            'particulars.*.reference_text' => 'nullable|string',
            'particulars.*.sort_order' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['particulars'] as $particular) {
                TestParticular::create([
                    'test_id' => $validated['test_id'],
                    'name' => $particular['particular_name'],
                    'unit' => $particular['unit'] ?? null,
                    'normal_range_min' => $particular['normal_range_min'] ?? null,
                    'normal_range_max' => $particular['normal_range_max'] ?? null,
                    'reference_text' => $particular['reference_text'] ?? null,
                    'sort_order' => $particular['sort_order'] ?? 0,
                ]);
            }
        });

        $count = count($validated['particulars']);

        return redirect()->route('pathology.add_test_particulars')
            ->with('success', $count . ' test ' . ($count === 1 ? 'particular' : 'particulars') . ' added successfully!');
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
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $testParticular->update([
            'test_id' => $request->test_id,
            'name' => $request->particular_name,
            'unit' => $request->unit,
            'normal_range_min' => $request->normal_range_min,
            'normal_range_max' => $request->normal_range_max,
            'reference_text' => $request->reference_text,
            'sort_order' => $request->sort_order ?? 0,
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