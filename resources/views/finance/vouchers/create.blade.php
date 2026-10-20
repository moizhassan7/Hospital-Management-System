@extends('layouts.app')

@section('content')
<div class="finance-shell mx-auto max-w-6xl space-y-5 text-slate-800">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('finance.vouchers.index') }}" class="finance-mono text-xs font-semibold uppercase tracking-wider text-teal-700 hover:text-teal-900">← Voucher register</a>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Create accounting voucher</h2>
            <p class="mt-1 text-sm text-slate-500">One form for Receipt, Payment and Contra Vouchers with automatic Debit and Credit posting.</p>
        </div>
        <div id="typeBadge" class="hidden rounded-xl px-4 py-2 finance-mono text-xs font-bold uppercase tracking-wider"></div>
    </div>

    @if($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><p class="font-extrabold">Check the highlighted voucher details.</p><ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('finance.vouchers.store') }}" id="voucherForm" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        @csrf
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4"><p class="finance-mono text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Voucher identity</p></div>
            <div class="space-y-6 p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="category" class="mb-1.5 block text-sm font-extrabold text-slate-700">Voucher category *</label>
                        <select id="category" name="category" required class="finance-focus w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm">
                            <option value="">Select voucher category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->code }}" data-code="{{ $category->code }}" data-type="{{ $category->default_type }}" data-account="{{ $category->account->name }}" @selected(old('category', $selectedCode) === $category->code)>{{ $category->code === 'cash-handover' ? 'Contra Voucher' : ($category->default_type === 'credit' ? 'Receipt Voucher (Credit)' : 'Payment Voucher (Debit)') }} · {{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p id="categoryHelp" class="mt-1.5 text-xs text-slate-500">The category selects the counter-account; Cash/Bank selects the settlement account.</p>
                    </div>
                    <div>
                        <label for="voucher_date" class="mb-1.5 block text-sm font-extrabold text-slate-700">Voucher date *</label>
                        <input id="voucher_date" type="date" name="voucher_date" value="{{ old('voucher_date', date('Y-m-d')) }}" required class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-3 text-sm">
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="amount" class="mb-1.5 block text-sm font-extrabold text-slate-700">Amount *</label>
                        <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">{{ get_setting('currency_symbol', 'Rs') }}</span><input id="amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="finance-focus w-full rounded-xl border border-slate-300 py-3 pl-12 pr-3 text-lg font-extrabold"></div>
                        <p id="amountHint" class="mt-1.5 text-xs text-slate-500">Enter the exact voucher amount.</p>
                    </div>
                    <div id="paymentMethodGroup">
                        <label for="payment_method" class="mb-1.5 block text-sm font-extrabold text-slate-700">Payment method *</label>
                        <select id="payment_method" name="payment_method" required class="finance-focus w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"><option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option><option value="bank" @selected(old('payment_method') === 'bank')>Bank</option><option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option></select>
                    </div>
                </div>

                <div id="patientPanel" class="hidden rounded-2xl border border-teal-100 bg-teal-50/60 p-4">
                    <label for="patient_id" class="mb-1.5 block text-sm font-extrabold text-teal-950">Patient receiving refund *</label>
                    <select id="patient_id" name="patient_id" class="finance-focus w-full rounded-xl border border-teal-200 bg-white px-3 py-3 text-sm"><option value="">Select patient</option>@foreach($patients as $patient)<option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->name }} · MR {{ $patient->mr_number }}</option>@endforeach</select>
                    <p class="mt-2 text-xs text-teal-800">Use the reference field below for the original receipt, bill or discharge number.</p>
                </div>

                <div id="doctorPanel" class="hidden space-y-4 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4">
                    <div><label for="doctor_id" class="mb-1.5 block text-sm font-extrabold text-indigo-950">Doctor *</label><select id="doctor_id" name="doctor_id" class="finance-focus w-full rounded-xl border border-indigo-200 bg-white px-3 py-3 text-sm"><option value="">Select doctor</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>{{ $doctor->name }} ({{ $doctor->code }})</option>@endforeach</select></div>
                    <div id="doctorBalance" class="hidden grid grid-cols-3 gap-2 text-center"><div class="rounded-xl bg-white p-3"><p class="text-[10px] font-bold uppercase text-slate-400">Share</p><p id="doctorShare" class="mt-1 text-sm font-extrabold">—</p></div><div class="rounded-xl bg-white p-3"><p class="text-[10px] font-bold uppercase text-slate-400">Paid</p><p id="doctorPaid" class="mt-1 text-sm font-extrabold">—</p></div><div class="rounded-xl bg-white p-3"><p class="text-[10px] font-bold uppercase text-amber-600">Pending</p><p id="doctorPending" class="mt-1 text-sm font-extrabold text-amber-700">—</p></div></div>
                </div>

                <div id="cashPanel" class="hidden rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    @if($cashSnapshot)
                    <div class="flex items-center justify-between gap-4"><div><p class="finance-mono text-[11px] font-semibold uppercase tracking-wider text-amber-700">Available collection cash</p><p class="mt-1 text-2xl font-extrabold text-amber-950">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($cashSnapshot['cash_in_hand'], 2) }}</p></div><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Receipt confirmation required</span></div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-amber-900"><div class="rounded-lg bg-white/70 p-2">Collected: <strong>{{ number_format($cashSnapshot['total_collected'], 2) }}</strong></div><div class="rounded-lg bg-white/70 p-2">Handed over: <strong>{{ number_format($cashSnapshot['previously_handed_over'], 2) }}</strong></div></div>
                    @else
                    <p class="text-sm font-bold text-amber-900">Your role cannot create a Contra Voucher.</p>
                    @endif
                </div>

                <div id="partyPanel" class="hidden space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><label for="party_type" class="mb-1.5 block text-sm font-extrabold text-slate-700">Counterparty type</label><select id="party_type" name="party_type" class="finance-focus w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"><option value="other" @selected(old('party_type','other') === 'other')>Other / free text</option><option value="supplier" @selected(old('party_type') === 'supplier')>Supplier</option><option value="patient" @selected(old('party_type') === 'patient')>Patient</option><option value="doctor" @selected(old('party_type') === 'doctor')>Doctor</option><option value="user" @selected(old('party_type') === 'user')>Staff user</option><option value="owner" @selected(old('party_type') === 'owner')>Hospital owner</option></select></div>
                        <div><label for="party_name" class="mb-1.5 block text-sm font-extrabold text-slate-700">Payee / received from</label><input id="party_name" name="party_name" value="{{ old('party_name') }}" placeholder="Name shown on voucher" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-3 text-sm"></div>
                    </div>
                    <div id="supplierSelect" class="hidden"><label class="mb-1.5 block text-sm font-extrabold text-slate-700">Supplier</label><select name="supplier_id" class="finance-focus w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"><option value="">Select supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}{{ $supplier->company_name ? ' · '.$supplier->company_name : '' }}</option>@endforeach</select></div>
                    <div id="staffSelect" class="hidden"><label class="mb-1.5 block text-sm font-extrabold text-slate-700">Staff user (optional)</label><select name="staff_id" class="finance-focus w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"><option value="">Use typed name</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected(old('staff_id') == $member->id)>{{ $member->name }} ({{ $member->username }})</option>@endforeach</select></div>
                    <div id="genericPatientSelect" class="hidden"><label class="mb-1.5 block text-sm font-extrabold text-slate-700">Patient</label><select name="generic_patient_id" class="finance-focus w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"><option value="">Select patient</option>@foreach($patients as $patient)<option value="{{ $patient->id }}">{{ $patient->name }} · MR {{ $patient->mr_number }}</option>@endforeach</select></div>
                    <div id="genericDoctorSelect" class="hidden"><label class="mb-1.5 block text-sm font-extrabold text-slate-700">Doctor</label><select name="generic_doctor_id" class="finance-focus w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"><option value="">Select doctor</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->code }})</option>@endforeach</select></div>
                </div>

                <div id="payPeriodPanel" class="hidden"><label for="pay_period" class="mb-1.5 block text-sm font-extrabold text-slate-700">Salary month</label><input id="pay_period" type="month" name="pay_period" value="{{ old('pay_period') }}" class="finance-focus w-full max-w-xs rounded-xl border border-slate-300 px-3 py-3 text-sm"></div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div><label for="reference_no" class="mb-1.5 block text-sm font-extrabold text-slate-700">Reference number</label><input id="reference_no" name="reference_no" value="{{ old('reference_no') }}" placeholder="Receipt, cheque or bill number" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-3 text-sm"></div>
                    <div><label for="remarks" class="mb-1.5 block text-sm font-extrabold text-slate-700">Remarks</label><textarea id="remarks" name="remarks" rows="3" placeholder="Why this voucher was issued" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-3 text-sm">{{ old('remarks') }}</textarea></div>
                </div>
            </div>
        </section>

        <aside class="space-y-4 lg:sticky lg:top-5 lg:self-start">
            <div class="overflow-hidden rounded-2xl bg-[#0f2f2c] text-white shadow-xl">
                <div class="border-b border-white/10 px-5 py-4"><p class="finance-mono text-[11px] font-semibold uppercase tracking-wider text-teal-200">Posting preview</p></div>
                <div class="space-y-3 p-5">
                    <div class="rounded-xl bg-white/10 p-3"><div class="flex justify-between gap-3 text-xs text-teal-100"><span>Debit account</span><span>Dr</span></div><p id="debitAccount" class="mt-1 text-sm font-extrabold">Select a category</p></div>
                    <div class="rounded-xl bg-white/10 p-3"><div class="flex justify-between gap-3 text-xs text-teal-100"><span>Credit account</span><span>Cr</span></div><p id="creditAccount" class="mt-1 text-sm font-extrabold">Select a category</p></div>
                    <div class="flex items-center justify-between border-t border-white/10 pt-3"><span class="text-xs text-teal-100">Both sides</span><span id="previewAmount" class="finance-mono text-sm font-semibold">{{ get_setting('currency_symbol', 'Rs') }} 0.00</span></div>
                    <p id="postingNote" class="text-xs leading-5 text-teal-100/75">A posted voucher always has equal Debit and Credit journal lines.</p>
                </div>
            </div>
            <button id="submitButton" type="submit" disabled class="finance-focus w-full rounded-xl bg-[#d7ff66] px-5 py-3.5 text-sm font-extrabold text-[#17312d] shadow-lg transition enabled:hover:-translate-y-0.5 enabled:hover:bg-[#e2ff8f] disabled:cursor-not-allowed disabled:opacity-50">Select a category</button>
            <p class="px-2 text-center text-xs leading-5 text-slate-500">Receipt and Payment Vouchers post immediately. Voiding creates an auditable Reversal Voucher.</p>
        </aside>
    </form>
</div>
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
