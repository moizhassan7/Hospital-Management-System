@extends('layouts.app')

@section('title', 'Indoor discharge history')

@section('content')
    <x-ui.page-header title="Indoor discharge patient history" subtitle="Discharged indoor patients with diagnosis and bill.">
        <x-slot:actions>
            <a href="{{ route('index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Reports
            </a>
            <a href="{{ route('reports.indoor_discharge_history.download', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary">
                <x-ui.icon name="download" />Download PDF
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <x-ui.icon name="printer" />Print report
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Period" class="no-print" flush>
        <form action="{{ route('reports.indoor_discharge_history') }}" method="GET">
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
            <p class="text-sm font-semibold">Indoor Discharge Patient History</p>
            <p class="mt-1 text-[12.5px] text-fg-muted">From {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="num w-12">Sr. No</th>
                        <th>Adm. date</th>
                        <th>Doctor</th>
                        <th>Patient (MR no)</th>
                        <th>Dis. date</th>
                        <th class="num">Total bill</th>
                        <th class="num">Discount</th>
                        <th class="num">Paid</th>
                        <th>Discharge type & diagnosis</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @forelse($dischargedPatients as $record)
                        <tr>
                            <td class="num muted">{{ $counter++ }}</td>
                            <td class="nowrap">{{ \Carbon\Carbon::parse($record->patient->created_at)->format('d-M-Y') }}</td>
                            <td>{{ $record->certifyingDoctor->name ?? 'N/A' }}</td>
                            <td>
                                <span class="cell-strong">{{ $record->patient->name ?? 'N/A' }}</span>
                                <span class="cell-sub">{{ $record->patient->mr_number ?? 'N/A' }}</span>
                            </td>
                            <td class="nowrap">{{ \Carbon\Carbon::parse($record->discharge_date)->format('d-M-Y') }}</td>
                            <td class="num"><x-ui.money :amount="$record->total_bill" /></td>
                            <td class="num"><x-ui.money :amount="$record->discount" /></td>
                            <td class="num"><x-ui.money :amount="$record->amount_paid" /></td>
                            <td>
                                {{ $record->discharge_status ?? 'N/A' }}
                                <span class="cell-sub">{{ $record->diagnoses ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="9" class="p-0">
                                <x-ui.empty-state icon="file-text" title="No discharges in this period" description="Choose a different date range." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
@endsection
