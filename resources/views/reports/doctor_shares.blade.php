@extends('layouts.app')

@section('title', 'Doctor config shares')

@section('content')
    <x-ui.page-header title="Doctor config shares" subtitle="Defined doctor percentages on shareable procedures.">
        <x-slot:actions>
            <a href="{{ route('index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Reports
            </a>
            <a href="{{ route('reports.hospital_shares') }}" class="btn btn-secondary">
                <x-ui.icon name="building" />Hospital shares
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Filter" flush>
        <form action="{{ route('reports.doctor_shares') }}" method="GET">
            <div class="panel-body">
                <div class="flex flex-wrap items-end gap-3">
                    <x-ui.field label="Procedure" for="procedure_id" class="min-w-[16rem] flex-1">
                        <select name="procedure_id" id="procedure_id" class="select">
                            <option value="">All procedures</option>
                            @foreach($procedures as $proc)
                                <option value="{{ $proc->id }}" @selected($procedureId == $proc->id)>{{ $proc->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('reports.doctor_shares') }}" class="btn btn-ghost">Clear</a>
                </div>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel title="Doctor share table" class="mt-5" flush>
        <x-slot:actions>
            <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg">{{ $shares->count() }}</span> rows</span>
        </x-slot:actions>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Doctor code</th>
                        <th>Procedure</th>
                        <th class="num">Doctor share</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shares as $share)
                        <tr>
                            <td class="cell-strong">{{ $share->doctor_name }}</td>
                            <td><span class="id">{{ $share->doctor_code }}</span></td>
                            <td>{{ $share->procedure_name }}</td>
                            <td class="num"><x-ui.badge tone="success">{{ $share->share_percentage }}%</x-ui.badge></td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="4" class="p-0">
                                <x-ui.empty-state icon="percent" title="No share records found" description="Choose another procedure or define shares on the procedure." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
@endsection
