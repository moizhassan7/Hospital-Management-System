@extends('layouts.app')

@section('title', ($category ?? 'Laboratory') . ' catalog')

@section('content')
    @php
        $label = $category ?? 'Laboratory';
        $hubRoute = $category == 'Pathology' ? route('pathology.index') : ($category == 'Radiology' ? route('radiology.index') : route('laboratory.index'));
        $testCount = $testHeads->sum(fn ($head) => $head->tests->count());
    @endphp

    <x-ui.page-header :title="$label . ' catalog'" :subtitle="'Every ' . strtolower($label) . ' test with its parameters, units and reference ranges.'">
        <x-slot:actions>
            <a href="{{ $hubRoute }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />{{ $label }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($testHeads->isEmpty())
        <x-ui.panel flush>
            <x-ui.empty-state icon="book-open" title="No categories found" description="Start by adding test heads and tests to build the catalogue." />
        </x-ui.panel>
    @else
        <div class="stack">
            <p class="text-[12.5px] text-fg-muted">
                <span class="font-semibold text-fg">{{ $testHeads->count() }}</span> {{ \Illuminate\Support\Str::plural('head', $testHeads->count()) }}
                · <span class="font-semibold text-fg">{{ $testCount }}</span> {{ \Illuminate\Support\Str::plural('test', $testCount) }}
            </p>

            @foreach ($testHeads as $head)
                <x-ui.panel :title="$head->name" icon="layers" flush>
                    <x-slot:actions>
                        <span class="text-[12.5px] text-fg-muted">{{ $head->tests->count() }} {{ \Illuminate\Support\Str::plural('test', $head->tests->count()) }}</span>
                    </x-slot:actions>

                    @forelse ($head->tests as $test)
                        <div class="border-b border-line last:border-b-0">
                            <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-3.5 bg-subtle">
                                <div class="min-w-0">
                                    <span class="id">{{ $test->test_id }}</span>
                                    <h3 class="mt-0.5 text-[15px] font-semibold text-fg">{{ $test->name }}</h3>
                                </div>
                                <div class="text-right">
                                    <span class="cell-sub">Report time</span>
                                    <span class="block font-semibold tabular-nums text-fg">{{ $test->report_time }} hours</span>
                                </div>
                            </div>
                            <div class="table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Particular</th>
                                            <th>Unit</th>
                                            <th>Normal range</th>
                                            <th>Reference note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($test->testParticulars as $particular)
                                            <tr>
                                                <td class="cell-strong">{{ $particular->name }}</td>
                                                <td>
                                                    @if ($particular->unit)
                                                        <x-ui.badge tone="neutral">{{ $particular->unit }}</x-ui.badge>
                                                    @else
                                                        <span class="muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="tabular-nums">
                                                    @if ($particular->normal_range_min !== null || $particular->normal_range_max !== null)
                                                        {{ $particular->normal_range_min ?? '0' }}
                                                        <span class="muted">—</span>
                                                        {{ $particular->normal_range_max ?? '∞' }}
                                                    @else
                                                        <span class="muted">Qualitative / text</span>
                                                    @endif
                                                </td>
                                                <td class="muted">{{ $particular->reference_text ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="muted">No particulars defined for this test.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="p-6">
                            <x-ui.empty-state icon="flask" title="No tests under this heading" description="Add tests from Manage tests and they will appear here." />
                        </div>
                    @endforelse
                </x-ui.panel>
            @endforeach
        </div>
    @endif
@endsection
