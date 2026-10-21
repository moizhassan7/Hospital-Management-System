@extends('layouts.app')

@section('title', 'Hospital revenue share')

@section('content')
    <x-ui.page-header title="Hospital revenue share" subtitle="Hospital cut of shared procedures across modules.">
        <x-slot:actions>
            <a href="{{ route('index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Reports
            </a>
            <a href="{{ route('reports.revenue_doctors') }}" class="btn btn-secondary">
                <x-ui.icon name="stethoscope" />Doctor revenue
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Report criteria" flush>
        <form method="GET" action="{{ route('reports.revenue_hospital') }}">
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="Procedure (optional)" for="procedure_id" class="md:col-span-6">
                        <select id="procedure_id" name="procedure_id" class="select">
                            <option value="">All shared procedures</option>
                            @foreach($proceduresList as $proc)
                                <option value="{{ $proc->id }}" @selected($procedureId == $proc->id)>{{ $proc->name }} ({{ $proc->code }})</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="From date" for="from_date" class="md:col-span-3">
                        <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}" class="input">
                    </x-ui.field>
                    <x-ui.field label="To date" for="to_date" class="md:col-span-3">
                        <input type="date" id="to_date" name="to_date" value="{{ $toDate }}" class="input">
                    </x-ui.field>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary">Generate report</button>
            </div>
        </form>
    </x-ui.panel>

    <section aria-label="Hospital totals" class="metric-strip mt-5">
        <x-ui.metric label="Total billed (procedures)" icon="receipt" :prefix="get_setting('currency_symbol', 'Rs')" :value="number_format($grandTotalAmount, 2)" />
        <x-ui.metric label="Hospital revenue share" icon="landmark" :prefix="get_setting('currency_symbol', 'Rs')" :value="number_format($grandTotalHospitalRevenue, 2)" />
    </section>

    <x-ui.panel title="Share lines" class="mt-5" flush>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Module</th>
                        <th>Patient</th>
                        <th>Procedure</th>
                        <th>Doctor</th>
                        <th class="num">Total amount</th>
                        <th class="num">Hospital share</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                        <tr>
                            <td class="nowrap">{{ $row['date'] }}</td>
                            <td><x-ui.badge tone="info">{{ $row['module'] }}</x-ui.badge></td>
                            <td class="cell-strong">{{ $row['patient_name'] }}</td>
                            <td>{{ $row['procedure_name'] }}</td>
                            <td>{{ $row['doctor_name'] }}</td>
                            <td class="num"><x-ui.money :amount="$row['total_amount']" /></td>
                            <td class="num"><x-ui.money :amount="$row['hospital_share_amount']" /></td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="7" class="p-0">
                                <x-ui.empty-state icon="landmark" title="No procedure revenue in this period" description="Choose a different date range or procedure." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
@endsection
