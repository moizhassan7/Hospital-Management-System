<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestHead;
use App\Models\TestParticular;
use App\Support\CaseInsensitiveSearch;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    /**
     * Display a comprehensive catalog of all tests and their particulars.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function showTestCatalog(Request $request)
    {
        $category = 'Pathology';

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'test_head_id' => $request->query('test_head_id'),
            'particulars' => (string) $request->query('particulars', ''),
        ];

        $allTestHeads = TestHead::where('category', $category)->orderBy('name')->get();

        $stats = [
            'heads' => $allTestHeads->count(),
            'tests' => Test::where('category', $category)->count(),
            'particulars' => TestParticular::whereHas('test', fn ($q) => $q->where('category', $category))->count(),
        ];

        $testsQuery = Test::query()
            ->where('tests.category', $category)
            ->join('test_heads', 'tests.test_head_id', '=', 'test_heads.id')
            ->select('tests.*')
            ->with(['testHead', 'testParticulars' => fn ($pq) => $pq->orderBy('sort_order')->orderBy('id')])
            ->when($filters['particulars'] === 'with', fn ($query) => $query->has('testParticulars'))
            ->when($filters['particulars'] === 'without', fn ($query) => $query->doesntHave('testParticulars'))
            ->when($filters['test_head_id'], fn ($query) => $query->where('tests.test_head_id', $filters['test_head_id']))
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $terms = preg_split('/\s+/', $filters['q']) ?: [];

                foreach ($terms as $term) {
                    if ($term === '') {
                        continue;
                    }

                    $pattern = CaseInsensitiveSearch::pattern($term);

                    $query->where(function ($sub) use ($pattern) {
                        $sub->whereRaw('LOWER(tests.name) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(tests.test_code) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(tests.test_id) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(tests.sample_vial) LIKE ?', [$pattern])
                            ->orWhereHas('testHead', fn ($hq) => $hq->whereRaw('LOWER(name) LIKE ?', [$pattern]))
                            ->orWhereHas('testParticulars', fn ($pq) => $pq->where(function ($p) use ($pattern) {
                                $p->whereRaw('LOWER(name) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(unit) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(reference_text) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(reference_range_text) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(patient_type) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(interpretation_name) LIKE ?', [$pattern]);
                            }));
                    });
                }
            })
            ->orderBy('test_heads.name')
            ->orderBy('tests.name');

        $tests = $testsQuery->paginate(20)->withQueryString();

        $filteredTestsCount = $tests->total();
        $hasActiveFilters = collect($filters)->filter(fn ($value, $key) => $key === 'particulars' ? $value !== '' : filled($value))->isNotEmpty();

        $viewData = compact(
            'tests',
            'category',
            'allTestHeads',
            'filters',
            'stats',
            'filteredTestsCount',
            'hasActiveFilters'
        );

        if ($request->ajax()) {
            return response()->view('laboratory.partials.test_catalog_results', $viewData);
        }

        return view('laboratory.test_catalog', $viewData);
    }
}
