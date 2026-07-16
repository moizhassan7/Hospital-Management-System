<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestHead;
use App\Support\CaseInsensitiveSearch;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $category = 'Pathology';
        $search = trim((string) $request->query('q', ''));
        $tests = $this->buildTestsQuery($search)->paginate(15)->withQueryString();
        $testHeads = TestHead::where('category', 'Pathology')->get();

        if ($request->ajax()) {
            return view('laboratory.partials.existing_tests_list', compact('tests', 'search'));
        }

        return view('laboratory.manage_test', compact('tests', 'testHeads', 'category', 'search'));
    }

    public function edit(Request $request, Test $test)
    {
        $category = 'Pathology';
        $search = trim((string) $request->query('q', ''));
        $tests = $this->buildTestsQuery($search)->paginate(15)->withQueryString();
        $testHeads = TestHead::where('category', 'Pathology')->get();

        if ($request->ajax()) {
            return view('laboratory.partials.existing_tests_list', compact('tests', 'search'));
        }

        return view('laboratory.manage_test', compact('tests', 'testHeads', 'test', 'category', 'search'));
    }

    /**
     * Build a query for Pathology tests with an optional multi-term search.
     *
     * Each whitespace-separated term must match (AND); within a term any of the
     * displayed fields may match (OR): test name, type, priority, sample vial,
     * or the parent test head name.
     *
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildTestsQuery(string $search)
    {
        $query = Test::with('testHead')->pathology();

        if ($search !== '') {
            $terms = preg_split('/\s+/', $search) ?: [];

            foreach ($terms as $term) {
                if ($term === '') {
                    continue;
                }

                $pattern = CaseInsensitiveSearch::pattern($term);

                $query->where(function ($q) use ($pattern) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(type) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(priority) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(sample_vial) LIKE ?', [$pattern])
                        ->orWhereHas('testHead', fn ($th) => $th->whereRaw('LOWER(name) LIKE ?', [$pattern]));
                });
            }
        }

        return $query;
    }

    public function store(Request $request)
    {
        $request->validate([
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:255',
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'sample_expiry_hours' => 'nullable|integer|min:1',
            'sample_vial' => 'nullable|string|max:255',
            'vials_required' => 'nullable|integer|min:1|max:10',
            'vial_volume' => 'nullable|string|max:255',
            'reference_tables' => 'nullable|string',
        ]);

        Test::create([
            'test_id' => Test::generateNextTestId('Pathology'),
            'name' => $request->test_name,
            'price' => (float) $request->price,
            'type' => $request->test_type,
            'report_format' => 'Quantitative',
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
            'category' => 'Pathology',
            'sample_expiry_hours' => $request->sample_expiry_hours,
            'sample_vial' => $request->sample_vial,
            'vials_required' => $request->vials_required ?? 1,
            'vial_volume' => $request->vial_volume,
            'reference_tables' => $this->parseReferenceTables($request->input('reference_tables')),
        ]);

        Test::clearPathologyCache();

        return redirect()->route('pathology.manage_test')->with('success', 'Test added successfully!');
    }

    public function update(Request $request, Test $test)
    {
        $request->validate([
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:255',
            'test_head_id' => 'required|exists:test_heads,id',
            'priority' => 'required|string|max:255',
            'report_time' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'sample_expiry_hours' => 'nullable|integer|min:1',
            'sample_vial' => 'nullable|string|max:255',
            'vials_required' => 'nullable|integer|min:1|max:10',
            'vial_volume' => 'nullable|string|max:255',
            'reference_tables' => 'nullable|string',
        ]);

        $test->update([
            'name' => $request->test_name,
            'price' => (float) $request->price,
            'type' => $request->test_type,
            'report_format' => 'Quantitative',
            'test_head_id' => $request->test_head_id,
            'priority' => $request->priority,
            'report_time' => $request->report_time,
            'category' => 'Pathology',
            'sample_expiry_hours' => $request->sample_expiry_hours,
            'sample_vial' => $request->sample_vial,
            'vials_required' => $request->vials_required ?? 1,
            'vial_volume' => $request->vial_volume,
            'reference_tables' => $this->parseReferenceTables($request->input('reference_tables')),
        ]);

        Test::clearPathologyCache();

        return redirect()->route('pathology.manage_test')->with('success', 'Test updated successfully!');
    }

    public function destroy(Test $test)
    {
        $test->delete();

        Test::clearPathologyCache();

        return redirect()->route('pathology.manage_test')->with('success', 'Test deleted successfully!');
    }

    /**
     * Sanitize the JSON reference-tables payload from the Manage Test form into
     * a clean array of { title, columns[], rows[][] }. Empty tables/rows dropped.
     *
     * @return list<array{title: string, columns: list<string>, rows: list<list<string>>}>|null
     */
    private function parseReferenceTables(?string $json): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return null;
        }

        $tables = [];

        foreach ($decoded as $table) {
            if (! is_array($table)) {
                continue;
            }

            $columns = array_values(array_filter(
                array_map(fn ($c) => trim((string) $c), $table['columns'] ?? []),
                fn ($c) => $c !== ''
            ));

            if ($columns === []) {
                continue;
            }

            $columnCount = count($columns);
            $rows = [];

            foreach ($table['rows'] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $cells = array_map(fn ($cell) => trim((string) $cell), array_values($row));
                $cells = array_slice(array_pad($cells, $columnCount, ''), 0, $columnCount);

                if (implode('', $cells) === '') {
                    continue;
                }

                $rows[] = $cells;
            }

            if ($rows === []) {
                continue;
            }

            $tables[] = [
                'title' => trim((string) ($table['title'] ?? '')),
                'columns' => $columns,
                'rows' => $rows,
            ];
        }

        return $tables === [] ? null : $tables;
    }
}
