@extends('layouts.app')

@section('content')
<div class="finance-shell mx-auto max-w-7xl space-y-6 text-slate-800">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    <section class="relative overflow-hidden rounded-[1.75rem] bg-[#0f2f2c] px-6 py-7 text-white shadow-xl md:px-8">
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full border-[40px] border-[#194c47] opacity-70"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="finance-mono mb-3 text-xs uppercase tracking-[0.22em] text-teal-200">Hospital accounting · unified voucher desk</p>
                <h2 class="text-3xl font-extrabold tracking-tight md:text-4xl">One ledger for every receipt and payment.</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-teal-50/80">CR, CP, BR, BP and Contra Vouchers use one form while the system posts balanced Debit and Credit journal lines.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($canCreateGeneral || Auth::user()->hasPermission('Create Cash Handover'))
                    <a href="{{ route('finance.vouchers.create') }}" class="finance-focus rounded-xl bg-[#d7ff66] px-5 py-3 text-sm font-extrabold text-[#17312d] shadow-lg transition hover:-translate-y-0.5 hover:bg-[#e2ff8f]">New voucher</a>
                @endif
                <a href="{{ route('finance.vouchers.index') }}" class="finance-focus rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/15">View register</a>
            </div>
        </div>
    </section>

    @unless($selfOnly)
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="grid md:grid-cols-[1fr_auto_1fr]">
            <div class="p-6">
                <div class="flex items-center gap-2 text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <p class="finance-mono text-xs font-semibold uppercase tracking-wider">Receipt vouchers · this month</p>
                </div>
                <p class="mt-3 text-3xl font-extrabold tracking-tight">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($creditTotal, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Cash / Bank receipts (Credit nature)</p>
            </div>
            <div class="hidden w-px bg-slate-200 md:block"></div>
            <div class="border-t border-slate-200 p-6 md:border-l-0 md:border-t-0">
                <div class="flex items-center gap-2 text-rose-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                    <p class="finance-mono text-xs font-semibold uppercase tracking-wider">Payment vouchers · this month</p>
                </div>
                <p class="mt-3 text-3xl font-extrabold tracking-tight">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($debitTotal, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Cash / Bank payments (Debit nature)</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-6 py-3 text-sm">
            <span class="text-slate-500">Net voucher movement</span>
            <span class="finance-mono font-semibold {{ $creditTotal - $debitTotal >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($creditTotal - $debitTotal, 2) }}</span>
        </div>
    </section>
    @endunless

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_19rem]">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="text-lg font-extrabold">Recent vouchers</h3>
                    <p class="text-xs text-slate-500">Latest activity across the unified register</p>
                </div>
                <a href="{{ route('finance.vouchers.index') }}" class="finance-focus text-sm font-bold text-teal-700 hover:text-teal-900">Open full register →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50 text-left finance-mono text-[11px] uppercase tracking-wider text-slate-500">
                        <tr><th class="px-5 py-3">Voucher</th><th class="px-5 py-3">Category / party</th><th class="px-5 py-3">Type</th><th class="px-5 py-3 text-right">Amount</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentVouchers as $voucher)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-5 py-4">
                                <a href="{{ route('finance.vouchers.show', $voucher) }}" class="finance-mono text-xs font-semibold text-teal-800 hover:underline">{{ $voucher->voucher_no }}</a>
                                <p class="mt-1 text-xs text-slate-400">{{ $voucher->voucher_date->format('d M Y') }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm"><p class="font-bold text-slate-700">{{ $voucher->category->name }}</p><p class="text-xs text-slate-500">{{ $voucher->party_name ?: 'No counterparty' }}</p></td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-extrabold {{ $voucher->voucher_type === 'credit' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $voucher->accounting_type_code }} · {{ $voucher->accounting_type }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right finance-mono text-sm font-semibold">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($voucher->effective_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500">No accounting vouchers yet. Create the first Receipt or Payment Voucher.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-4">
            @if($cashSnapshot)
            <div class="rounded-2xl bg-[#e8f5f1] p-5 text-[#143d38]">
                <p class="finance-mono text-[11px] font-semibold uppercase tracking-wider">My collection cash</p>
                <p class="mt-2 text-2xl font-extrabold">{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($cashSnapshot['cash_in_hand'], 2) }}</p>
                <p class="mt-1 text-xs text-[#426b66]">Available to hand over</p>
                @if(Auth::user()->hasPermission('Create Cash Handover'))
                <a href="{{ route('finance.vouchers.create', ['category' => 'cash-handover']) }}" class="finance-focus mt-4 block rounded-xl bg-[#0f766e] px-4 py-2.5 text-center text-sm font-extrabold text-white hover:bg-[#0b5f59]">Create handover</a>
                @endif
            </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><span class="text-sm font-bold text-slate-600">Pending handovers</span><span class="finance-mono rounded-lg bg-amber-50 px-2.5 py-1 text-sm font-bold text-amber-700">{{ $pendingHandovers }}</span></div>
                <div class="mt-5 space-y-2 border-t border-slate-100 pt-4 text-sm font-bold">
                    <a href="{{ route('finance.ledger.index') }}" class="finance-focus flex items-center justify-between rounded-lg px-2 py-2 text-slate-700 hover:bg-slate-50"><span>Account ledger</span><span>→</span></a>
                    @if(!$selfOnly)<a href="{{ route('finance.reports.trial_balance') }}" class="finance-focus flex items-center justify-between rounded-lg px-2 py-2 text-slate-700 hover:bg-slate-50"><span>Trial balance</span><span>→</span></a><a href="{{ route('finance.accounts.index') }}" class="finance-focus flex items-center justify-between rounded-lg px-2 py-2 text-slate-700 hover:bg-slate-50"><span>Chart of accounts</span><span>→</span></a>@endif
                    <a href="{{ route('finance.vouchers.index', ['status' => 'pending']) }}" class="finance-focus flex items-center justify-between rounded-lg px-2 py-2 text-slate-700 hover:bg-slate-50"><span>Pending vouchers</span><span>→</span></a>
                    @if($canManageCategories)
                    <a href="{{ route('finance.categories.index') }}" class="finance-focus flex items-center justify-between rounded-lg px-2 py-2 text-slate-700 hover:bg-slate-50"><span>Voucher categories</span><span>→</span></a>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
