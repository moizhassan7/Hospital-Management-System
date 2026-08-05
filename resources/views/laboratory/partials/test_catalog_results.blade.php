@php
    $currentSearch = $filters['q'] ?? '';
@endphp

<p class="text-sm text-gray-500" id="catalog-results-summary">
    {{ number_format($filteredTestsCount) }} {{ Str::plural('test', $filteredTestsCount) }} found
    @if($currentSearch !== '')
        for “{{ $currentSearch }}”
    @endif
    @if($tests->total() > 0)
        · showing {{ number_format($tests->firstItem()) }}–{{ number_format($tests->lastItem()) }}
    @endif
</p>

@if($hasActiveFilters)
    <div class="flex flex-wrap gap-2 mt-3" id="catalog-active-filters">
        @if($filters['q'] !== '')
            <span class="hms-badge hms-badge-purple">Search: {{ $filters['q'] }}</span>
        @endif
        @if($filters['test_head_id'])
            <span class="hms-badge hms-badge-blue">
                Head: {{ $allTestHeads->firstWhere('id', (int) $filters['test_head_id'])?->name ?? 'Selected' }}
            </span>
        @endif
        @if($filters['particulars'] === 'with')
            <span class="hms-badge hms-badge-teal">With parameters</span>
        @elseif($filters['particulars'] === 'without')
            <span class="hms-badge hms-badge-red">Without parameters</span>
        @endif
        <a href="{{ route('pathology.test_catalog') }}" class="text-xs text-indigo-600 hover:text-indigo-800 ml-1">Reset all</a>
    </div>
@endif

@if($tests->isEmpty())
    <div class="hms-empty mt-6" id="catalog-results-empty">
        <p class="hms-empty-title">
            @if($hasActiveFilters)
                No tests match your search
            @else
                No catalog data yet
            @endif
        </p>
        <p class="hms-empty-desc">
            @if($hasActiveFilters)
                Try a different search term or clear filters.
            @else
                Add test heads, tests, and particulars to build your catalog.
            @endif
        </p>
    </div>
@else
    <div class="catalog-test-list mt-4 space-y-2" id="catalog-test-list">
        @foreach($tests as $test)
            <details class="catalog-test-item group rounded-xl border border-gray-200 bg-white overflow-hidden">
                <summary class="catalog-test-summary flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between px-4 py-3 cursor-pointer list-none select-none [&::-webkit-details-marker]:hidden hover:bg-gray-50/80">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <h4 class="text-sm font-semibold text-gray-900">{{ $test->name }}</h4>
                            @if($test->test_code)
                                <span class="text-xs font-mono text-gray-400">{{ $test->test_code }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                            <span class="hms-badge hms-badge-blue">{{ $test->testHead->name ?? '—' }}</span>
                            @if($test->sample_vial)
                                <span class="hms-badge hms-badge-teal">{{ $test->sample_vial }}</span>
                            @endif
                            <span class="text-xs text-gray-500">{{ $test->testParticulars->count() }} {{ Str::plural('parameter', $test->testParticulars->count()) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        <span class="text-sm font-semibold text-blue-700">Rs {{ number_format($test->price) }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </summary>

                @if($test->testParticulars->isNotEmpty())
                    <div class="border-t border-gray-100">
                        <div class="hms-table-wrap">
                            <table class="hms-table text-sm">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Patient type</th>
                                        <th>Unit</th>
                                        <th>Normal range</th>
                                        <th>Critical range</th>
                                        <th>Range in words</th>
                                        <th>Interpretation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($test->testParticulars as $particular)
                                        <tr>
                                            <td class="font-medium text-gray-900">{{ $particular->name }}</td>
                                            <td>{{ $particular->patient_type ?: '—' }}</td>
                                            <td>{{ $particular->unit ?: '—' }}</td>
                                            <td>{{ $particular->formattedNumericRange() ?: '—' }}</td>
                                            <td>{{ $particular->formattedCriticalRange() ?: '—' }}</td>
                                            <td class="text-gray-600 max-w-xs">{{ $particular->referenceRangeText() ?: '—' }}</td>
                                            <td class="text-gray-600 max-w-xs">{{ $particular->interpretationLabel() ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-4 text-sm text-gray-500 italic border-t border-gray-100 bg-gray-50/40">
                        No parameters defined for this test.
                    </div>
                @endif
            </details>
        @endforeach
    </div>

    @if($tests->hasPages())
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between catalog-pagination">
            <p class="text-sm text-gray-500">
                Page {{ $tests->currentPage() }} of {{ $tests->lastPage() }}
            </p>
            <div class="test-catalog-pagination">
                {{ $tests->onEachSide(1)->links() }}
            </div>
        </div>
    @endif
@endif
