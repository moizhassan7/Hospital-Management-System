@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $canVouchers = Auth::user()->hasPermission('Create Vouchers') || Auth::user()->hasPermission('Manage Doctor Payments') || Auth::user()->hasPermission('Create Cash Handover') || Auth::user()->hasPermission('Receive Cash Handover') || Auth::user()->isSuperAdmin();
        $canHospitalFinance = Auth::user()->hasPermission('View Finance') || Auth::user()->hasPermission('Create Vouchers') || Auth::user()->hasPermission('Manage Doctor Payments') || Auth::user()->hasPermission('Receive Cash Handover') || Auth::user()->isSuperAdmin();
    @endphp

    <x-ui.page-header title="Reports" subtitle="Operational summaries, discharge history and share configuration." />

    <section aria-label="At a glance" class="metric-strip">
        <x-ui.metric label="Patients registered" icon="users" :value="number_format($totalPatients)" />
        <x-ui.metric label="Indoor records" icon="bed" :value="number_format($totalIndoorPatients)" />
        <x-ui.metric label="OPD appointments" icon="clipboard" :value="number_format($totalOpdAppointments)" />
        <x-ui.metric label="Discharges" icon="log-out" :value="number_format($totalDischargedPatients)" />
        <x-ui.metric label="Doctors" icon="stethoscope" :value="number_format($totalDoctors)" />
        <x-ui.metric label="Indoor billed" icon="banknote" :prefix="$currency" :value="number_format($totalRevenue, 2)" hint="From indoor patient totals" />
    </section>

    <div class="mt-5 stack">
        <section>
            <h2 class="section-heading">Patient activity</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('reports.indoor_patient_summary')" icon="bed" title="Indoor patient summary" description="Admitted patients by ward, general and welfare." primary />
                <x-ui.module-link :href="route('reports.opd_summary')" icon="clipboard" title="OPD patient summary" description="Outpatient visits grouped by department." />
                <x-ui.module-link :href="route('reports.indoor_discharge_history')" icon="file-text" title="Discharge history" description="Discharged indoor patients with diagnosis and bill." />
                <x-ui.module-link :href="route('reports.indoor_discharge_payment')" icon="wallet" title="IPD discharge payment" description="Doctor and hospital shares on indoor discharges." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Shares & revenue</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('reports.doctor_shares')" icon="percent" title="Doctor config shares" description="Defined percentages for doctors on procedures." />
                <x-ui.module-link :href="route('reports.hospital_shares')" icon="building" title="Hospital config shares" description="Defined percentages for the hospital on procedures." />
                <x-ui.module-link :href="route('reports.revenue_doctors')" icon="stethoscope" title="Doctor revenue share" description="Actual earnings by doctor over a date range." />
                <x-ui.module-link :href="route('reports.revenue_hospital')" icon="landmark" title="Hospital revenue share" description="Actual hospital cut of shared procedures." />
            </div>
        </section>

        @if($canVouchers || $canHospitalFinance)
            <section>
                <h2 class="section-heading">Accounting</h2>
                <div class="module-grid">
                    @if($canVouchers)
                        <x-ui.module-link :href="route('finance.vouchers.index')" icon="receipt" title="Voucher daybook" description="CR, CP, BR, BP and Contra Vouchers in one register." />
                    @endif
                    @if($canHospitalFinance)
                        <x-ui.module-link :href="route('finance.ledger.index')" icon="book-open" title="Account ledger" description="Debit and Credit journal, filterable by account and party." />
                        <x-ui.module-link :href="route('finance.reports.trial_balance')" icon="scale" title="Trial balance" description="Balances of all postable accounts from the unified journal." />
                    @endif
                </div>
            </section>
        @endif
    </div>
@endsection
