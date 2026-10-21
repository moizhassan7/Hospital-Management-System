@extends('layouts.app')

@section('title', $appointment->appointment_number)

@section('content')
@php
    $currency = get_setting('currency_symbol', 'Rs');
    $open = $appointment->isOpen();
    $paid = $appointment->isPaid();
    $tokenNo = $appointment->displayToken();
@endphp

    <x-ui.page-header :title="$appointment->patient_name" :subtitle="$appointment->appointment_number.' · '.$appointment->mr_number">
        <x-slot:actions>
            <a href="{{ route('patients.appointments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Appointments
            </a>
            @if ($paid)
                <a href="{{ route('patients.outdoor_receipt', $appointment->appointment_number) }}" class="btn btn-secondary" target="ReceiptWindow">
                    <x-ui.icon name="printer" />Print slip
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($appointment->token_status === 'provisional' && $tokenNo)
        <x-ui.alert tone="warning" class="mb-5" title="Provisional token">
            Token #{{ $tokenNo }} is provisional and will be confirmed when the required fee is paid.
        </x-ui.alert>
    @elseif ($appointment->hasGuaranteedToken() && $tokenNo)
        <x-ui.alert tone="success" class="mb-5" title="Reserved token">
            Token #{{ $tokenNo }} is reserved for this appointment.
        </x-ui.alert>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="stack">
            <x-ui.panel title="Patient" icon="user">
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Name</dt><dd class="font-semibold">{{ $appointment->patient_name }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">MR number</dt><dd class="id">{{ $appointment->mr_number }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Age / gender</dt><dd>{{ $appointment->age }} · {{ $appointment->gender }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Referred by</dt><dd>{{ $appointment->referred_by ?: '—' }}</dd></div>
                </dl>
            </x-ui.panel>

            <x-ui.panel title="Appointment" icon="calendar">
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Date</dt><dd class="font-semibold">{{ $appointment->appointment_date?->format('d M Y') }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Time</dt><dd>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : '—' }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Doctor</dt><dd>{{ $appointment->doctor_name }} <span class="cell-sub">{{ $appointment->doctor_code }}</span></dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Session</dt><dd>{{ $appointment->sessionLabel() }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Status</dt><dd><x-ui.badge :tone="\App\Support\OpdStates::appointmentTone((string) $appointment->status)">{{ \App\Support\OpdStates::appointmentLabel((string) $appointment->status) }}</x-ui.badge></dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Booked on</dt><dd>{{ $appointment->booking_date?->format('d M Y') ?: $appointment->created_at?->format('d M Y') }}</dd></div>
                </dl>
            </x-ui.panel>

            <x-ui.panel title="Token" icon="hash">
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Current token</dt><dd class="text-2xl font-extrabold">{{ $tokenNo ? '#'.$tokenNo : '—' }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Preferred (provisional)</dt><dd>{{ $appointment->preferred_token_number ? '#'.$appointment->preferred_token_number : '—' }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Reservation</dt><dd><x-ui.badge :tone="\App\Support\OpdStates::tokenTone($appointment->token_status)">{{ \App\Support\OpdStates::tokenLabel($appointment->token_status) }}</x-ui.badge></dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Reserved at</dt><dd>{{ $appointment->token_reserved_at?->format('d M Y h:i A') ?: '—' }}</dd></div>
                </dl>
            </x-ui.panel>

            <x-ui.panel title="Payment" icon="banknote">
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Consultation fee</dt><dd><x-ui.money :amount="$appointment->total_amount" /></dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Amount paid</dt><dd><x-ui.money :amount="$appointment->collectedAmount()" /></dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Outstanding</dt><dd><x-ui.money :amount="$appointment->outstandingFee()" /></dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Status</dt><dd><x-ui.badge :tone="\App\Support\OpdStates::paymentTone((string) $appointment->payment_status)">{{ \App\Support\OpdStates::paymentLabel((string) $appointment->payment_status) }}</x-ui.badge></dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Method</dt><dd>{{ $appointment->payment_method ? ucfirst($appointment->payment_method) : '—' }}</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Collected by</dt><dd>{{ $appointment->paymentCollectedBy->name ?? $appointment->receivedBy->name ?? '—' }}</dd></div>
                </dl>
            </x-ui.panel>

            <x-ui.panel title="Token history" icon="list">
                @if ($appointment->tokenClaims->isEmpty())
                    <x-ui.empty-state icon="hash" title="No token history" description="Tokens assigned to this appointment will appear here." />
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Token</th>
                                    <th>Status</th>
                                    <th>When</th>
                                    <th>By</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appointment->tokenClaims as $claim)
                                    <tr>
                                        <td class="id">#{{ $claim->token_number }}</td>
                                        <td><x-ui.badge :tone="\App\Support\OpdStates::tokenTone($claim->status)">{{ \App\Support\OpdStates::tokenLabel($claim->status) }}</x-ui.badge></td>
                                        <td class="nowrap">{{ $claim->created_at?->format('d M Y h:i A') }}</td>
                                        <td>{{ $claim->changedBy->name ?? '—' }}</td>
                                        <td class="muted">{{ $claim->reason ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.panel>
        </div>

        <aside class="stack">
            <x-ui.panel title="Actions" icon="sliders">
                <div class="flex flex-col gap-2">
                    @if ($open && ! $paid)
                        <button type="button" class="btn btn-primary btn-block" data-open-collect>
                            <x-ui.icon name="banknote" />Collect fee
                        </button>
                    @endif
                    @if ($open && $paid && $appointment->status === 'booked')
                        <form method="POST" action="{{ route('patients.appointments.check_in', $appointment) }}">
                            @csrf
                            <button class="btn btn-primary btn-block" type="submit"><x-ui.icon name="log-in" />Check in</button>
                        </form>
                    @endif
                    @if ($open && $paid && $appointment->status === 'checked_in')
                        <form method="POST" action="{{ route('patients.appointments.complete', $appointment) }}">
                            @csrf
                            <button class="btn btn-primary btn-block" type="submit"><x-ui.icon name="check" />Mark completed</button>
                        </form>
                    @endif
                    @if ($open)
                        <button type="button" class="btn btn-secondary btn-block" data-open-reschedule>
                            <x-ui.icon name="refresh" />Reschedule
                        </button>
                    @endif
                    @if ($open && $paid)
                        <button type="button" class="btn btn-secondary btn-block" data-open-reassign>
                            <x-ui.icon name="hash" />Reassign token
                        </button>
                    @endif
                    @if ($open)
                        <form method="POST" action="{{ route('patients.appointments.no_show', $appointment) }}" data-confirm="Mark this appointment as a no-show? A paid token stays reserved.">
                            @csrf
                            <button class="btn btn-secondary btn-block" type="submit">Mark no-show</button>
                        </form>
                        <form method="POST" action="{{ route('patients.appointments.cancel', $appointment) }}" data-confirm="Cancel this appointment? Payment history is kept and the token is released.">
                            @csrf
                            <input type="hidden" name="cancellation_reason" value="Cancelled at desk">
                            <button class="btn btn-danger btn-block" type="submit">Cancel appointment</button>
                        </form>
                    @endif
                </div>
            </x-ui.panel>
        </aside>
    </div>

    <x-ui.dialog id="collect_fee_dialog" title="Confirm appointment token" description="Would you like to keep the originally assigned token or assign a different token?" size="lg" icon="hash">
        <form method="POST" action="{{ route('patients.appointments.collect_fee', $appointment) }}" id="collect_fee_form" class="space-y-4">
            @csrf
            <input type="hidden" name="keep_original_token" id="keep_original_token" value="1">
            <input type="hidden" name="idempotency_key" value="{{ 'collect-'.$appointment->id.'-'.now()->timestamp }}">

            <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Patient</dt><dd class="font-semibold">{{ $appointment->patient_name }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">MR number</dt><dd>{{ $appointment->mr_number }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Doctor</dt><dd>{{ $appointment->doctor_name }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Date</dt><dd>{{ $appointment->appointment_date?->format('d M Y') }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Session</dt><dd>{{ $appointment->sessionLabel() }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Original token</dt><dd id="original_token_label">#{{ $appointment->preferred_token_number ?: $tokenNo ?: '—' }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Outstanding fee</dt><dd><x-ui.money :amount="$appointment->outstandingFee() ?: $appointment->total_amount" /></dd></div>
            </dl>

            <p id="original_availability" class="field-hint">{{ $inspect['available'] ? 'Token available.' : ($inspect['conflict'] ?: 'Choose a token.') }}</p>

            <fieldset class="space-y-2">
                <label class="flex items-start gap-2">
                    <input type="radio" name="token_choice" value="keep" {{ $inspect['available'] ? 'checked' : 'disabled' }}>
                    <span>Keep original token @if(!$inspect['available']) <span class="field-error inline">(unavailable)</span> @endif</span>
                </label>
                <label class="flex items-start gap-2">
                    <input type="radio" name="token_choice" value="other" {{ $inspect['available'] ? '' : 'checked' }}>
                    <span>Assign another token</span>
                </label>
            </fieldset>

            <x-ui.field label="Token number" for="collect_token_number" required>
                <input type="number" min="1" step="1" name="token_number" id="collect_token_number" class="input input-mono" value="{{ $inspect['available'] ? $inspect['preferred'] : $inspect['suggestion'] }}" required>
            </x-ui.field>
            <x-ui.field label="Amount paid" for="collect_amount_paid" required>
                <div class="input-group has-start">
                    <span class="input-affix is-start">{{ $currency }}</span>
                    <input type="number" min="0.01" step="0.01" name="amount_paid" id="collect_amount_paid" class="input input-money" value="{{ number_format($appointment->outstandingFee() ?: $appointment->total_amount, 2, '.', '') }}" required>
                </div>
            </x-ui.field>
            <x-ui.field label="Payment method" for="collect_payment_method">
                <select name="payment_method" id="collect_payment_method" class="select">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="cheque">Cheque</option>
                </select>
            </x-ui.field>
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-ghost" data-dialog-close>Cancel</button>
            <button type="submit" class="btn btn-primary" form="collect_fee_form">Confirm token &amp; collect fee</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="reschedule_dialog" title="Reschedule appointment" size="md" icon="refresh">
        <form method="POST" action="{{ route('patients.appointments.reschedule', $appointment) }}" id="reschedule_form" class="space-y-4">
            @csrf
            <x-ui.field label="Date" for="reschedule_date" required>
                <input type="date" name="appointment_date" id="reschedule_date" class="input" value="{{ $appointment->appointment_date?->toDateString() }}" required>
            </x-ui.field>
            <x-ui.field label="Time" for="reschedule_time" required>
                <input type="time" name="appointment_time" id="reschedule_time" class="input" value="{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') : '09:00' }}" required>
            </x-ui.field>
            <x-ui.field label="Doctor" for="reschedule_doctor" required>
                <select name="doctor_code" id="reschedule_doctor" class="select" required>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->code }}" @selected($doctor->code === $appointment->doctor_code)>{{ $doctor->code }} — {{ $doctor->name }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Token" for="reschedule_token" hint="Validated in the new doctor/date queue." required>
                <input type="number" min="1" name="token_number" id="reschedule_token" class="input input-mono" value="{{ $tokenNo }}" required>
            </x-ui.field>
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-ghost" data-dialog-close>Cancel</button>
            <button type="submit" class="btn btn-primary" form="reschedule_form">Save new slot</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="reassign_dialog" title="Reassign reserved token" size="md" icon="hash" tone="warning">
        <form method="POST" action="{{ route('patients.appointments.reassign_token', $appointment) }}" id="reassign_form" class="space-y-4">
            @csrf
            <p class="text-sm">Current reserved token: <strong>#{{ $tokenNo }}</strong>. The new number must be free in this doctor’s queue.</p>
            <x-ui.field label="New token" for="reassign_token" required>
                <input type="number" min="1" name="token_number" id="reassign_token" class="input input-mono" required>
            </x-ui.field>
            <x-ui.field label="Reason" for="reassign_reason" required>
                <input type="text" name="reason" id="reassign_reason" class="input" required placeholder="Why is the token changing?">
            </x-ui.field>
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-ghost" data-dialog-close>Cancel</button>
            <button type="submit" class="btn btn-primary" form="reassign_form">Reassign token</button>
        </x-slot:footer>
    </x-ui.dialog>

    <script>
        const inspect = @json($inspect);
        const original = inspect.preferred;
        const suggestion = inspect.suggestion;
        const originalAvailable = !!inspect.available;
        const keepInput = document.getElementById('keep_original_token');
        const tokenField = document.getElementById('collect_token_number');

        document.querySelectorAll('[data-open-collect]').forEach((btn) => btn.addEventListener('click', () => LC.openDialog(document.getElementById('collect_fee_dialog'))));
        document.querySelectorAll('[data-open-reschedule]').forEach((btn) => btn.addEventListener('click', () => LC.openDialog(document.getElementById('reschedule_dialog'))));
        document.querySelectorAll('[data-open-reassign]').forEach((btn) => btn.addEventListener('click', () => LC.openDialog(document.getElementById('reassign_dialog'))));

        document.querySelectorAll('input[name="token_choice"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                if (radio.value === 'keep' && originalAvailable) {
                    keepInput.value = '1';
                    tokenField.value = original;
                    tokenField.readOnly = true;
                } else {
                    keepInput.value = '0';
                    tokenField.value = suggestion;
                    tokenField.readOnly = false;
                }
            });
        });
        if (originalAvailable) {
            tokenField.readOnly = true;
        }
    </script>
@endsection
