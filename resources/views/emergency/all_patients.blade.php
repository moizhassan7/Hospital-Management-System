@extends('layouts.app')

@section('title', 'Emergency patients')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $patients = $emergencyPatients->sortByDesc('created_at')->values();
        $consultantsOf = function ($record) {
            $value = $record->consultants;
            // Older saves stored the list as a JSON string inside the JSON column.
            if (is_string($value)) {
                $value = json_decode($value, true);
            }
            return is_array($value) ? $value : [];
        };
        $todayCount = $patients->filter(fn ($p) => optional($p->created_at)->isToday())->count();
    @endphp

    <x-ui.page-header title="Emergency patients" subtitle="Everyone registered in emergency, newest first.">
        <x-slot:actions>
            <a href="{{ route('emergency.patients.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" />Register emergency patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <div class="metric-strip">
            <x-ui.metric label="Registered" icon="users" :value="number_format($patients->count())" :hint="$todayCount . ' today'" />
            <x-ui.metric label="Billed" icon="receipt" :prefix="$currency" :value="number_format((float) $patients->sum('total_fee'), 2)" />
            <x-ui.metric label="Collected" icon="banknote" :prefix="$currency" :value="number_format((float) $patients->sum('amount_paid'), 2)" />
            <x-ui.metric label="Receivable" icon="alert-circle" :prefix="$currency" :value="number_format((float) $patients->sum('amount_receivable'), 2)" :hint="$patients->where('amount_receivable', '>', 0)->count() . ' with a balance'" />
        </div>

        <x-ui.panel flush>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter by MR No, patient or consultant" data-table-filter="#emergency-patients-table" />
                <span class="toolbar-spacer"></span>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#emergency-patients-table">{{ $patients->count() }}</span> shown</span>
            </div>
            <div class="table-wrap">
                <table id="emergency-patients-table" class="table">
                    <thead>
                        <tr>
                            <th>Registered</th>
                            <th>MR No</th>
                            <th>Patient</th>
                            <th>Consultants</th>
                            <th class="num">Hours</th>
                            <th class="num">Total</th>
                            <th class="num">Paid</th>
                            <th class="num">Receivable</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $record)
                            @php $consultants = $consultantsOf($record); @endphp
                            <tr>
                                <td class="nowrap">
                                    {{ optional($record->created_at)->format('d M Y') }}
                                    <span class="cell-sub">{{ optional($record->created_at)->format('h:i A') }}</span>
                                </td>
                                <td><span class="id">{{ $record->mr_number }}</span></td>
                                <td>
                                    <span class="cell-strong">{{ $record->patient_name }}</span>
                                    <span class="cell-sub">{{ $record->age }} yrs · {{ $record->gender }}</span>
                                </td>
                                <td>
                                    @if (count($consultants))
                                        {{ collect($consultants)->pluck('name')->filter()->join(', ') }}
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td class="num">{{ $record->emergency_hours }}</td>
                                <td class="num">{{ number_format((float) $record->total_fee, 2) }}</td>
                                <td class="num">{{ number_format((float) $record->amount_paid, 2) }}</td>
                                <td class="num">
                                    @if ((float) $record->amount_receivable > 0)
                                        <span class="font-semibold text-warning-700">{{ number_format((float) $record->amount_receivable, 2) }}</span>
                                    @else
                                        <span class="muted">{{ number_format((float) $record->amount_receivable, 2) }}</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('emergency.patient.receipt', $record) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="receipt" />Receipt</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="9" class="p-0">
                                    <x-ui.empty-state icon="siren" title="No emergency patients yet" description="Patients you register in emergency appear here with their bills.">
                                        <x-slot:actions>
                                            <a href="{{ route('emergency.patients.create') }}" class="btn btn-primary btn-sm"><x-ui.icon name="plus" />Register emergency patient</a>
                                        </x-slot:actions>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
@endsection
