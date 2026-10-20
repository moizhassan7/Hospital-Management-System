@extends('layouts.app')

@section('content')
<div class="finance-shell mx-auto max-w-7xl space-y-5 text-slate-800">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('finance.index') }}" class="finance-mono text-xs font-semibold uppercase tracking-wider text-teal-700 hover:text-teal-900">← Finance</a>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Voucher daybook</h2>
            <p class="mt-1 text-sm text-slate-500">Cash Receipt, Cash Payment, Bank Receipt, Bank Payment and Contra Vouchers in one register.</p>
        </div>
        @if($canCreate)
        <a href="{{ route('finance.vouchers.create') }}" class="finance-focus rounded-xl bg-[#0f766e] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-teal-900/10 transition hover:-translate-y-0.5 hover:bg-[#0b5f59]">New voucher</a>
        @endif
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    @unless($selfOnly)
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-sky-100 bg-sky-50/70 p-4"><p class="finance-mono text-[11px] font-semibold uppercase tracking-wider text-sky-700">Journal Debit (Dr)</p><p class="mt-2 text-xl font-extrabold text-sky-950">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($debitTotal, 2) }}</p></div>
        <div class="rounded-2xl border border-violet-100 bg-violet-50/70 p-4"><p class="finance-mono text-[11px] font-semibold uppercase tracking-wider text-violet-700">Journal Credit (Cr)</p><p class="mt-2 text-xl font-extrabold text-violet-950">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($creditTotal, 2) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="finance-mono text-[11px] font-semibold uppercase tracking-wider text-slate-500">Balance check</p><div class="mt-2 flex items-center gap-2"><p class="text-xl font-extrabold {{ abs($debitTotal - $creditTotal) < 0.01 ? 'text-emerald-700' : 'text-rose-700' }}">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format(abs($debitTotal - $creditTotal), 2) }}</p>@if(abs($debitTotal - $creditTotal) < 0.01)<span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-extrabold uppercase text-emerald-700">Balanced</span>@endif</div></div>
    </div>
    @endunless

    <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2"><label class="mb-1 block text-xs font-bold text-slate-600">Search</label><input name="search" value="{{ request('search') }}" placeholder="Voucher, party or reference" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Posting nature</label><select name="voucher_type" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="">Receipt + Payment</option><option value="credit" @selected(request('voucher_type') === 'credit')>Receipt (Credit)</option><option value="debit" @selected(request('voucher_type') === 'debit')>Payment (Debit)</option></select></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Category</label><select name="category" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->code }}" @selected(request('category') === $category->code)>{{ $category->name }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Status</label><select name="status" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="">All statuses</option>@foreach(['posted','pending','rejected','void'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="flex items-end gap-2"><button class="finance-focus flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-slate-700">Filter</button><a href="{{ route('finance.vouchers.index') }}" class="finance-focus rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-600">Reset</a></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">From</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">To</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50 text-left finance-mono text-[11px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Voucher</th><th class="px-5 py-3">Category / party</th><th class="px-5 py-3">Document type</th><th class="px-5 py-3">Debit posting</th><th class="px-5 py-3">Credit posting</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vouchers as $voucher)
                    @php
                        $statusClass = match($voucher->status) { 'posted' => 'bg-emerald-50 text-emerald-700', 'pending' => 'bg-amber-50 text-amber-700', 'rejected' => 'bg-rose-50 text-rose-700', default => 'bg-slate-100 text-slate-600' };
                        $debitEntry = $voucher->entries->first(fn($entry) => !$entry->is_reversal && $entry->entry_type === 'debit');
                        $creditEntry = $voucher->entries->first(fn($entry) => !$entry->is_reversal && $entry->entry_type === 'credit');
                    @endphp
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-5 py-4"><p class="finance-mono text-xs font-semibold text-slate-800">{{ $voucher->voucher_no }}</p><p class="mt-1 text-xs text-slate-400">{{ $voucher->voucher_date->format('d M Y') }}</p></td>
                        <td class="px-5 py-4 text-sm"><p class="font-bold text-slate-700">{{ $voucher->category->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $voucher->party_name ?: 'No counterparty' }}@if($voucher->reference_no) · Ref {{ $voucher->reference_no }}@endif</p></td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-extrabold {{ $voucher->voucher_type === 'credit' ? 'bg-emerald-50 text-emerald-700' : ($voucher->isCashHandover() ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">{{ $voucher->accounting_type }} ({{ $voucher->accounting_type_code }})</span><p class="mt-1 text-[10px] font-bold uppercase text-slate-400">{{ ucfirst($voucher->voucher_type) }} posting</p></td>
                        <td class="min-w-48 px-5 py-4">@if($debitEntry)<p class="text-xs font-extrabold text-sky-800">Dr · {{ $debitEntry->account->name }}</p><p class="mt-1 finance-mono text-xs text-slate-600">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format((float)$debitEntry->amount, 2) }}</p>@else<span class="text-xs text-slate-400">Awaiting posting</span>@endif</td>
                        <td class="min-w-48 px-5 py-4">@if($creditEntry)<p class="text-xs font-extrabold text-violet-800">Cr · {{ $creditEntry->account->name }}</p><p class="mt-1 finance-mono text-xs text-slate-600">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format((float)$creditEntry->amount, 2) }}</p>@else<span class="text-xs text-slate-400">Awaiting posting</span>@endif</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst($voucher->status) }}</span></td>
                        <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('finance.vouchers.show', $voucher) }}" class="finance-focus text-sm font-extrabold text-teal-700 hover:text-teal-900">View / Print</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-14 text-center"><p class="font-bold text-slate-700">No vouchers match these filters.</p><p class="mt-1 text-sm text-slate-500">Reset filters or create a new voucher.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $vouchers->links() }}</div>@endif
    </section>
</div>
@endsection
