@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = Auth::user();
        $currency = get_setting('currency_symbol', 'Rs');
        $totalBeds = $availableBeds + $indoorPatientsCount;

        $canCreateVoucher = $user->hasPermission('Create Vouchers') || $user->hasPermission('Manage Doctor Payments') || $user->hasPermission('Create Cash Handover');
        $canPatients = $user->hasPermission('View Patients');
        $canLab = $user->hasPermission('View Laboratory');

        $tasks = array_values(array_filter([
            $canPatients ? ['route' => 'patients.register', 'icon' => 'user-plus', 'title' => 'Register patient', 'description' => 'Create a medical record for a new patient.', 'primary' => true] : null,
            $canPatients ? ['route' => 'patients.outdoor_register', 'icon' => 'clipboard', 'title' => 'OPD visit', 'description' => 'Issue a token and consultation slip.'] : null,
            $canPatients ? ['route' => 'patients.appointments.index', 'icon' => 'calendar', 'title' => 'Appointment desk', 'description' => 'Collect fees and confirm reserved tokens.'] : null,
            $canPatients ? ['route' => 'patients.indoor_register', 'icon' => 'bed', 'title' => 'Indoor admission', 'description' => 'Admit a patient to a ward or private room.'] : null,
            $canPatients ? ['route' => 'patients.discharge', 'icon' => 'log-out', 'title' => 'Discharge', 'description' => 'Settle charges and discharge an admitted patient.'] : null,
            $user->hasPermission('View Emergency') ? ['route' => 'emergency.patients.create', 'icon' => 'siren', 'title' => 'Emergency registration', 'description' => 'Register a patient arriving in emergency.'] : null,
            $user->hasPermission('View Procedures') ? ['route' => 'day-care.create', 'icon' => 'sun', 'title' => 'Day care procedure', 'description' => 'Record a same-day procedure and its charges.'] : null,
            $canLab ? ['route' => 'pathology.patient_registration', 'icon' => 'flask', 'title' => 'Lab registration', 'description' => 'Book pathology tests for a patient.'] : null,
            $canLab ? ['route' => 'pathology.result_entry.search', 'icon' => 'test-tube', 'title' => 'Enter lab results', 'description' => 'Find a patient and record test results.'] : null,
            $canCreateVoucher ? ['route' => 'finance.vouchers.create', 'icon' => 'receipt', 'title' => 'Accounting voucher', 'description' => 'Record a receipt, payment or contra entry.'] : null,
        ]));
    @endphp

    <x-ui.page-header title="Dashboard" :subtitle="now()->format('l, j F Y') . ' · Today at ' . get_setting('hospital_name', 'the hospital')" />

    <section aria-label="Key figures" class="metric-strip">
        <x-ui.metric label="Registered today" icon="user-plus" :value="number_format($patientsRegisteredToday)" hint="New patient records" />
        <x-ui.metric label="Emergency cases" icon="siren" :value="number_format($emergencyPatientsCount)" hint="Registered to date" />
        <x-ui.metric label="Indoor patients" icon="bed" :value="number_format($indoorPatientsCount)" hint="Currently admitted" />
        <x-ui.metric label="Bed occupancy" icon="activity" :value="$bedOccupancyPercentage . '%'" :meter="$bedOccupancyPercentage" :hint="number_format($availableBeds) . ' beds available'" />
        <x-ui.metric label="Revenue today" icon="banknote" :prefix="$currency" :value="number_format($revenueToday, 2)" hint="Indoor and emergency billing" />
        <x-ui.metric label="Active doctors" icon="stethoscope" :value="number_format($activeDoctorsCount)" hint="Active doctor profiles" />
        <x-ui.metric label="Upcoming appointments" icon="calendar" :value="number_format($upcomingAppointments)" hint="OPD from today onward" />
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
        <x-ui.panel title="Start a task" description="Common front-desk and clinical workflows you have access to.">
            @if (count($tasks))
                <div class="grid gap-3 md:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($tasks as $task)
                        <x-ui.module-link :href="route($task['route'])" :icon="$task['icon']" :title="$task['title']" :description="$task['description']" :primary="$task['primary'] ?? false" />
                    @endforeach
                </div>
            @else
                <x-ui.empty-state icon="lock" title="No tasks available" description="Your role does not include patient, laboratory or finance workflows. Ask an administrator if you need access." />
            @endif
        </x-ui.panel>

        <x-ui.panel title="Bed status" description="Across all wards and rooms." icon="bed">
            <div class="flex items-end justify-between gap-3">
                <p class="font-display text-[34px] leading-none font-extrabold tracking-tight tabular-nums">{{ $bedOccupancyPercentage }}<span class="text-lg text-fg-muted">%</span></p>
                <x-ui.badge tone="brand">{{ number_format($availableBeds) }} free</x-ui.badge>
            </div>
            <div class="meter h-2.5" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $bedOccupancyPercentage }}" aria-label="Bed occupancy">
                <span style="width: {{ max(0, min(100, $bedOccupancyPercentage)) }}%"></span>
            </div>
            <dl class="mt-5 grid grid-cols-3 divide-x divide-line-soft rounded-lg border border-line text-center">
                <div class="px-2 py-3">
                    <dt class="text-xs text-fg-muted">Occupied</dt>
                    <dd class="mt-1 font-display text-lg font-bold tabular-nums">{{ number_format($indoorPatientsCount) }}</dd>
                </div>
                <div class="px-2 py-3">
                    <dt class="text-xs text-fg-muted">Available</dt>
                    <dd class="mt-1 font-display text-lg font-bold tabular-nums">{{ number_format($availableBeds) }}</dd>
                </div>
                <div class="px-2 py-3">
                    <dt class="text-xs text-fg-muted">Total beds</dt>
                    <dd class="mt-1 font-display text-lg font-bold tabular-nums">{{ number_format($totalBeds) }}</dd>
                </div>
            </dl>
        </x-ui.panel>
    </div>

    @if (count($patientsByDepartment) || count($doctorsByDepartment))
        <div class="mt-5 grid gap-5 xl:grid-cols-2">
            <x-ui.panel title="Patients by department" :description="count($patientsByDepartment) ? 'Currently admitted, grouped by the consultant department.' : 'No admitted patients with a consultant assigned.'" icon="users">
                @if (count($patientsByDepartment))
                    @php $maxPatients = max(array_column($patientsByDepartment, 'patients')) ?: 1; @endphp
                    <ul class="stack-sm">
                        @foreach ($patientsByDepartment as $row)
                            <li>
                                <div class="mb-1 flex items-center justify-between gap-3 text-[13px]">
                                    <span class="font-semibold">{{ $row['department'] }}</span>
                                    <span class="tabular-nums text-fg-muted">{{ number_format($row['patients']) }}</span>
                                </div>
                                <div class="meter h-1.5" role="meter" aria-valuemin="0" aria-valuemax="{{ $maxPatients }}" aria-valuenow="{{ $row['patients'] }}" aria-label="{{ $row['department'] }}">
                                    <span style="width: {{ max(4, min(100, ($row['patients'] / $maxPatients) * 100)) }}%"></span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state icon="users" title="No department activity" description="Admit a patient with a consultant, or book an OPD visit today." />
                @endif
            </x-ui.panel>

            <x-ui.panel title="Doctors by department" description="Active doctor profiles, excluding procedure and service entries." icon="stethoscope">
                @if (count($doctorsByDepartment))
                    @php $maxDoctors = max(array_column($doctorsByDepartment, 'doctors')) ?: 1; @endphp
                    <ul class="stack-sm">
                        @foreach ($doctorsByDepartment as $row)
                            <li>
                                <div class="mb-1 flex items-center justify-between gap-3 text-[13px]">
                                    <span class="font-semibold">{{ $row['department'] }}</span>
                                    <span class="tabular-nums text-fg-muted">{{ number_format($row['doctors']) }}</span>
                                </div>
                                <div class="meter h-1.5" role="meter" aria-valuemin="0" aria-valuemax="{{ $maxDoctors }}" aria-valuenow="{{ $row['doctors'] }}" aria-label="{{ $row['department'] }}">
                                    <span style="width: {{ max(4, min(100, ($row['doctors'] / $maxDoctors) * 100)) }}%"></span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state icon="stethoscope" title="No doctors on file" description="Add doctors and assign them to a department." />
                @endif
            </x-ui.panel>
        </div>
    @endif
@endsection
