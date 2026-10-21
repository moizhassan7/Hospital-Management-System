@extends('layouts.app')

@section('title', 'Create accounting voucher')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Create accounting voucher" subtitle="One form for Receipt, Payment and Contra Vouchers with automatic Debit and Credit posting.">
        <x-slot:actions>
            <div id="typeBadge" class="hidden"></div>
            <a href="{{ route('finance.vouchers.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Voucher register
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('finance.vouchers.store') }}" id="voucherForm" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">
        @csrf

        <x-ui.panel title="Voucher identity" icon="receipt" flush>
            <div class="panel-body space-y-5">
                <div class="form-grid">
                    <x-ui.field label="Voucher category" for="category" required class="md:col-span-6">
                        <select id="category" name="category" required class="select">
                            <option value="">Select voucher category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->code }}" data-code="{{ $category->code }}" data-type="{{ $category->default_type }}" data-account="{{ $category->account->name }}" @selected(old('category', $selectedCode) === $category->code)>{{ $category->code === 'cash-handover' ? 'Contra Voucher' : ($category->default_type === 'credit' ? 'Receipt Voucher (Credit)' : 'Payment Voucher (Debit)') }} · {{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p id="categoryHelp" class="field-hint">The category selects the counter-account; Cash/Bank selects the settlement account.</p>
                    </x-ui.field>
                    <x-ui.field label="Voucher date" for="voucher_date" required class="md:col-span-6">
                        <input id="voucher_date" type="date" name="voucher_date" value="{{ old('voucher_date', date('Y-m-d')) }}" required class="input">
                    </x-ui.field>
                    <x-ui.field label="Amount" for="amount" required class="md:col-span-6">
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input id="amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="input input-num">
                        </div>
                        <p id="amountHint" class="field-hint">Enter the exact voucher amount.</p>
                    </x-ui.field>
                    <div id="paymentMethodGroup" class="md:col-span-6">
                        <x-ui.field label="Payment method" for="payment_method" required>
                            <select id="payment_method" name="payment_method" required class="select">
                                <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                                <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                            </select>
                        </x-ui.field>
                    </div>
                </div>

                <div id="patientPanel" class="hidden rounded-lg border border-line bg-subtle p-4">
                    <x-ui.field label="Patient receiving refund" for="patient_id" required>
                        <select id="patient_id" name="patient_id" class="select">
                            <option value="">Select patient</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->name }} · MR {{ $patient->mr_number }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <p class="field-hint mt-2">Use the reference field below for the original receipt, bill or discharge number.</p>
                </div>

                <div id="doctorPanel" class="hidden space-y-4 rounded-lg border border-line bg-subtle p-4">
                    <x-ui.field label="Doctor" for="doctor_id" required>
                        <select id="doctor_id" name="doctor_id" class="select">
                            <option value="">Select doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>{{ $doctor->name }} ({{ $doctor->code }})</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <div id="doctorBalance" class="hidden grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg border border-line bg-surface p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Share</p>
                            <p id="doctorShare" class="mt-1 text-sm font-bold">—</p>
                        </div>
                        <div class="rounded-lg border border-line bg-surface p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Paid</p>
                            <p id="doctorPaid" class="mt-1 text-sm font-bold">—</p>
                        </div>
                        <div class="rounded-lg border border-line bg-surface p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Pending</p>
                            <p id="doctorPending" class="mt-1 text-sm font-bold">—</p>
                        </div>
                    </div>
                </div>

                <div id="cashPanel" class="hidden rounded-lg border border-line bg-subtle p-4">
                    @if($cashSnapshot)
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Available collection cash</p>
                                <p class="mt-1 font-display text-2xl font-extrabold tabular-nums">{{ $currency }} {{ number_format($cashSnapshot['cash_in_hand'], 2) }}</p>
                            </div>
                            <x-ui.badge tone="warning">Receipt confirmation required</x-ui.badge>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-[12.5px]">
                            <div class="rounded-lg border border-line bg-surface p-2">Collected: <strong>{{ number_format($cashSnapshot['total_collected'], 2) }}</strong></div>
                            <div class="rounded-lg border border-line bg-surface p-2">Handed over: <strong>{{ number_format($cashSnapshot['previously_handed_over'], 2) }}</strong></div>
                        </div>
                    @else
                        <p class="text-sm font-semibold">Your role cannot create a Contra Voucher.</p>
                    @endif
                </div>

                <div id="partyPanel" class="hidden space-y-4">
                    <div class="form-grid">
                        <x-ui.field label="Counterparty type" for="party_type" class="md:col-span-6">
                            <select id="party_type" name="party_type" class="select">
                                <option value="other" @selected(old('party_type','other') === 'other')>Other / free text</option>
                                <option value="supplier" @selected(old('party_type') === 'supplier')>Supplier</option>
                                <option value="patient" @selected(old('party_type') === 'patient')>Patient</option>
                                <option value="doctor" @selected(old('party_type') === 'doctor')>Doctor</option>
                                <option value="user" @selected(old('party_type') === 'user')>Staff user</option>
                                <option value="owner" @selected(old('party_type') === 'owner')>Hospital owner</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Payee / received from" for="party_name" class="md:col-span-6">
                            <input id="party_name" name="party_name" value="{{ old('party_name') }}" placeholder="Name shown on voucher" class="input">
                        </x-ui.field>
                    </div>
                    <div id="supplierSelect" class="hidden">
                        <x-ui.field label="Supplier">
                            <select name="supplier_id" class="select">
                                <option value="">Select supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}{{ $supplier->company_name ? ' · '.$supplier->company_name : '' }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    </div>
                    <div id="staffSelect" class="hidden">
                        <x-ui.field label="Staff user (optional)">
                            <select name="staff_id" class="select">
                                <option value="">Use typed name</option>
                                @foreach($staff as $member)
                                    <option value="{{ $member->id }}" @selected(old('staff_id') == $member->id)>{{ $member->name }} ({{ $member->username }})</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    </div>
                    <div id="genericPatientSelect" class="hidden">
                        <x-ui.field label="Patient">
                            <select name="generic_patient_id" class="select">
                                <option value="">Select patient</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }} · MR {{ $patient->mr_number }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    </div>
                    <div id="genericDoctorSelect" class="hidden">
                        <x-ui.field label="Doctor">
                            <select name="generic_doctor_id" class="select">
                                <option value="">Select doctor</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->code }})</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    </div>
                </div>

                <div id="payPeriodPanel" class="hidden">
                    <x-ui.field label="Salary month" for="pay_period">
                        <input id="pay_period" type="month" name="pay_period" value="{{ old('pay_period') }}" class="input max-w-xs">
                    </x-ui.field>
                </div>

                <div class="form-grid">
                    <x-ui.field label="Reference number" for="reference_no" class="md:col-span-6">
                        <input id="reference_no" name="reference_no" value="{{ old('reference_no') }}" placeholder="Receipt, cheque or bill number" class="input">
                    </x-ui.field>
                    <x-ui.field label="Remarks" for="remarks" class="md:col-span-6">
                        <textarea id="remarks" name="remarks" rows="3" placeholder="Why this voucher was issued" class="input">{{ old('remarks') }}</textarea>
                    </x-ui.field>
                </div>
            </div>
        </x-ui.panel>

        <aside class="stack lg:sticky lg:top-[84px]">
            <x-ui.panel title="Posting preview" icon="layers" flush>
                <div class="panel-body space-y-3">
                    <div class="rounded-lg border border-line bg-subtle p-3">
                        <div class="flex justify-between text-[11px] font-semibold uppercase tracking-wider text-fg-muted"><span>Debit account</span><span>Dr</span></div>
                        <p id="debitAccount" class="mt-1 text-sm font-bold">Select a category</p>
                    </div>
                    <div class="rounded-lg border border-line bg-subtle p-3">
                        <div class="flex justify-between text-[11px] font-semibold uppercase tracking-wider text-fg-muted"><span>Credit account</span><span>Cr</span></div>
                        <p id="creditAccount" class="mt-1 text-sm font-bold">Select a category</p>
                    </div>
                    <div class="flex items-center justify-between border-t border-line pt-3">
                        <span class="text-[12.5px] text-fg-muted">Both sides</span>
                        <span id="previewAmount" class="font-mono text-sm font-semibold">{{ $currency }} 0.00</span>
                    </div>
                    <p id="postingNote" class="text-[12.5px] leading-5 text-fg-muted">A posted voucher always has equal Debit and Credit journal lines.</p>
                </div>
                <div class="panel-footer">
                    <button id="submitButton" type="submit" disabled class="btn btn-primary w-full">Select a category</button>
                </div>
            </x-ui.panel>
            <p class="px-1 text-center text-[12px] leading-5 text-fg-muted">Receipt and Payment Vouchers post immediately. Voiding creates an auditable Reversal Voucher.</p>
        </aside>
    </form>
@endsection

@push('scripts')
<script>
(() => {
    const currency = @json(get_setting('currency_symbol', 'Rs'));
    const category = document.getElementById('category');
    const method = document.getElementById('payment_method');
    const amount = document.getElementById('amount');
    const panels = ['patientPanel','doctorPanel','cashPanel','partyPanel','payPeriodPanel'];
    const cashInHand = @json($cashSnapshot['cash_in_hand'] ?? null);

    function paymentAccount() {
        return method.value === 'bank' ? 'Bank' : (method.value === 'cheque' ? 'Cheque Clearing' : 'Cash in Hand');
    }
    function documentType(type, code) {
        if (code === 'cash-handover') return 'Contra Voucher (CV)';
        if (type === 'credit') return method.value === 'cash' ? 'Cash Receipt (CR)' : 'Bank Receipt (BR)';
        if (type === 'debit') return method.value === 'cash' ? 'Cash Payment (CP)' : 'Bank Payment (BP)';
        return '';
    }
    function setVisible(id, visible) { document.getElementById(id)?.classList.toggle('hidden', !visible); }
    function updatePartyType() {
        const type = document.getElementById('party_type').value;
        setVisible('supplierSelect', type === 'supplier');
        setVisible('staffSelect', type === 'user');
        setVisible('genericPatientSelect', type === 'patient');
        setVisible('genericDoctorSelect', type === 'doctor');
    }
    function updatePreview() {
        const option = category.selectedOptions[0];
        const code = option?.dataset.code || '';
        const type = option?.dataset.type || '';
        const account = option?.dataset.account || 'Select a category';
        panels.forEach(id => setVisible(id, false));
        setVisible('patientPanel', code === 'patient-refund');
        setVisible('doctorPanel', code === 'doctor-payment');
        setVisible('cashPanel', code === 'cash-handover');
        setVisible('partyPanel', !!code && !['patient-refund','doctor-payment','cash-handover'].includes(code));
        setVisible('payPeriodPanel', code === 'salary-payment');

        const badge = document.getElementById('typeBadge');
        badge.classList.toggle('hidden', !type);
        badge.textContent = type ? documentType(type, code) : '';
        badge.className = `rounded-xl px-4 py-2 finance-mono text-xs font-bold uppercase tracking-wider ${code === 'cash-handover' ? 'bg-amber-50 text-amber-700' : (type === 'credit' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700')} ${type ? '' : 'hidden'}`;

        let debit = 'Select a category';
        let credit = 'Select a category';
        if (code === 'cash-handover') {
            debit = 'Hospital Cash in Hand'; credit = 'Staff Cash in Hand'; method.value = 'cash';
            amount.max = cashInHand ?? '';
            document.getElementById('amountHint').textContent = cashInHand === null ? 'Contra Voucher is unavailable.' : `Maximum staff cash custody: ${currency} ${Number(cashInHand).toFixed(2)}`;
            document.getElementById('postingNote').textContent = 'This Contra Voucher stays pending until Accounts confirms physical cash receipt.';
        } else if (type === 'credit') {
            debit = paymentAccount(); credit = account; amount.removeAttribute('max');
            document.getElementById('amountHint').textContent = 'Enter the exact amount received.';
            document.getElementById('postingNote').textContent = 'Saving posts equal Debit and Credit journal lines immediately.';
        } else if (type === 'debit') {
            debit = account; credit = paymentAccount(); amount.removeAttribute('max');
            document.getElementById('amountHint').textContent = 'Enter the exact amount paid.';
            document.getElementById('postingNote').textContent = 'Saving posts equal Debit and Credit journal lines immediately.';
        }
        document.getElementById('debitAccount').textContent = debit;
        document.getElementById('creditAccount').textContent = credit;
        const submit = document.getElementById('submitButton');
        submit.disabled = !code || (code === 'cash-handover' && cashInHand === null);
        submit.textContent = !code ? 'Select a category' : (code === 'cash-handover' ? 'Create pending Contra Voucher' : `Post ${documentType(type, code)}`);

        if (code === 'salary-payment') document.getElementById('party_type').value = 'user';
        if (code === 'owner-withdrawal') document.getElementById('party_type').value = 'owner';
        updatePartyType();
    }
    function updateAmount() {
        document.getElementById('previewAmount').textContent = `${currency} ${Number(amount.value || 0).toFixed(2)}`;
    }
    async function loadDoctorBalance() {
        const doctorId = document.getElementById('doctor_id').value;
        const panel = document.getElementById('doctorBalance');
        if (!doctorId) { panel.classList.add('hidden'); return; }
        const response = await fetch(`{{ url('/finance/api/doctor-balance') }}/${doctorId}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) return;
        const data = await response.json();
        document.getElementById('doctorShare').textContent = `${currency} ${Number(data.total_share).toFixed(2)}`;
        document.getElementById('doctorPaid').textContent = `${currency} ${Number(data.total_paid).toFixed(2)}`;
        document.getElementById('doctorPending').textContent = `${currency} ${Number(data.pending).toFixed(2)}`;
        amount.max = data.pending;
        panel.classList.remove('hidden');
    }

    category.addEventListener('change', updatePreview);
    method.addEventListener('change', updatePreview);
    amount.addEventListener('input', updateAmount);
    document.getElementById('party_type').addEventListener('change', updatePartyType);
    document.getElementById('doctor_id').addEventListener('change', loadDoctorBalance);
    updatePreview(); updateAmount(); updatePartyType();
    if (document.getElementById('doctor_id').value) loadDoctorBalance();
})();
</script>
@endpush
