<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\TestHead;
use App\Models\TestParticular;

class LaboratoryController extends Controller
{
    /**
     * Display a comprehensive catalog of all tests and their particulars.
     *
     * @return \Illuminate\View\View
     */
    public function showTestCatalog(Request $request)
    {
        $category = 'Pathology';

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'test_head_id' => $request->query('test_head_id'),
            'priority' => $request->query('priority'),
            'type' => $request->query('type'),
            'particulars' => (string) $request->query('particulars', ''),
        ];

        $allTestHeads = TestHead::where('category', $category)->orderBy('name')->get();

        $stats = [
            'heads' => $allTestHeads->count(),
            'tests' => Test::where('category', $category)->count(),
            'particulars' => TestParticular::whereHas('test', fn ($q) => $q->where('category', $category))->count(),
        ];

        $filterTypes = Test::where('category', $category)->distinct()->orderBy('type')->pluck('type');
        $filterPriorities = Test::where('category', $category)->distinct()->orderBy('priority')->pluck('priority');

        $testsQuery = Test::query()
            ->where('tests.category', $category)
            ->join('test_heads', 'tests.test_head_id', '=', 'test_heads.id')
            ->select('tests.*')
            ->with(['testHead', 'testParticulars' => fn ($pq) => $pq->orderBy('sort_order')->orderBy('id')])
            ->when(filled($filters['priority']), fn ($query) => $query->where('tests.priority', $filters['priority']))
            ->when(filled($filters['type']), fn ($query) => $query->where('tests.type', $filters['type']))
            ->when($filters['particulars'] === 'with', fn ($query) => $query->has('testParticulars'))
            ->when($filters['particulars'] === 'without', fn ($query) => $query->doesntHave('testParticulars'))
            ->when($filters['test_head_id'], fn ($query) => $query->where('tests.test_head_id', $filters['test_head_id']))
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('tests.name', 'like', $term)
                        ->orWhereHas('testParticulars', fn ($pq) => $pq
                            ->where('name', 'like', $term)
                            ->orWhere('reference_text', 'like', $term)
                            ->orWhere('unit', 'like', $term));
                });
            })
            ->orderBy('test_heads.name')
            ->orderBy('tests.name');

        $tests = $testsQuery->paginate(15)->withQueryString();

        $testHeads = $tests->getCollection()
            ->groupBy('test_head_id')
            ->map(function ($headTests) {
                $head = $headTests->first()->testHead;
                if ($head) {
                    $head->setRelation('tests', $headTests->values());
                }

                return $head;
            })
            ->filter()
            ->values();

        $filteredTestsCount = $tests->total();
        $hasActiveFilters = collect($filters)->filter(fn ($value, $key) => $key === 'particulars' ? $value !== '' : filled($value))->isNotEmpty();

        return view('laboratory.test_catalog', compact(
            'testHeads',
            'tests',
            'category',
            'allTestHeads',
            'filters',
            'stats',
            'filterTypes',
            'filterPriorities',
            'filteredTestsCount',
            'hasActiveFilters'
        ));
    }
}
