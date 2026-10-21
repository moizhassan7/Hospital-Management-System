@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
@php
    $currency = get_setting('currency_symbol', 'Rs');
    $appointmentTone = fn ($status) => \App\Support\OpdStates::appointmentTone((string) $status);
    $paymentTone = fn ($status) => \App\Support\OpdStates::paymentTone((string) $status);
    $tokenTone = fn ($status) => \App\Support\OpdStates::tokenTone($status);
@endphp

    <x-ui.page-header title="Appointment desk" subtitle="Find booked visits, collect unpaid fees and confirm reserved tokens.">
        <x-slot:actions>
            <a href="{{ route('patients.book_appointment') }}" class="btn btn-primary">
                <x-ui.icon name="calendar-plus" />Book appointment
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Filters" flush>
        <form method="GET" action="{{ route('patients.appointments.index') }}">
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="Search" class="md:col-span-4">
                        <input name="q" value="{{ request('q') }}" placeholder="Patient, MR no. or appointment no." class="input">
                    </x-ui.field>
                    <x-ui.field label="Appointment date" class="md:col-span-2">
                        <input type="date" name="date" value="{{ request('date') }}" class="input">
                    </x-ui.field>
                    <x-ui.field label="Doctor" class="md:col-span-3">
                        <select name="doctor_code" class="select">
                            <option value="">All doctors</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->code }}" @selected(request('doctor_code') === $doctor->code)>{{ $doctor->code }} — {{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Appointment" class="md:col-span-3">
                        <select name="status" class="select">
                            <option value="">All statuses</option>
                            @foreach (['booked' => 'Booked', 'checked_in' => 'Checked in', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No-show'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Payment" class="md:col-span-3">
                        <select name="payment_status" class="select">
                            <option value="">All payments</option>
                            @foreach (['unpaid' => 'Unpaid', 'partially_paid' => 'Partially paid', 'paid' => 'Paid', 'refunded' => 'Refunded'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Token" class="md:col-span-3">
                        <select name="token_status" class="select">
                            <option value="">All tokens</option>
                            @foreach (['provisional' => 'Provisional', 'reserved' => 'Reserved', 'confirmed' => 'Confirmed', 'used' => 'Used', 'released' => 'Released'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('token_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <div class="md:col-span-6 flex items-end gap-2">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <a href="{{ route('patients.appointments.index', ['date' => '']) }}" class="btn btn-ghost">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel class="mt-5" flush>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient / MR no.</th>
                        <th>Doctor</th>
                        <th>Token</th>
                        <th>Appointment</th>
                        <th>Payment</th>
                        <th>Token status</th>
                        <th class="actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $row)
                        <tr>
                            <td class="nowrap">
                                {{ $row->appointment_date?->format('d M Y') }}
                                <span class="cell-sub">{{ $row->appointment_time ? \Carbon\Carbon::parse($row->appointment_time)->format('h:i A') : '—' }}</span>
                            </td>
                            <td>
                                <span class="cell-strong">{{ $row->patient_name }}</span>
                                <span class="cell-sub">{{ $row->mr_number }} · {{ $row->appointment_number }}</span>
                            </td>
                            <td>{{ $row->doctor_name }}</td>
                            <td><span class="id">{{ $row->displayToken() ? '#'.$row->displayToken() : '—' }}</span></td>
                            <td><x-ui.badge :tone="$appointmentTone($row->status)">{{ \App\Support\OpdStates::appointmentLabel((string) $row->status) }}</x-ui.badge></td>
                            <td><x-ui.badge :tone="$paymentTone($row->payment_status)">{{ \App\Support\OpdStates::paymentLabel((string) $row->payment_status) }}</x-ui.badge></td>
                            <td><x-ui.badge :tone="$tokenTone($row->token_status)">{{ \App\Support\OpdStates::tokenLabel($row->token_status) }}</x-ui.badge></td>
                            <td class="actions">
                                <a href="{{ route('patients.appointments.show', $row) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="8" class="p-0">
                                <x-ui.empty-state icon="calendar" title="No appointments" description="Book a visit or clear the filters." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($appointments->hasPages())
            <div class="panel-footer">{{ $appointments->links() }}</div>
        @endif
    </x-ui.panel>
@endsection
