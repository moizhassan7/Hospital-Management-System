@extends('layouts.app')

@section('title', 'Finance')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $net = $creditTotal - $debitTotal;
    @endphp

    <x-ui.page-header title="Finance" subtitle="One ledger for every receipt, payment and contra voucher.">
        <x-slot:actions>
            <a href="{{ route('finance.vouchers.index') }}" class="btn btn-secondary">
                <x-ui.icon name="book-open" />Voucher register
            </a>
            @if($canCreateGeneral || Auth::user()->hasPermission('Create Cash Handover'))
                <a href="{{ route('finance.vouchers.create') }}" class="btn btn-primary">
                    <x-ui.icon name="plus" />New voucher
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @unless($selfOnly)
        <section aria-label="This month" class="metric-strip">
            <x-ui.metric label="Receipts this month" icon="banknote" :prefix="$currency" :value="number_format($creditTotal, 2)" hint="Cash / bank receipts (credit)" />
            <x-ui.metric label="Payments this month" icon="credit-card" :prefix="$currency" :value="number_format($debitTotal, 2)" hint="Cash / bank payments (debit)" />
            <x-ui.metric label="Net voucher movement" icon="scale" :prefix="$currency" :value="number_format($net, 2)" :hint="$net >= 0 ? 'Receipts exceed payments' : 'Payments exceed receipts'" />
            <x-ui.metric label="Pending handovers" icon="clock" :value="number_format($pendingHandovers)" hint="Contra vouchers awaiting receipt" />
        </section>
    @endunless

    <div class="mt-5 grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <x-ui.panel title="Recent vouchers" description="Latest activity across the unified register." flush>
            <x-slot:actions>
                <a href="{{ route('finance.vouchers.index') }}" class="btn btn-ghost btn-sm">Open register</a>
            </x-slot:actions>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Voucher</th>
                            <th>Category / party</th>
                            <th>Type</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentVouchers as $voucher)
                            <tr>
                                <td>
                                    <a href="{{ route('finance.vouchers.show', $voucher) }}" class="id">{{ $voucher->voucher_no }}</a>
                                    <span class="cell-sub">{{ $voucher->voucher_date->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <span class="cell-strong">{{ $voucher->category->name }}</span>
                                    <span class="cell-sub">{{ $voucher->party_name ?: 'No counterparty' }}</span>
                                </td>
                                <td>
                                    <x-ui.badge :tone="$voucher->voucher_type === 'credit' ? 'success' : ($voucher->isCashHandover() ? 'warning' : 'danger')">
                                        {{ $voucher->accounting_type_code }} · {{ $voucher->accounting_type }}
                                    </x-ui.badge>
                                </td>
                                <td class="num"><x-ui.money :amount="$voucher->effective_amount" /></td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="4" class="p-0">
                                    <x-ui.empty-state icon="receipt" title="No accounting vouchers yet" description="Create the first receipt, payment or contra voucher." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>

        <div class="stack">
            @if($cashSnapshot)
                <x-ui.panel title="My collection cash" icon="wallet" flush>
                    <div class="panel-body">
                        <p class="font-display text-[28px] font-extrabold tabular-nums leading-none">
                            {{ $currency }} {{ number_format($cashSnapshot['cash_in_hand'], 2) }}
                        </p>
                        <p class="mt-1 text-[12.5px] text-fg-muted">Available to hand over</p>
                    </div>
                    @if(Auth::user()->hasPermission('Create Cash Handover'))
                        <div class="panel-footer">
                            <a href="{{ route('finance.vouchers.create', ['category' => 'cash-handover']) }}" class="btn btn-primary w-full">
                                <x-ui.icon name="send" />Create handover
                            </a>
                        </div>
                    @endif
                </x-ui.panel>
            @endif

            <x-ui.panel title="Accounting" icon="landmark">
                <div class="stack-sm">
                    <a href="{{ route('finance.ledger.index') }}" class="btn btn-ghost justify-between w-full">Account ledger <x-ui.icon name="arrow-right" /></a>
                    @if(!$selfOnly)
                        <a href="{{ route('finance.reports.trial_balance') }}" class="btn btn-ghost justify-between w-full">Trial balance <x-ui.icon name="arrow-right" /></a>
                        <a href="{{ route('finance.accounts.index') }}" class="btn btn-ghost justify-between w-full">Chart of accounts <x-ui.icon name="arrow-right" /></a>
                    @endif
                    <a href="{{ route('finance.vouchers.index', ['status' => 'pending']) }}" class="btn btn-ghost justify-between w-full">Pending vouchers <x-ui.icon name="arrow-right" /></a>
                    @if($canManageCategories)
                        <a href="{{ route('finance.categories.index') }}" class="btn btn-ghost justify-between w-full">Voucher categories <x-ui.icon name="arrow-right" /></a>
                    @endif
                </div>
            </x-ui.panel>
        </div>
    </div>
@endsection
