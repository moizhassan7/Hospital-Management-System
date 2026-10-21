@extends('layouts.app')

@section('title', $patient->name)

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $money = fn ($value) => number_format((float) $value, 2);
        $date = fn ($value, $format = 'd M Y') => $value ? \Illuminate\Support\Carbon::parse($value)->format($format) : '—';
        $tabs = [
            'opd' => ['label' => 'OPD visits', 'icon' => 'stethoscope'],
            'admissions' => ['label' => 'Admissions', 'icon' => 'bed'],
            'emergency' => ['label' => 'Emergency', 'icon' => 'siren'],
            'day_care' => ['label' => 'Day care', 'icon' => 'sun'],
            'laboratory' => ['label' => 'Laboratory', 'icon' => 'flask'],
        ];
        $firstWithRecords = collect($tabs)->keys()->first(fn ($key) => $history[$key]['count'] > 0) ?? 'opd';
        $consultantNames = function ($value) {
            if (is_string($value)) {
                $value = json_decode($value, true);
            }
            return collect(is_array($value) ? $value : [])->pluck('name')->filter()->join(', ');
        };
    @endphp

    <x-ui.page-header :title="$patient->name" :subtitle="collect([
        filled($patient->age) ? $patient->age . ' yrs' : null,
        $patient->gender,
        $patient->mobile_number,
        'Registered ' . $date($patient->registration_date),
    ])->filter()->join(' · ')">
        <x-slot:meta>
            <span class="id">MR {{ $patient->mr_number }}</span>
            <x-ui.badge :tone="$patient->is_welfare ? 'info' : 'neutral'">{{ $patient->is_welfare ? 'Welfare' : 'Normal' }}</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('patients.all') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All patients
            </a>
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-primary">
                <x-ui.icon name="pencil" />Edit details
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[340px_minmax(0,1fr)]">
        <div class="stack">
            <x-ui.panel title="Details" icon="user">
                <dl class="kv">
                    <dt>Mobile</dt>
                    <dd class="tabular-nums">{{ $patient->mobile_number ?: '—' }}</dd>
                    <dt>Date of birth</dt>
                    <dd>{{ $date($patient->date_of_birth) }}</dd>
                    <dt>Age</dt>
                    <dd>{{ filled($patient->age) ? $patient->age . ' years' : '—' }}</dd>
                    <dt>Gender</dt>
                    <dd>{{ $patient->gender ?: '—' }}</dd>
                    <dt>Marital status</dt>
                    <dd>{{ $patient->marital_status ?: '—' }}</dd>
                    <dt>Weight</dt>
                    <dd>{{ filled($patient->weight) ? rtrim(rtrim(number_format($patient->weight, 1), '0'), '.') . ' kg' : '—' }}</dd>
                    <dt>CNIC</dt>
                    <dd class="tabular-nums">{{ $patient->cnic ?: '—' }}</dd>
                    <dt>Email</dt>
                    <dd class="break-all">{{ $patient->email ?: '—' }}</dd>
                    <dt>Address</dt>
                    <dd>{{ $patient->address ?: '—' }}</dd>
                </dl>
            </x-ui.panel>

            <x-ui.panel title="Guardian" icon="users">
                @if ($patient->guardian_name || $patient->relation_type || $patient->guardian_cnic)
                    <dl class="kv">
                        <dt>Name</dt>
                        <dd>{{ $patient->guardian_name ?: '—' }}</dd>
                        <dt>Relation</dt>
                        <dd>{{ $patient->relation_type ?: '—' }}</dd>
                        <dt>CNIC</dt>
                        <dd class="tabular-nums">{{ $patient->guardian_cnic ?: '—' }}</dd>
                    </dl>
                @else
                    <p class="text-[13px] text-fg-muted">No guardian recorded. <a href="{{ route('patients.edit', $patient) }}" class="font-medium text-brand-700 hover:underline">Add guardian</a></p>
                @endif
            </x-ui.panel>
        </div>

        <x-ui.panel title="Visit history" icon="clock" flush>
            <div class="tabs px-3" role="tablist" aria-label="Visit history" data-tabs>
                @foreach ($tabs as $key => $tab)
                    <button type="button" class="tab" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}" aria-selected="{{ $key === $firstWithRecords ? 'true' : 'false' }}">
                        <x-ui.icon :name="$tab['icon']" />{{ $tab['label'] }}
                        <span class="badge badge-neutral">{{ $history[$key]['count'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- OPD visits --}}
            <div id="panel-opd" role="tabpanel" aria-labelledby="tab-opd" @if ($firstWithRecords !== 'opd') hidden @endif>
                @if ($history['opd']['count'])
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Appointment</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th class="num">Amount ({{ $currency }})</th>
                                    <th class="actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history['opd']['records'] as $visit)
                                    <tr>
                                        <td class="nowrap">
                                            {{ $date($visit->appointment_date) }}
                                            @if ($visit->appointment_time)
                                                <span class="cell-sub">{{ $date($visit->appointment_time, 'h:i A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="id">{{ $visit->appointment_number }}</span>
                                            @if ($visit->token_number)
                                                <span class="cell-sub">Token {{ $visit->token_number }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $visit->doctor_name ?: '—' }}</td>
                                        <td>
                                            @php $status = strtolower((string) $visit->status); @endphp
                                            <x-ui.badge :tone="\App\Support\OpdStates::appointmentTone($status)">{{ \App\Support\OpdStates::appointmentLabel($status) }}</x-ui.badge>
                                        </td>
                                        <td class="num">{{ $money($visit->total_amount) }}</td>
                                        <td class="actions">
                                            <a href="{{ route('patients.appointments.show', $visit) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="clipboard" />Open</a>
                                            <a href="{{ route('patients.outdoor_receipt', $visit->appointment_number) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="receipt" />Receipt</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state icon="stethoscope" title="No OPD visits" description="Outdoor appointments for this MR number appear here." />
                @endif
            </div>

            {{-- Admissions --}}
            <div id="panel-admissions" role="tabpanel" aria-labelledby="tab-admissions" @if ($firstWithRecords !== 'admissions') hidden @endif>
                @if ($history['admissions']['count'])
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Admitted</th>
                                    <th>File / slip</th>
                                    <th>Ward or room</th>
                                    <th>Consultant</th>
                                    <th>Status</th>
                                    <th class="num">Amount ({{ $currency }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history['admissions']['records'] as $admission)
                                    @php $place = $admission->admission_type === 'ward' ? $admission->ward : $admission->room; @endphp
                                    <tr>
                                        <td class="nowrap">{{ $date($admission->registration_date) }}</td>
                                        <td>
                                            <span class="id">{{ $admission->file_no }}</span>
                                            <span class="cell-sub">Slip {{ $admission->slip_no }}</span>
                                        </td>
                                        <td>
                                            {{ $place->name ?? '—' }}
                                            <span class="cell-sub">{{ ucfirst((string) $admission->admission_type) }}@if ($admission->bed_no) · Bed {{ $admission->bed_no }}@endif</span>
                                        </td>
                                        <td>{{ $admission->consultant->name ?? '—' }}</td>
                                        <td>
                                            @if ($admission->is_discharged)
                                                <x-ui.badge tone="neutral">Discharged</x-ui.badge>
                                                <span class="cell-sub">{{ $date($admission->discharge_date) }}@if ($admission->discharge_status) · {{ $admission->discharge_status }}@endif</span>
                                            @else
                                                <x-ui.badge tone="brand" dot>Admitted</x-ui.badge>
                                            @endif
                                        </td>
                                        <td class="num">{{ $money($admission->total_amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state icon="bed" title="No admissions" description="Indoor admissions for this patient appear here." />
                @endif
            </div>

            {{-- Emergency --}}
            <div id="panel-emergency" role="tabpanel" aria-labelledby="tab-emergency" @if ($firstWithRecords !== 'emergency') hidden @endif>
                @if ($history['emergency']['count'])
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Consultants</th>
                                    <th class="num">Hours</th>
                                    <th class="num">Total ({{ $currency }})</th>
                                    <th class="num">Receivable</th>
                                    <th class="actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history['emergency']['records'] as $visit)
                                    <tr>
                                        <td class="nowrap">
                                            {{ $date($visit->created_at) }}
                                            <span class="cell-sub">{{ $date($visit->created_at, 'h:i A') }}</span>
                                        </td>
                                        <td>{{ $consultantNames($visit->consultants) ?: '—' }}</td>
                                        <td class="num">{{ $visit->emergency_hours }}</td>
                                        <td class="num">{{ $money($visit->total_fee) }}</td>
                                        <td @class(['num', 'font-semibold text-warning-700' => (float) $visit->amount_receivable > 0])>{{ $money($visit->amount_receivable) }}</td>
                                        <td class="actions">
                                            <a href="{{ route('emergency.patient.receipt', $visit) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="receipt" />Receipt</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state icon="siren" title="No emergency visits" description="Emergency registrations for this MR number appear here." />
                @endif
            </div>

            {{-- Day care --}}
            <div id="panel-day_care" role="tabpanel" aria-labelledby="tab-day_care" @if ($firstWithRecords !== 'day_care') hidden @endif>
                @if ($history['day_care']['count'])
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Procedure</th>
                                    <th class="num">Hours</th>
                                    <th class="num">Total ({{ $currency }})</th>
                                    <th class="num">Paid</th>
                                    <th class="num">Receivable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history['day_care']['records'] as $visit)
                                    <tr>
                                        <td class="nowrap">{{ $date($visit->created_at) }}</td>
                                        <td class="max-w-[280px]"><span class="line-clamp-2">{{ $visit->procedure_details ?: '—' }}</span></td>
                                        <td class="num">{{ $visit->hours_of_stay }}</td>
                                        <td class="num">{{ $money($visit->total_bill) }}</td>
                                        <td class="num">{{ $money($visit->amount_paid) }}</td>
                                        <td @class(['num', 'font-semibold text-warning-700' => (float) $visit->amount_receivable > 0])>{{ $money($visit->amount_receivable) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state icon="sun" title="No day care procedures" description="Day care procedures for this patient appear here." />
                @endif
            </div>

            {{-- Laboratory --}}
            <div id="panel-laboratory" role="tabpanel" aria-labelledby="tab-laboratory" @if ($firstWithRecords !== 'laboratory') hidden @endif>
                @if ($history['laboratory']['count'])
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Tests</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th class="num">Total ({{ $currency }})</th>
                                    <th class="num">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history['laboratory']['records'] as $visit)
                                    @php $tests = collect(is_array($visit->selected_tests) ? $visit->selected_tests : [])->pluck('name')->filter(); @endphp
                                    <tr>
                                        <td class="nowrap">
                                            {{ $date($visit->created_at) }}
                                            @if ($visit->file_no)
                                                <span class="cell-sub">File {{ $visit->file_no }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $tests->isNotEmpty() ? $tests->join(', ') : '—' }}</td>
                                        <td>{{ $visit->priority ?: '—' }}</td>
                                        <td>
                                            @php $status = strtolower((string) $visit->status); @endphp
                                            <x-ui.badge :tone="$status === 'completed' ? 'success' : ($status ? 'warning' : 'neutral')">{{ $visit->status ?: '—' }}</x-ui.badge>
                                        </td>
                                        <td class="num">{{ $money($visit->grand_total) }}</td>
                                        <td @class(['num', 'font-semibold text-warning-700' => (float) $visit->due_amount > 0])>{{ $money($visit->due_amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state icon="flask" title="No laboratory visits" description="Lab registrations for this MR number appear here." />
                @endif
            </div>

            @php $truncated = collect($tabs)->keys()->filter(fn ($key) => $history[$key]['count'] > $limit); @endphp
            @if ($truncated->isNotEmpty())
                <p class="border-t border-line-soft px-5 py-3 text-[12.5px] text-fg-muted">Each tab shows the latest {{ $limit }} records.</p>
            @endif
        </x-ui.panel>
    </div>
@endsection
