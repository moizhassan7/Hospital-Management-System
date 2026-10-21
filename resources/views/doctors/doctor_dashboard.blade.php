@extends('layouts.doctor')

@section('title', 'Doctor dashboard')

@section('content')
    <x-ui.page-header :title="'Welcome, ' . $doctor->name" :subtitle="$doctor->code . ($doctor->department?->name ? ' · ' . $doctor->department->name : '')" />

    <section aria-label="Today" class="metric-strip">
        <x-ui.metric label="Today's appointments" icon="calendar" :value="number_format($todayAppointments)" hint="OPD visits scheduled for today" />
        <x-ui.metric label="Patients seen" icon="users" :value="number_format($totalPatients)" hint="Distinct patients on OPD records" />
        <x-ui.metric label="Pending lab orders" icon="flask" :value="number_format($pendingLabResults)" hint="Referrals that are not yet completed" />
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
        <x-ui.panel title="Today's OPD list" flush>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Token</th>
                            <th class="num">Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($todaysList as $row)
                            <tr>
                                <td class="nowrap">{{ $row->appointment_time ?: '—' }}</td>
                                <td>
                                    <span class="cell-strong">{{ $row->patient_name }}</span>
                                    <span class="cell-sub">{{ $row->mr_number }}</span>
                                </td>
                                <td><span class="id">{{ $row->token_number ?: '—' }}</span></td>
                                <td class="num"><x-ui.money :amount="$row->total_amount" /></td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="4" class="p-0">
                                    <x-ui.empty-state icon="calendar" title="No appointments today" description="New OPD bookings for your code will appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Tasks" icon="clipboard">
            <x-ui.module-link :href="route('doctors.write_prescription')" icon="pill" title="Write prescription" description="Create and print a prescription for a visit." primary />
        </x-ui.panel>
    </div>
@endsection
