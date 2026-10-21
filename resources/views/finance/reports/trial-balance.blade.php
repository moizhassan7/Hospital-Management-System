@extends('layouts.app')

@section('title', 'Trial balance')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $balanced = abs($total_debit - $total_credit) < 0.01;
    @endphp

    <x-ui.page-header title="Trial balance" :subtitle="'Account balances as at ' . \Carbon\Carbon::parse($toDate)->format('d F Y') . '.'">
        <x-slot:actions>
            <a href="{{ route('finance.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Finance
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary">
                <x-ui.icon name="printer" />Print
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="As at date" class="no-print" flush>
        <form method="GET" action="{{ route('finance.reports.trial_balance') }}">
            <div class="panel-body">
                <div class="flex flex-wrap items-end gap-3">
                    <x-ui.field label="As at date">
                        <input type="date" name="to_date" value="{{ $toDate }}" class="input w-auto">
                    </x-ui.field>
                    <button class="btn btn-primary" type="submit">Run trial balance</button>
                </div>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel title="Account balances" class="mt-5" flush>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Account code</th>
                        <th>Account title</th>
                        <th class="num">Debit balance</th>
                        <th class="num">Credit balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td><span class="id">{{ $row['account']->code }}</span></td>
                            <td class="cell-strong">{{ $row['account']->name }}</td>
                            <td class="num">{{ $row['debit'] ? number_format($row['debit'], 2) : '—' }}</td>
                            <td class="num">{{ $row['credit'] ? number_format($row['credit'], 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="4" class="p-0">
                                <x-ui.empty-state icon="scale" title="No posted balances up to this date" description="Post vouchers, then run the trial balance again." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right font-semibold">Totals</td>
                        <td class="num font-semibold">{{ number_format($total_debit, 2) }}</td>
                        <td class="num font-semibold">{{ number_format($total_credit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-ui.panel>

    <x-ui.alert :tone="$balanced ? 'success' : 'danger'" class="mt-5">
        {{ $balanced ? 'Trial balance is balanced.' : 'Trial balance difference: ' . $currency . ' ' . number_format(abs($total_debit - $total_credit), 2) }}
    </x-ui.alert>
@endsection
