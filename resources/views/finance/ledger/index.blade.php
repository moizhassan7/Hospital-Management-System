@extends('layouts.app')

@section('content')
<div class="finance-shell mx-auto max-w-7xl space-y-5 text-slate-800">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('finance.index') }}" class="finance-mono text-xs font-semibold uppercase tracking-wider text-teal-700 hover:text-teal-900">← Finance</a>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Account ledger</h2>
            <p class="mt-1 text-sm text-slate-500">Posted Debit and Credit journal lines · {{ $scopeLabel }}</p>
        </div>
        <button type="button" onclick="window.print()" class="no-print finance-focus rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-extrabold text-slate-700">Print ledger</button>
    </div>

    <form method="GET" class="no-print rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div><label class="mb-1 block text-xs font-bold text-slate-600">From date</label><input type="date" name="from_date" value="{{ $fromDate }}" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">To date</label><input type="date" name="to_date" value="{{ $toDate }}" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>
            @unless($selfOnly)
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Posting account</label><select name="account_id" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="">All posting accounts</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected($accountId == $account->id)>{{ $account->code }} · {{ $account->name }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Staff cash custodian</label><select name="user_id" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="">All staff</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }} ({{ $u->username }})</option>@endforeach</select></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Doctor party</label><select name="doctor_id" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="">All doctors</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected($doctorId == $doctor->id)>{{ $doctor->name }} ({{ $doctor->code }})</option>@endforeach</select></div>
            @endunless
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Journal side</label><select name="entry_type" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="">Debit + Credit</option><option value="debit" @selected($entryType === 'debit')>Debit (Dr)</option><option value="credit" @selected($entryType === 'credit')>Credit (Cr)</option></select></div>
            <div class="flex items-end gap-2"><button class="finance-focus flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-extrabold text-white">Apply</button><a href="{{ route('finance.ledger.index') }}" class="finance-focus rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-600">Reset</a></div>
        </div>
    </form>

    <section class="grid gap-3 sm:grid-cols-2 {{ $selectedAccount ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
        @if($selectedAccount)<div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="finance-mono text-[11px] uppercase tracking-wider text-slate-500">Opening balance</p><p class="mt-2 text-xl font-extrabold">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format(abs($opening_balance), 2) }} <span class="text-xs text-slate-400">{{ $opening_balance < 0 ? 'Cr' : 'Dr' }}</span></p></div>@endif
        <div class="rounded-2xl border border-sky-100 bg-sky-50/70 p-4"><p class="finance-mono text-[11px] uppercase tracking-wider text-sky-700">Debit (Dr)</p><p class="mt-2 text-xl font-extrabold text-sky-950">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($total_debit, 2) }}</p></div>
        <div class="rounded-2xl border border-violet-100 bg-violet-50/70 p-4"><p class="finance-mono text-[11px] uppercase tracking-wider text-violet-700">Credit (Cr)</p><p class="mt-2 text-xl font-extrabold text-violet-950">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($total_credit, 2) }}</p></div>
        @if($selectedAccount)<div class="rounded-2xl bg-[#0f2f2c] p-4 text-white"><p class="finance-mono text-[11px] uppercase tracking-wider text-teal-200">Closing balance</p><p class="mt-2 text-xl font-extrabold">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format(abs($closing_balance), 2) }} <span class="text-xs text-teal-200">{{ $closing_balance < 0 ? ($selectedAccount->normal_balance === 'debit' ? 'Cr' : 'Dr') : ucfirst($selectedAccount->normal_balance) }}</span></p></div>@else<div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="finance-mono text-[11px] uppercase tracking-wider text-slate-500">Balance check</p><p class="mt-2 text-xl font-extrabold {{ abs($total_debit-$total_credit) < .01 ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format(abs($total_debit-$total_credit), 2) }}</p></div>@endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full">
            <thead class="border-b border-slate-200 bg-slate-50 finance-mono text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3 text-left">Date / document</th><th class="px-4 py-3 text-left">Account</th><th class="px-4 py-3 text-left">Particulars</th><th class="px-4 py-3 text-left">Reference</th><th class="px-4 py-3 text-right">Debit</th><th class="px-4 py-3 text-right">Credit</th>@if($selectedAccount)<th class="px-4 py-3 text-right">Balance</th>@endif</tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($entries as $entry)<tr class="{{ $entry['is_reversal'] ? 'bg-amber-50/60' : 'hover:bg-slate-50' }}"><td class="whitespace-nowrap px-4 py-3"><p class="text-sm font-bold">{{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}</p><span class="finance-mono text-[10px] font-bold text-teal-700">{{ $entry['document_type'] }}</span>@if($entry['is_reversal'])<span class="ml-1 text-[10px] font-bold text-amber-700">REVERSAL</span>@endif</td><td class="px-4 py-3"><p class="finance-mono text-[10px] text-slate-400">{{ $entry['account_code'] }}</p><p class="text-xs font-bold text-slate-700">{{ $entry['account_name'] }}</p></td><td class="min-w-64 px-4 py-3"><p class="text-sm font-semibold text-slate-700">{{ $entry['module'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $entry['description'] }}</p>@if($entry['person'] !== '—')<p class="mt-1 text-[11px] text-slate-400">Party: {{ $entry['person'] }}</p>@endif</td><td class="px-4 py-3 finance-mono text-xs text-slate-600">{{ $entry['reference'] }}</td><td class="px-4 py-3 text-right finance-mono text-sm font-semibold text-sky-800">{{ $entry['debit'] ? number_format($entry['debit'],2) : '—' }}</td><td class="px-4 py-3 text-right finance-mono text-sm font-semibold text-violet-800">{{ $entry['credit'] ? number_format($entry['credit'],2) : '—' }}</td>@if($selectedAccount)<td class="px-4 py-3 text-right finance-mono text-sm font-bold">{{ number_format(abs($entry['running_balance']),2) }}</td>@endif</tr>@empty<tr><td colspan="{{ $selectedAccount ? 7 : 6 }}" class="px-5 py-14 text-center"><p class="font-bold text-slate-700">No posted journal lines found.</p><p class="mt-1 text-sm text-slate-500">Change filters or post a receipt/payment voucher.</p></td></tr>@endforelse</tbody>
            @if(count($entries))<tfoot><tr class="border-t border-slate-200 bg-slate-50 font-bold"><td colspan="4" class="px-4 py-3 text-right text-sm">Period totals</td><td class="px-4 py-3 text-right finance-mono text-sky-800">{{ number_format($total_debit,2) }}</td><td class="px-4 py-3 text-right finance-mono text-violet-800">{{ number_format($total_credit,2) }}</td>@if($selectedAccount)<td></td>@endif</tr></tfoot>@endif
        </table></div>
    </section>
</div>
@endsection

@push('styles')<style>@media print{.no-print,nav,aside,.sidebar{display:none!important}body,main{background:white!important}}</style>@endpush
