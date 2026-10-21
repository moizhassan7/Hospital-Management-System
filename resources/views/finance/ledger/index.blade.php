@extends('layouts.app')

@section('title', 'Account ledger')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Account ledger" :subtitle="'Posted Debit and Credit journal lines · ' . $scopeLabel">
        <x-slot:actions>
            <a href="{{ route('finance.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Finance
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary">
                <x-ui.icon name="printer" />Print ledger
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Filters" class="no-print" flush>
        <form method="GET" action="{{ route('finance.ledger.index') }}">
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="From date" class="md:col-span-3">
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="input">
                    </x-ui.field>
                    <x-ui.field label="To date" class="md:col-span-3">
                        <input type="date" name="to_date" value="{{ $toDate }}" class="input">
                    </x-ui.field>
                    @unless($selfOnly)
                        <x-ui.field label="Posting account" class="md:col-span-3">
                            <select name="account_id" class="select">
                                <option value="">All posting accounts</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" @selected($accountId == $account->id)>{{ $account->code }} · {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Staff cash custodian" class="md:col-span-3">
                            <select name="user_id" class="select">
                                <option value="">All staff</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }} ({{ $u->username }})</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Doctor party" class="md:col-span-3">
                            <select name="doctor_id" class="select">
                                <option value="">All doctors</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected($doctorId == $doctor->id)>{{ $doctor->name }} ({{ $doctor->code }})</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    @endunless
                    <x-ui.field label="Journal side" class="md:col-span-3">
                        <select name="entry_type" class="select">
                            <option value="">Debit + Credit</option>
                            <option value="debit" @selected($entryType === 'debit')>Debit (Dr)</option>
                            <option value="credit" @selected($entryType === 'credit')>Credit (Cr)</option>
                        </select>
                    </x-ui.field>
                    <div class="md:col-span-3 flex items-end gap-2">
                        <button class="btn btn-primary" type="submit">Apply</button>
                        <a href="{{ route('finance.ledger.index') }}" class="btn btn-ghost">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </x-ui.panel>

    <section class="mt-5 metric-strip">
        @if($selectedAccount)
            <x-ui.metric label="Opening balance" icon="book-open" :prefix="$currency" :value="number_format(abs($opening_balance), 2)" :hint="$opening_balance < 0 ? 'Cr' : 'Dr'" />
        @endif
        <x-ui.metric label="Debit (Dr)" icon="arrow-right" :prefix="$currency" :value="number_format($total_debit, 2)" />
        <x-ui.metric label="Credit (Cr)" icon="arrow-left" :prefix="$currency" :value="number_format($total_credit, 2)" />
        @if($selectedAccount)
            <x-ui.metric label="Closing balance" icon="scale" :prefix="$currency" :value="number_format(abs($closing_balance), 2)" :hint="$closing_balance < 0 ? ($selectedAccount->normal_balance === 'debit' ? 'Cr' : 'Dr') : ucfirst($selectedAccount->normal_balance)" />
        @else
            <x-ui.metric label="Balance check" icon="scale" :value="number_format(abs($total_debit-$total_credit), 2)" :hint="abs($total_debit-$total_credit) < .01 ? 'Balanced' : 'Difference'" />
        @endif
    </section>

    <x-ui.panel title="Journal lines" class="mt-5" flush>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date / document</th>
                        <th>Account</th>
                        <th>Particulars</th>
                        <th>Reference</th>
                        <th class="num">Debit</th>
                        <th class="num">Credit</th>
                        @if($selectedAccount)
                            <th class="num">Balance</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td>
                                <span class="cell-strong">{{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}</span>
                                <span class="cell-sub">{{ $entry['document_type'] }}@if($entry['is_reversal']) · Reversal@endif</span>
                            </td>
                            <td>
                                <span class="id">{{ $entry['account_code'] }}</span>
                                <span class="cell-sub">{{ $entry['account_name'] }}</span>
                            </td>
                            <td>
                                <span class="cell-strong">{{ $entry['module'] }}</span>
                                <span class="cell-sub">{{ $entry['description'] }}</span>
                                @if($entry['person'] !== '—')
                                    <span class="cell-sub">Party: {{ $entry['person'] }}</span>
                                @endif
                            </td>
                            <td class="id">{{ $entry['reference'] }}</td>
                            <td class="num">{{ $entry['debit'] ? number_format($entry['debit'], 2) : '—' }}</td>
                            <td class="num">{{ $entry['credit'] ? number_format($entry['credit'], 2) : '—' }}</td>
                            @if($selectedAccount)
                                <td class="num">{{ number_format(abs($entry['running_balance']), 2) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="{{ $selectedAccount ? 7 : 6 }}" class="p-0">
                                <x-ui.empty-state icon="book-open" title="No posted journal lines found" description="Change filters or post a receipt or payment voucher." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($entries))
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right font-semibold">Period totals</td>
                            <td class="num font-semibold">{{ number_format($total_debit, 2) }}</td>
                            <td class="num font-semibold">{{ number_format($total_credit, 2) }}</td>
                            @if($selectedAccount)<td></td>@endif
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-ui.panel>
@endsection
