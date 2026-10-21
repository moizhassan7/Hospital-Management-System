@extends('layouts.app')

@section('title', 'OPD patient summary')

@section('content')
    <x-ui.page-header title="OPD patient summary" subtitle="Outpatient visits grouped by department.">
        <x-slot:actions>
            <a href="{{ route('index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Reports
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <x-ui.icon name="printer" />Print report
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Period" class="no-print" flush>
        <form action="{{ route('reports.opd_summary') }}" method="GET">
            <div class="panel-body">
                <div class="flex flex-wrap items-end gap-3">
                    <x-ui.field label="Start date" for="start_date">
                        <input type="date" id="start_date" name="start_date" class="input w-auto" value="{{ $startDate }}">
                    </x-ui.field>
                    <x-ui.field label="End date" for="end_date">
                        <input type="date" id="end_date" name="end_date" class="input w-auto" value="{{ $endDate }}">
                    </x-ui.field>
                    <button type="submit" class="btn btn-primary">Generate report</button>
                </div>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel class="mt-5" flush>
        <div class="border-b border-line px-5 py-4 text-center">
            <h2 class="font-display text-lg font-bold">{{ get_setting('hospital_name', 'Hospital Management System') }}</h2>
            <p class="text-sm font-semibold">OPD Patient Summary</p>
            <p class="mt-1 text-[12.5px] text-fg-muted">Date from {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="num w-12">Sr. #</th>
                        <th>Department</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @forelse($opdAppointments as $appointment)
                        <tr>
                            <td class="num muted">{{ $counter++ }}</td>
                            <td class="cell-strong">{{ $appointment->department_name }}</td>
                            <td class="num">{{ $appointment->total }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="3" class="p-0">
                                <x-ui.empty-state icon="clipboard" title="No OPD visits in this period" description="Choose a different date range." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="font-semibold">Total</td>
                        <td class="num font-semibold">{{ $totalAppointments }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-ui.panel>
@endsection
