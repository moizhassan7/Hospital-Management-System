@extends('layouts.app')

@section('title', 'Voucher daybook')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $difference = abs($debitTotal - $creditTotal);
        $balanced = $difference < 0.01;
        $statusTone = fn ($status) => match ($status) {
            'posted' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'neutral',
        };
    @endphp

    <x-ui.page-header title="Voucher daybook" subtitle="Cash Receipt, Cash Payment, Bank Receipt, Bank Payment and Contra Vouchers in one register.">
        <x-slot:actions>
            <a href="{{ route('finance.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Finance
            </a>
            @if($canCreate)
                <a href="{{ route('finance.vouchers.create') }}" class="btn btn-primary">
                    <x-ui.icon name="plus" />New voucher
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @unless($selfOnly)
        <section aria-label="Journal totals" class="metric-strip">
            <x-ui.metric label="Journal debit (Dr)" icon="arrow-right" :prefix="$currency" :value="number_format($debitTotal, 2)" />
            <x-ui.metric label="Journal credit (Cr)" icon="arrow-left" :prefix="$currency" :value="number_format($creditTotal, 2)" />
            <x-ui.metric label="Balance check" icon="scale" :prefix="$currency" :value="number_format($difference, 2)" :hint="$balanced ? 'Balanced' : 'Difference on filtered vouchers'" />
        </section>
    @endunless

    <x-ui.panel title="Filters" class="mt-5" flush>
        <form method="GET" action="{{ route('finance.vouchers.index') }}">
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="Search" class="md:col-span-4">
                        <input name="search" value="{{ request('search') }}" placeholder="Voucher, party or reference" class="input">
                    </x-ui.field>
                    <x-ui.field label="Posting nature" class="md:col-span-4">
                        <select name="voucher_type" class="select">
                            <option value="">Receipt + Payment</option>
                            <option value="credit" @selected(request('voucher_type') === 'credit')>Receipt (Credit)</option>
                            <option value="debit" @selected(request('voucher_type') === 'debit')>Payment (Debit)</option>
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Category" class="md:col-span-4">
                        <select name="category" class="select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->code }}" @selected(request('category') === $category->code)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Status" class="md:col-span-3">
                        <select name="status" class="select">
                            <option value="">All statuses</option>
                            @foreach(['posted','pending','rejected','void'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="From" class="md:col-span-3">
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="input">
                    </x-ui.field>
                    <x-ui.field label="To" class="md:col-span-3">
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="input">
                    </x-ui.field>
                    <div class="md:col-span-3 flex items-end gap-2">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <a href="{{ route('finance.vouchers.index') }}" class="btn btn-ghost">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel title="Vouchers" class="mt-5" flush>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Voucher</th>
                        <th>Category / party</th>
                        <th>Document type</th>
                        <th>Debit posting</th>
                        <th>Credit posting</th>
                        <th>Status</th>
                        <th class="actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        @php
                            $debitEntry = $voucher->entries->first(fn($entry) => !$entry->is_reversal && $entry->entry_type === 'debit');
                            $creditEntry = $voucher->entries->first(fn($entry) => !$entry->is_reversal && $entry->entry_type === 'credit');
                            $typeTone = $voucher->voucher_type === 'credit' ? 'success' : ($voucher->isCashHandover() ? 'warning' : 'danger');
                        @endphp
                        <tr>
                            <td>
                                <span class="id">{{ $voucher->voucher_no }}</span>
                                <span class="cell-sub">{{ $voucher->voucher_date->format('d M Y') }}</span>
                            </td>
                            <td>
                                <span class="cell-strong">{{ $voucher->category->name }}</span>
                                <span class="cell-sub">{{ $voucher->party_name ?: 'No counterparty' }}@if($voucher->reference_no) · Ref {{ $voucher->reference_no }}@endif</span>
                            </td>
                            <td>
                                <x-ui.badge :tone="$typeTone">{{ $voucher->accounting_type }} ({{ $voucher->accounting_type_code }})</x-ui.badge>
                                <span class="cell-sub">{{ ucfirst($voucher->voucher_type) }} posting</span>
                            </td>
                            <td>
                                @if($debitEntry)
                                    <span class="cell-strong">Dr · {{ $debitEntry->account->name }}</span>
                                    <span class="cell-sub"><x-ui.money :amount="$debitEntry->amount" /></span>
                                @else
                                    <span class="muted">Awaiting posting</span>
                                @endif
                            </td>
                            <td>
                                @if($creditEntry)
                                    <span class="cell-strong">Cr · {{ $creditEntry->account->name }}</span>
                                    <span class="cell-sub"><x-ui.money :amount="$creditEntry->amount" /></span>
                                @else
                                    <span class="muted">Awaiting posting</span>
                                @endif
                            </td>
                            <td><x-ui.badge :tone="$statusTone($voucher->status)">{{ ucfirst($voucher->status) }}</x-ui.badge></td>
                            <td class="actions">
                                <a href="{{ route('finance.vouchers.show', $voucher) }}" class="btn btn-ghost btn-sm">View / Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="7" class="p-0">
                                <x-ui.empty-state icon="receipt" title="No vouchers match these filters" description="Reset filters or create a new voucher." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
            <div class="panel-footer">{{ $vouchers->links() }}</div>
        @endif
    </x-ui.panel>
@endsection
