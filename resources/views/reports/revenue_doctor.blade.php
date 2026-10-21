@extends('layouts.app')

@section('title', 'Doctor revenue share')

@section('content')
    <x-ui.page-header title="Doctor revenue share" subtitle="Actual doctor and hospital cuts for a selected doctor and period.">
        <x-slot:actions>
            <a href="{{ route('index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Reports
            </a>
            <a href="{{ route('reports.revenue_hospital') }}" class="btn btn-secondary">
                <x-ui.icon name="landmark" />Hospital revenue
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Report criteria" flush>
        <form method="GET" action="{{ route('reports.revenue_doctors') }}">
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="Doctor" for="doctor_id" required class="md:col-span-6">
                        <select id="doctor_id" name="doctor_id" class="select" required>
                            <option value="">Choose doctor</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" @selected($selectedDoctorId == $doc->id)>{{ $doc->name }} ({{ $doc->code }})</option>
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

    @if($selectedDoctorId)
        <section aria-label="Share totals" class="metric-strip mt-5">
            <x-ui.metric label="Doctor revenue share" icon="stethoscope" :prefix="get_setting('currency_symbol', 'Rs')" :value="number_format($totalDoctorRevenue, 2)" />
            <x-ui.metric label="Hospital revenue share" icon="building" :prefix="get_setting('currency_symbol', 'Rs')" :value="number_format($totalHospitalRevenue, 2)" />
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
                            <th class="num">Total amount</th>
                            <th class="num">Doctor share</th>
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
                                <td class="num"><x-ui.money :amount="$row['total_amount']" /></td>
                                <td class="num"><x-ui.money :amount="$row['doctor_share_amount']" /></td>
                                <td class="num"><x-ui.money :amount="$row['hospital_share_amount']" /></td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="7" class="p-0">
                                    <x-ui.empty-state icon="stethoscope" title="No revenue records for this doctor" description="No shared procedures were billed in the selected period." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    @else
        <x-ui.empty-state class="mt-5" icon="stethoscope" title="Choose a doctor to generate the report" description="Select a doctor and date range, then generate the revenue share." />
    @endif
@endsection
