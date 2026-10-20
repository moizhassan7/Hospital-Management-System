@extends('layouts.app')

@section('content')
@php
    $meta = $voucher->metadata ?? [];
    $statusClass = match($voucher->status) { 'posted' => 'bg-emerald-100 text-emerald-800', 'pending' => 'bg-amber-100 text-amber-800', 'rejected' => 'bg-rose-100 text-rose-800', default => 'bg-slate-200 text-slate-700' };
@endphp
<div class="finance-shell mx-auto max-w-5xl space-y-5 text-slate-800">
    <div class="no-print flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('finance.vouchers.index') }}" class="finance-mono text-xs font-semibold uppercase tracking-wider text-teal-700 hover:text-teal-900">← Voucher register</a>
        <div class="flex flex-wrap gap-2"><button type="button" onclick="window.print()" class="finance-focus rounded-xl bg-[#0f766e] px-4 py-2.5 text-sm font-extrabold text-white hover:bg-[#0b5f59]">Print voucher</button></div>
    </div>

    @if(session('success'))<div class="no-print rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="no-print rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <article class="voucher-sheet overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl">
        <div class="h-2 {{ $voucher->voucher_type === 'credit' ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
        <header class="flex flex-col gap-5 border-b border-dashed border-slate-300 px-7 py-6 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                @if(get_setting('logo'))<img src="{{ asset('storage/'.get_setting('logo')) }}" alt="Hospital logo" class="h-14 w-14 rounded-xl border border-slate-200 object-contain p-1">@endif
                <div><p class="text-xl font-extrabold tracking-tight">{{ get_setting('hospital_name', 'Hospital Management System') }}</p><p class="mt-1 max-w-md text-xs text-slate-500">{{ get_setting('address', '') }}</p></div>
            </div>
            <div class="sm:text-right"><p class="finance-mono text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Accounting document</p><p class="mt-1 text-2xl font-extrabold {{ $voucher->voucher_type === 'credit' ? 'text-emerald-700' : ($voucher->isCashHandover() ? 'text-amber-700' : 'text-rose-700') }}">{{ $voucher->accounting_type }}</p><p class="finance-mono text-xs font-bold text-slate-400">{{ $voucher->accounting_type_code }}</p><span class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-extrabold {{ $statusClass }}">{{ ucfirst($voucher->status) }}</span></div>
        </header>

        <div class="grid gap-0 md:grid-cols-[1fr_17rem]">
            <div class="px-7 py-6">
                <div class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                    <div><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">Voucher number</p><p class="mt-1 finance-mono text-sm font-semibold text-slate-800">{{ $voucher->voucher_no }}</p></div>
                    <div><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">Voucher date</p><p class="mt-1 text-sm font-bold">{{ $voucher->voucher_date->format('d F Y') }}</p></div>
                    <div><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">Category</p><p class="mt-1 text-sm font-bold">{{ $voucher->category->name }}</p></div>
                    <div><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">Settlement account</p><p class="mt-1 text-sm font-bold">{{ $voucher->paymentAccount->name ?? ucfirst($voucher->payment_method) }}</p></div>
                    <div><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $voucher->voucher_type === 'credit' ? 'Received from' : 'Paid to' }}</p><p class="mt-1 text-sm font-bold">{{ $voucher->party_name ?: '—' }}</p>@if(!empty($meta['mr_number']))<p class="mt-1 text-xs text-slate-500">MR {{ $meta['mr_number'] }}</p>@endif</div>
                    <div><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">Reference</p><p class="mt-1 text-sm font-bold">{{ $voucher->reference_no ?: '—' }}</p></div>
                </div>

                @if($voucher->remarks)
                <div class="mt-6 rounded-xl bg-slate-50 p-4"><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-400">Remarks</p><p class="mt-1 text-sm leading-6 text-slate-700">{{ $voucher->remarks }}</p></div>
                @endif

                @if($voucher->category->code === 'doctor-payment' && isset($meta['total_share']))
                <div class="mt-6 grid grid-cols-3 gap-2"><div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] font-bold uppercase text-slate-400">Total share</p><p class="mt-1 text-sm font-extrabold">{{ number_format($meta['total_share'], 2) }}</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] font-bold uppercase text-slate-400">Previously paid</p><p class="mt-1 text-sm font-extrabold">{{ number_format($meta['previously_paid'], 2) }}</p></div><div class="rounded-xl bg-amber-50 p-3"><p class="text-[10px] font-bold uppercase text-amber-600">Balance</p><p class="mt-1 text-sm font-extrabold text-amber-800">{{ number_format($meta['balance'], 2) }}</p></div></div>
                @endif

                @if($voucher->isCashHandover())
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50/70 p-4">
                    <div class="flex flex-wrap justify-between gap-3"><div><p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-amber-700">Cash custody handover</p><p class="mt-1 text-sm text-amber-900">Collected: <strong>{{ number_format($meta['total_collected'] ?? 0, 2) }}</strong> · Previously handed over: <strong>{{ number_format($meta['previously_handed_over'] ?? 0, 2) }}</strong></p></div></div>
                    @if(isset($meta['received_amount']))<div class="mt-3 grid grid-cols-2 gap-2 text-sm"><div class="rounded-lg bg-white/80 p-3">Received <strong class="float-right">{{ number_format($meta['received_amount'], 2) }}</strong></div><div class="rounded-lg bg-white/80 p-3">Difference <strong class="float-right">{{ number_format($meta['difference'] ?? 0, 2) }}</strong></div></div>@endif
                    @if(!empty($meta['receiver_remarks']))<p class="mt-3 text-xs text-amber-900">Receiver note: {{ $meta['receiver_remarks'] }}</p>@endif
                </div>
                @endif
            </div>

            <aside class="border-t border-slate-200 bg-[#f3f8f6] px-6 py-6 md:border-l md:border-t-0">
                <p class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-slate-500">Voucher amount</p>
                <p class="mt-2 text-2xl font-extrabold tracking-tight text-[#0f2f2c]">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($voucher->effective_amount, 2) }}</p>
                @if($voucher->actual_amount !== null && (float)$voucher->actual_amount !== (float)$voucher->amount)<p class="mt-1 text-xs text-slate-500">Expected {{ number_format((float)$voucher->amount, 2) }}</p>@endif
                <div class="mt-6 space-y-3 border-t border-slate-200 pt-5 text-xs"><div><span class="text-slate-400">Prepared by</span><p class="mt-1 font-bold text-slate-700">{{ $voucher->createdBy->name ?? '—' }}</p></div><div><span class="text-slate-400">Posted by</span><p class="mt-1 font-bold text-slate-700">{{ $voucher->postedBy->name ?? ($voucher->status === 'pending' ? 'Awaiting receipt' : '—') }}</p></div>@if($voucher->posted_at)<div><span class="text-slate-400">Posted at</span><p class="mt-1 font-bold text-slate-700">{{ $voucher->posted_at->format('d M Y, h:i A') }}</p></div>@endif</div>
            </aside>
        </div>

        <section class="border-t border-dashed border-slate-300 px-7 py-6">
            <div class="mb-3 flex items-center justify-between"><div><h3 class="text-sm font-extrabold">Journal posting</h3><p class="text-xs text-slate-500">Original posting and any subsequent reversal lines</p></div>@if(in_array($voucher->status, ['posted','void']))<span class="finance-mono text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Balanced</span>@endif</div>
            <div class="overflow-hidden rounded-xl border border-slate-200"><table class="min-w-full"><thead class="bg-slate-50 finance-mono text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-2.5 text-left">Account</th><th class="px-4 py-2.5 text-right">Debit</th><th class="px-4 py-2.5 text-right">Credit</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($voucher->entries as $entry)<tr class="{{ $entry->is_reversal ? 'bg-amber-50' : '' }}"><td class="px-4 py-3 text-sm"><span class="finance-mono mr-2 text-xs text-slate-400">{{ $entry->account->code }}</span><strong>{{ $entry->account->name }}</strong>@if($entry->is_reversal)<span class="ml-2 rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-amber-700">Reversal</span>@endif</td><td class="px-4 py-3 text-right finance-mono text-sm">{{ $entry->entry_type === 'debit' ? number_format((float)$entry->amount, 2) : '—' }}</td><td class="px-4 py-3 text-right finance-mono text-sm">{{ $entry->entry_type === 'credit' ? number_format((float)$entry->amount, 2) : '—' }}</td></tr>@empty<tr><td colspan="3" class="px-4 py-5 text-center text-sm text-slate-500">Journal lines will be created when this voucher is posted.</td></tr>@endforelse</tbody></table></div>
        </section>

        <footer class="grid grid-cols-2 gap-12 border-t border-slate-200 px-7 py-10 text-center text-xs text-slate-500"><div class="border-t border-slate-400 pt-2">Prepared by</div><div class="border-t border-slate-400 pt-2">Authorized / received by</div></footer>
    </article>

    @if($voucher->isCashHandover() && $voucher->status === 'pending' && $canReceive)
    <section class="no-print grid gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 md:grid-cols-2">
        <form method="POST" action="{{ route('finance.vouchers.receive', $voucher) }}" class="space-y-3">@csrf<h3 class="font-extrabold text-amber-950">Confirm physical cash receipt</h3><input type="number" name="received_amount" step="0.01" min="0.01" value="{{ $voucher->amount }}" required class="finance-focus w-full rounded-xl border border-amber-300 bg-white px-3 py-2.5"><textarea name="receiver_remarks" rows="2" placeholder="Receiver note (optional)" class="finance-focus w-full rounded-xl border border-amber-300 bg-white px-3 py-2.5 text-sm"></textarea><button class="finance-focus rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-emerald-800">Receive and post</button></form>
        <form method="POST" action="{{ route('finance.vouchers.reject', $voucher) }}" class="space-y-3 border-t border-amber-200 pt-4 md:border-l md:border-t-0 md:pl-5 md:pt-0">@csrf<h3 class="font-extrabold text-amber-950">Reject Contra Voucher</h3><textarea name="receiver_remarks" rows="2" placeholder="Reason for rejection" class="finance-focus w-full rounded-xl border border-amber-300 bg-white px-3 py-2.5 text-sm"></textarea><button onclick="return confirm('Reject this Contra Voucher?')" class="finance-focus rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-extrabold text-rose-700 hover:bg-rose-50">Reject voucher</button></form>
    </section>
    @endif

    @if($voucher->status === 'posted' && $canVoid)
    <details class="no-print rounded-2xl border border-slate-200 bg-white p-5"><summary class="cursor-pointer text-sm font-extrabold text-slate-700">Reverse and void this voucher</summary><form method="POST" action="{{ route('finance.vouchers.void', $voucher) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">@csrf<input name="void_reason" required minlength="5" placeholder="Required reversal reason" class="finance-focus flex-1 rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><button onclick="return confirm('Create a balanced Reversal Voucher and mark this document void?')" class="finance-focus rounded-xl bg-rose-700 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-rose-800">Post reversal</button></form></details>
    @endif
    @if($voucher->status === 'void')<div class="no-print rounded-xl border border-slate-300 bg-slate-100 p-4 text-sm text-slate-700"><strong>Voided:</strong> {{ $voucher->void_reason }} · {{ $voucher->voidedBy->name ?? 'User' }} on {{ optional($voucher->voided_at)->format('d M Y, h:i A') }}</div>@endif
</div>
@endsection

@push('styles')
<style>
@media print {
    aside:not(.voucher-sheet aside), body > aside, header, .no-print { display: none !important; }
    body, main { background: white !important; padding: 0 !important; }
    .voucher-sheet { box-shadow: none !important; border: 1px solid #94a3b8 !important; border-radius: 0 !important; }
}
</style>
@endpush
