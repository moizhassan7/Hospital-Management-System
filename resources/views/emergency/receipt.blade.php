@extends('layouts.app')

@section('title', 'Emergency receipt')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $hospitalName = get_setting('hospital_name', config('app.name', 'Hospital'));
        $hospitalLogo = get_setting('logo');
        $hospitalAddress = get_setting('address');
        $record = $emergencyPatient;
        $consultants = $record->consultants;
        // Older saves stored the list as a JSON string inside the JSON column.
        if (is_string($consultants)) {
            $consultants = json_decode($consultants, true);
        }
        $consultants = is_array($consultants) ? $consultants : [];
        $hours = (int) $record->emergency_hours;
        $extraHours = max(0, $hours - 1);
        $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    @endphp

    <x-ui.page-header title="Emergency receipt" :subtitle="$record->patient_name . ' · MR ' . $record->mr_number" class="no-print">
        <x-slot:actions>
            <a href="{{ route('emergency.patients.all') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Emergency patients
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <x-ui.icon name="printer" />Print receipt
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <article class="mx-auto max-w-[760px] rounded-card border border-line bg-white p-8 shadow-card print:max-w-none print:border-0 print:p-0 print:shadow-none" aria-label="Emergency receipt">
        <header class="flex items-start justify-between gap-6 border-b border-line pb-5">
            <div class="flex items-center gap-3">
                @if ($hospitalLogo)
                    <img src="{{ asset('storage/' . $hospitalLogo) }}" alt="" class="size-12 rounded-lg border border-line object-contain p-1">
                @endif
                <div>
                    <p class="font-display text-lg font-extrabold tracking-tight text-fg">{{ $hospitalName }}</p>
                    @if ($hospitalAddress)
                        <p class="mt-0.5 max-w-sm text-[12.5px] text-fg-muted">{{ $hospitalAddress }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-fg-muted">Emergency receipt</p>
                <p class="mt-1 font-mono text-[15px] font-semibold text-fg">#{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-0.5 text-[12.5px] text-fg-muted">{{ optional($record->created_at)->format('d M Y, h:i A') }}</p>
            </div>
        </header>

        <dl class="kv border-b border-line py-5 sm:grid-cols-[max-content_1fr_max-content_1fr]">
            <dt>Patient</dt>
            <dd class="font-semibold">{{ $record->patient_name }}</dd>
            <dt>MR No</dt>
            <dd class="font-mono">{{ $record->mr_number }}</dd>
            <dt>Age / gender</dt>
            <dd>{{ $record->age }} yrs · {{ $record->gender }}</dd>
            <dt>Emergency hours</dt>
            <dd>{{ $hours }}</dd>
        </dl>

        <section class="py-5">
            <h2 class="section-heading">Consultants</h2>
            @if (count($consultants))
                <div class="table-wrap rounded-lg border border-line">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Consultant</th>
                                <th class="num">Fee</th>
                                <th class="num">Qty</th>
                                <th class="num">Discount</th>
                                <th class="num">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($consultants as $consultant)
                                <tr>
                                    <td class="cell-strong">{{ $consultant['name'] ?? '—' }}</td>
                                    <td class="num">{{ number_format((float) ($consultant['fee'] ?? 0), 2) }}</td>
                                    <td class="num">{{ $consultant['qty'] ?? 1 }}</td>
                                    <td class="num">{{ number_format((float) ($consultant['discount'] ?? 0), 2) }}</td>
                                    <td class="num font-semibold">{{ number_format((float) ($consultant['total'] ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-[13px] text-fg-muted">No consultants added.</p>
            @endif
        </section>

        <section class="ml-auto max-w-[380px] border-t border-line pt-4">
            <div class="ledger">
                <div class="ledger-row">
                    <span class="ledger-op"></span>
                    <span class="ledger-label">First hour charges</span>
                    <span class="text-right font-mono tabular-nums">{{ $money($record->first_hour_charges) }}</span>
                </div>
                @if ($extraHours > 0)
                    <div class="ledger-row">
                        <span class="ledger-op">+</span>
                        <span class="ledger-label">Additional hours<small>{{ $extraHours }} × {{ $money($record->other_hours_charges) }}</small></span>
                        <span class="text-right font-mono tabular-nums">{{ $money($extraHours * (float) $record->other_hours_charges) }}</span>
                    </div>
                @endif
                @if (count($consultants))
                    <div class="ledger-row">
                        <span class="ledger-op">+</span>
                        <span class="ledger-label">Consultant fees<small>{{ count($consultants) }} {{ \Illuminate\Support\Str::plural('consultant', count($consultants)) }}</small></span>
                        <span class="text-right font-mono tabular-nums">{{ $money(collect($consultants)->sum(fn ($c) => (float) ($c['total'] ?? 0))) }}</span>
                    </div>
                @endif
                <div class="ledger-row ledger-total">
                    <span class="ledger-op">=</span>
                    <span class="ledger-label">Total bill</span>
                    <span class="text-right font-mono text-[15px] font-bold tabular-nums">{{ $money($record->total_fee) }}</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-op">−</span>
                    <span class="ledger-label">Amount paid</span>
                    <span class="text-right font-mono tabular-nums">{{ $money($record->amount_paid) }}</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-op">=</span>
                    <span class="ledger-label">Amount receivable</span>
                    <span @class(['text-right font-mono font-semibold tabular-nums', 'text-warning-700' => (float) $record->amount_receivable > 0])>{{ $money($record->amount_receivable) }}</span>
                </div>
            </div>
        </section>

        <footer class="mt-8 flex items-end justify-between gap-6 border-t border-line pt-4 text-[12px] text-fg-muted">
            <p>Received by: <span class="font-medium text-fg">{{ $record->receivedBy->name ?? '—' }}</span></p>
            <p>Printed {{ now()->format('d M Y, h:i A') }}</p>
        </footer>
    </article>
@endsection
