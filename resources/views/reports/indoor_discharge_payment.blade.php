@extends('layouts.app')

@section('title', 'IPD discharge payment')

@section('content')
    <x-ui.page-header title="Indoor discharge patient payment" subtitle="Doctor and hospital shares on indoor discharges.">
        <x-slot:actions>
            <a href="{{ route('index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Reports
            </a>
            <a href="{{ route('reports.indoor_discharge_payment.download', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary">
                <x-ui.icon name="download" />Download PDF
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <x-ui.icon name="printer" />Print report
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Period" class="no-print" flush>
        <form action="{{ route('reports.indoor_discharge_payment') }}" method="GET">
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
            <p class="text-sm font-semibold">Indoor Discharge Patient Payment</p>
            <p class="mt-1 text-[12.5px] text-fg-muted">From {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="num w-12">Sr. No</th>
                        <th>Adm. date</th>
                        <th>Patient (MR no)</th>
                        <th>Doctor</th>
                        <th>Dis. date</th>
                        <th class="num">Dr. share</th>
                        <th class="num">Hop. share</th>
                        <th class="num">Total bill</th>
                        <th class="num">Advance</th>
                        <th class="num">Amount adj.</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @forelse($payments as $record)
                        @php
                            $admission = $record->admission_date
                                ?? optional($record->indoorPatient)->registration_date
                                ?? optional($record->patient)->created_at;
                        @endphp
                        <tr>
                            <td class="num muted">{{ $counter++ }}</td>
                            <td class="nowrap">{{ $admission ? \Carbon\Carbon::parse($admission)->format('d-M-Y') : 'N/A' }}</td>
                            <td>
                                <span class="cell-strong">{{ $record->patient->name ?? 'N/A' }}</span>
                                <span class="cell-sub">{{ $record->patient->mr_number ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $record->certifyingDoctor->name ?? 'N/A' }}</td>
                            <td class="nowrap">{{ \Carbon\Carbon::parse($record->discharge_date)->format('d-M-Y') }}</td>
                            <td class="num"><x-ui.money :amount="$record->dr_share" /></td>
                            <td class="num"><x-ui.money :amount="$record->hop_share" /></td>
                            <td class="num"><x-ui.money :amount="$record->total_bill" /></td>
                            <td class="num"><x-ui.money :amount="$record->advance_fee" /></td>
                            <td class="num"><x-ui.money :amount="$record->amount_adj" /></td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="10" class="p-0">
                                <x-ui.empty-state icon="wallet" title="No discharge payments in this period" description="Choose a different date range." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="font-semibold">Grand total</td>
                        <td class="num font-semibold"><x-ui.money :amount="$payments->sum('dr_share')" /></td>
                        <td class="num font-semibold"><x-ui.money :amount="$payments->sum('hop_share')" /></td>
                        <td class="num font-semibold"><x-ui.money :amount="$payments->sum('total_bill')" /></td>
                        <td class="num font-semibold"><x-ui.money :amount="$payments->sum('advance_fee')" /></td>
                        <td class="num font-semibold"><x-ui.money :amount="$payments->sum('amount_adj')" /></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-ui.panel>
@endsection
