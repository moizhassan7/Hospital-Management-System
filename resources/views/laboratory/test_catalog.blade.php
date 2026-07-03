@extends('layouts.app')

@section('page_title', 'Test Catalog')

@section('content')
    <div class="hms-page-toolbar">
        <div>
            <h2 class="hms-page-heading">Pathology Test Catalog</h2>
            <p class="text-sm text-gray-500 mt-1">Browse tests, sample settings, and result parameters</p>
        </div>
        <a href="{{ route('pathology.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="hms-stat-card">
            <p class="hms-stat-label">Test heads</p>
            <p class="hms-stat-value text-gray-900">{{ number_format($stats['heads']) }}</p>
        </div>
        <div class="hms-stat-card">
            <p class="hms-stat-label">Total tests</p>
            <p class="hms-stat-value text-blue-700">{{ number_format($stats['tests']) }}</p>
        </div>
        <div class="hms-stat-card">
            <p class="hms-stat-label">Showing now</p>
            <p class="hms-stat-value text-purple-700">{{ number_format($filteredTestsCount) }}</p>
            @if($hasActiveFilters)
                <p class="hms-stat-hint">Filtered results</p>
            @endif
        </div>
        <div class="hms-stat-card">
            <p class="hms-stat-label">Parameters</p>
            <p class="hms-stat-value text-teal-700">{{ number_format($stats['particulars']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="hms-filter-panel">
        <h3 class="hms-filter-title">Search &amp; filters</h3>
        <form action="{{ route('pathology.test_catalog') }}" method="GET" class="space-y-4">
            <div class="hms-search-bar">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="search"
                    name="q"
                    class="hms-input"
                    placeholder="Search test name, parameter, unit, or reference note…"
                    value="{{ $filters['q'] }}"
                    autocomplete="off"
                >
            </div>

            <div class="hms-form-grid-4">
                <div>
                    <label for="filter_test_head" class="hms-label">Test head</label>
                    <select id="filter_test_head" name="test_head_id" class="hms-select">
                        <option value="">All test heads</option>
                        @foreach($allTestHeads as $head)
                            <option value="{{ $head->id }}" {{ (string) $filters['test_head_id'] === (string) $head->id ? 'selected' : '' }}>
                                {{ $head->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_type" class="hms-label">Test type</label>
                    <select id="filter_type" name="type" class="hms-select">
                        <option value="">All types</option>
                        @foreach($filterTypes as $type)
                            <option value="{{ $type }}" {{ $filters['type'] === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_priority" class="hms-label">Priority</label>
                    <select id="filter_priority" name="priority" class="hms-select">
                        <option value="">All priorities</option>
                        @foreach($filterPriorities as $priority)
                            <option value="{{ $priority }}" {{ $filters['priority'] === $priority ? 'selected' : '' }}>{{ $priority }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_particulars" class="hms-label">Parameters</label>
                    <select id="filter_particulars" name="particulars" class="hms-select">
                        <option value="" {{ $filters['particulars'] === '' ? 'selected' : '' }}>All tests</option>
                        <option value="with" {{ $filters['particulars'] === 'with' ? 'selected' : '' }}>With parameters</option>
                        <option value="without" {{ $filters['particulars'] === 'without' ? 'selected' : '' }}>Without parameters</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="hms-btn hms-btn-primary">Apply filters</button>
                @if($hasActiveFilters)
                    <a href="{{ route('pathology.test_catalog') }}" class="hms-btn hms-btn-ghost">Clear all</a>
                @endif
            </div>
        </form>
    </div>

    @if($hasActiveFilters)
        <div class="flex flex-wrap gap-2 mb-5">
            @if($filters['q'] !== '')
                <span class="hms-badge hms-badge-purple">Search: {{ $filters['q'] }}</span>
            @endif
            @if($filters['test_head_id'])
                <span class="hms-badge hms-badge-blue">
                    Head: {{ $allTestHeads->firstWhere('id', (int) $filters['test_head_id'])?->name ?? 'Selected' }}
                </span>
            @endif
            @if($filters['type'])
                <span class="hms-badge hms-badge-gray">Type: {{ $filters['type'] }}</span>
            @endif
            @if($filters['priority'])
                <span class="hms-badge hms-badge-yellow">Priority: {{ $filters['priority'] }}</span>
            @endif
            @if($filters['particulars'] === 'with')
                <span class="hms-badge hms-badge-teal">With parameters</span>
            @elseif($filters['particulars'] === 'without')
                <span class="hms-badge hms-badge-red">Without parameters</span>
            @endif
        </div>
    @endif

    {{-- Catalog --}}
    @forelse($testHeads as $head)
        <details class="hms-panel mb-4 group" open>
            <summary class="hms-panel-header cursor-pointer list-none flex items-center justify-between gap-3 select-none [&::-webkit-details-marker]:hidden">
                <div class="min-w-0">
                    <h3 class="hms-panel-title">{{ $head->name }}</h3>
                    <p class="hms-panel-subtitle">{{ $head->tests->count() }} {{ Str::plural('test', $head->tests->count()) }} in this section</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="hms-badge hms-badge-purple">{{ $head->tests->count() }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </summary>

            <div class="hms-panel-body space-y-4 pt-0">
                @foreach($head->tests as $test)
                    <article class="rounded-xl border border-gray-200 overflow-hidden bg-white">
                        <div class="px-4 py-3 sm:px-5 sm:py-4 bg-gray-50 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h4 class="text-base font-semibold text-gray-900">{{ $test->name }}</h4>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="hms-badge hms-badge-gray">{{ $test->type }}</span>
                                    <span class="hms-badge hms-badge-yellow">{{ $test->priority }}</span>
                                    <span class="hms-badge hms-badge-blue">{{ $test->report_time }}h report</span>
                                    @if($test->sample_vial)
                                        <span class="hms-badge hms-badge-teal">{{ $test->sample_vial }}</span>
                                    @endif
                                    @if($test->vial_volume)
                                        <span class="hms-badge hms-badge-purple">{{ $test->vial_volume }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 shrink-0">
                                <span class="font-medium text-gray-700">{{ $test->testParticulars->count() }}</span>
                                {{ Str::plural('parameter', $test->testParticulars->count()) }}
                            </div>
                        </div>

                        @if($test->testParticulars->isNotEmpty())
                            <div class="hms-table-wrap">
                                <table class="hms-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Unit</th>
                                            <th>Normal range</th>
                                            <th>Reference note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($test->testParticulars as $particular)
                                            <tr>
                                                <td class="font-medium text-gray-900">{{ $particular->name }}</td>
                                                <td>{{ $particular->unit ?: '—' }}</td>
                                                <td>
                                                    @if($particular->normal_range_min !== null || $particular->normal_range_max !== null)
                                                        {{ $particular->normal_range_min ?? '0' }} — {{ $particular->normal_range_max ?? '∞' }}
                                                    @else
                                                        <span class="text-gray-400 italic">Text / qualitative</span>
                                                    @endif
                                                </td>
                                                <td class="text-gray-600">{{ $particular->reference_text ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-5 py-6 text-sm text-gray-500 italic bg-gray-50/40">
                                No parameters defined for this test yet.
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </details>
    @empty
        <div class="hms-panel hms-panel-padded">
            <div class="hms-empty">
                <p class="hms-empty-title">
                    @if($hasActiveFilters)
                        No tests match your filters
                    @else
                        No catalog data yet
                    @endif
                </p>
                <p class="hms-empty-desc">
                    @if($hasActiveFilters)
                        Try a different search term or clear filters to see all pathology tests.
                    @else
                        Add test heads, tests, and particulars to build your catalog.
                    @endif
                </p>
                @if($hasActiveFilters)
                    <a href="{{ route('pathology.test_catalog') }}" class="hms-btn hms-btn-primary mt-4">Clear filters</a>
                @endif
            </div>
        </div>
    @endforelse
@endsection
