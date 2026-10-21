@extends('layouts.app')

@section('title', 'Test particular details')

@section('content')
    <x-ui.page-header title="Test particular details" subtitle="Every parameter defined on the laboratory catalogue, with units and reference ranges.">
        <x-slot:actions>
            <a href="{{ route('laboratory.add_test_particulars') }}" class="btn btn-primary">
                <x-ui.icon name="plus" />Add particular
            </a>
            <a href="{{ route('laboratory.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Laboratory
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Comprehensive list of test particulars" flush>
        <x-slot:actions>
            <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#particular-details-table">{{ count($testParticulars) }}</span> total</span>
        </x-slot:actions>
        <div class="table-toolbar">
            <x-ui.search-input name="" class="toolbar-search" placeholder="Filter particulars" data-table-filter="#particular-details-table" />
        </div>
        <div class="table-wrap">
            <table id="particular-details-table" class="table">
                <thead>
                    <tr>
                        <th class="num w-12">#</th>
                        <th>Particular ID</th>
                        <th>Particular</th>
                        <th>Test</th>
                        <th>Test head</th>
                        <th>Unit</th>
                        <th>Normal range</th>
                        <th>Reference notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testParticulars as $index => $particular)
                        <tr>
                            <td class="num muted">{{ $index + 1 }}</td>
                            <td><span class="id">{{ $particular->id }}</span></td>
                            <td class="cell-strong">{{ $particular->name }}</td>
                            <td>{{ $particular->test->name ?? '—' }}</td>
                            <td>{{ $particular->test->testHead->name ?? '—' }}</td>
                            <td>{{ $particular->unit ?? '—' }}</td>
                            <td class="tabular-nums">
                                {{ $particular->normal_range_min ?? '—' }}
                                <span class="muted">—</span>
                                {{ $particular->normal_range_max ?? '—' }}
                            </td>
                            <td class="muted max-w-xs truncate" title="{{ $particular->reference_text }}">
                                {{ $particular->reference_text ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="8" class="p-0">
                                <x-ui.empty-state icon="list" title="No test particulars found" description="Define parameters from Add test particulars." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
@endsection
