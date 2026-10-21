@extends('layouts.app')

@section('title', 'Procedures')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $filter = request('filter');
        $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    @endphp

    <x-ui.page-header title="Procedures" subtitle="Procedure catalogue with fees and doctor shares for general and welfare patients.">
        <x-slot:actions>
            <a href="{{ route('procedures.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" />Add procedure
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel flush>
        <div class="table-toolbar">
            <nav class="segmented" aria-label="Filter by type">
                @foreach (['' => 'All', 'Major' => 'Major', 'Minor' => 'Minor'] as $value => $label)
                    <a href="{{ route('procedures.index', $value ? ['filter' => $value] : []) }}" @class(['segmented-option', 'is-active' => ($filter ?? '') === $value]) @if (($filter ?? '') === $value) aria-current="page" @endif>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>
            <span class="toolbar-spacer"></span>
            <x-ui.search-input name="" class="toolbar-search" placeholder="Filter this page" data-table-filter="#procedures-table" />
        </div>

        <div class="table-wrap">
            <table id="procedures-table" class="table">
                <thead>
                    <tr>
                        <th>Procedure</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th>Performed for</th>
                        <th class="num">General fee</th>
                        <th class="num">Welfare fee</th>
                        <th class="actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($procedures as $procedure)
                        <tr>
                            <td>
                                <span class="cell-strong">{{ $procedure->name }}</span>
                                @if ($procedure->room_location)
                                    <span class="cell-sub">{{ $procedure->room_location }}</span>
                                @endif
                            </td>
                            <td><x-ui.badge :tone="$procedure->type === 'Major' ? 'brand' : 'neutral'">{{ $procedure->type }}</x-ui.badge></td>
                            <td>
                                {{ $procedure->department->name ?? 'N/A' }}
                                @if ($procedure->speciality)
                                    <span class="cell-sub">{{ $procedure->speciality->name }}</span>
                                @endif
                            </td>
                            <td>{{ $procedure->performed_for }}</td>
                            <td class="num">
                                {{ $money($procedure->general_normal_fee) }}
                                <span class="cell-sub">Emergency {{ $money($procedure->general_emergency_fee) }}</span>
                            </td>
                            <td class="num">
                                {{ $money($procedure->welfare_normal_fee) }}
                                <span class="cell-sub">Emergency {{ $money($procedure->welfare_emergency_fee) }}</span>
                            </td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('procedures.edit', $procedure) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                    <form action="{{ route('procedures.destroy', $procedure) }}" method="POST" class="inline"
                                        data-confirm="This permanently deletes the {{ $procedure->name }} procedure."
                                        data-confirm-title="Delete this procedure?" data-confirm-label="Delete procedure">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="7" class="p-0">
                                <x-ui.empty-state icon="scissors" :title="$filter ? 'No ' . strtolower($filter) . ' procedures' : 'No procedures yet'" description="Procedures you add appear here with their fees.">
                                    <x-slot:actions>
                                        <a href="{{ route('procedures.create') }}" class="btn btn-primary btn-sm"><x-ui.icon name="plus" />Add procedure</a>
                                    </x-slot:actions>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($procedures->hasPages())
            <div class="table-footer">{{ $procedures->appends(request()->only('filter'))->links() }}</div>
        @endif
    </x-ui.panel>
@endsection
