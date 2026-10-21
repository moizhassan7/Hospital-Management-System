@extends('layouts.app')

@section('title', $voucher->voucher_no)

@section('content')
    @php
        $meta = $voucher->metadata ?? [];
        $statusTone = match($voucher->status) {
            'posted' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'neutral',
        };
        $currency = get_setting('currency_symbol', 'Rs');
    @endphp

    <x-ui.page-header :title="$voucher->accounting_type" :subtitle="$voucher->voucher_no . ' · ' . $voucher->accounting_type_code">
        <x-slot:actions>
            <a href="{{ route('finance.vouchers.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Voucher register
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <x-ui.icon name="printer" />Print voucher
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <article class="voucher-sheet panel overflow-hidden">
        <header class="flex flex-col gap-5 border-b border-dashed border-line px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                @if(get_setting('logo'))
                    <img src="{{ asset('storage/'.get_setting('logo')) }}" alt="Hospital logo" class="h-14 w-14 rounded-lg border border-line object-contain p-1">
                @endif
                <div>
                    <p class="font-display text-xl font-extrabold tracking-tight">{{ get_setting('hospital_name', 'Hospital Management System') }}</p>
                    <p class="mt-1 max-w-md text-[12.5px] text-fg-muted">{{ get_setting('address', '') }}</p>
                </div>
            </div>
            <div class="sm:text-right">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Accounting document</p>
                <p class="mt-1 font-display text-2xl font-extrabold">{{ $voucher->accounting_type }}</p>
                <p class="id">{{ $voucher->accounting_type_code }}</p>
                <div class="mt-2"><x-ui.badge :tone="$statusTone">{{ ucfirst($voucher->status) }}</x-ui.badge></div>
            </div>
        </header>

        <div class="grid md:grid-cols-[1fr_17rem]">
            <div class="px-6 py-5">
                <div class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Voucher number</p>
                        <p class="mt-1 id">{{ $voucher->voucher_no }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Voucher date</p>
                        <p class="mt-1 text-sm font-semibold">{{ $voucher->voucher_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Category</p>
                        <p class="mt-1 text-sm font-semibold">{{ $voucher->category->name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Settlement account</p>
                        <p class="mt-1 text-sm font-semibold">{{ $voucher->paymentAccount->name ?? ucfirst($voucher->payment_method) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">{{ $voucher->voucher_type === 'credit' ? 'Received from' : 'Paid to' }}</p>
                        <p class="mt-1 text-sm font-semibold">{{ $voucher->party_name ?: '—' }}</p>
                        @if(!empty($meta['mr_number']))
                            <p class="mt-1 text-[12.5px] text-fg-muted">MR {{ $meta['mr_number'] }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Reference</p>
                        <p class="mt-1 text-sm font-semibold">{{ $voucher->reference_no ?: '—' }}</p>
                    </div>
                </div>

                @if($voucher->remarks)
                    <div class="mt-5 rounded-lg bg-subtle p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Remarks</p>
                        <p class="mt-1 text-sm leading-6">{{ $voucher->remarks }}</p>
                    </div>
                @endif

                @if($voucher->category->code === 'doctor-payment' && isset($meta['total_share']))
                    <div class="mt-5 grid grid-cols-3 gap-2">
                        <div class="rounded-lg bg-subtle p-3">
                            <p class="text-[10px] font-semibold uppercase text-fg-muted">Total share</p>
                            <p class="mt-1 text-sm font-bold">{{ number_format($meta['total_share'], 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-subtle p-3">
                            <p class="text-[10px] font-semibold uppercase text-fg-muted">Previously paid</p>
                            <p class="mt-1 text-sm font-bold">{{ number_format($meta['previously_paid'], 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-subtle p-3">
                            <p class="text-[10px] font-semibold uppercase text-fg-muted">Balance</p>
                            <p class="mt-1 text-sm font-bold">{{ number_format($meta['balance'], 2) }}</p>
                        </div>
                    </div>
                @endif

                @if($voucher->isCashHandover())
                    <div class="mt-5 rounded-lg border border-line bg-subtle p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Cash custody handover</p>
                        <p class="mt-1 text-sm">Collected: <strong>{{ number_format($meta['total_collected'] ?? 0, 2) }}</strong> · Previously handed over: <strong>{{ number_format($meta['previously_handed_over'] ?? 0, 2) }}</strong></p>
                        @if(isset($meta['received_amount']))
                            <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                <div class="rounded-lg border border-line bg-surface p-3">Received <strong class="float-right">{{ number_format($meta['received_amount'], 2) }}</strong></div>
                                <div class="rounded-lg border border-line bg-surface p-3">Difference <strong class="float-right">{{ number_format($meta['difference'] ?? 0, 2) }}</strong></div>
                            </div>
                        @endif
                        @if(!empty($meta['receiver_remarks']))
                            <p class="mt-3 text-[12.5px]">Receiver note: {{ $meta['receiver_remarks'] }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <aside class="border-t border-line bg-subtle px-6 py-5 md:border-l md:border-t-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-fg-muted">Voucher amount</p>
                <p class="mt-2 font-display text-2xl font-extrabold tabular-nums tracking-tight">{{ $currency }} {{ number_format($voucher->effective_amount, 2) }}</p>
                @if($voucher->actual_amount !== null && (float)$voucher->actual_amount !== (float)$voucher->amount)
                    <p class="mt-1 text-[12.5px] text-fg-muted">Expected {{ number_format((float)$voucher->amount, 2) }}</p>
                @endif
                <div class="mt-6 space-y-3 border-t border-line pt-5 text-[12.5px]">
                    <div>
                        <span class="text-fg-muted">Prepared by</span>
                        <p class="mt-1 font-semibold">{{ $voucher->createdBy->name ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-fg-muted">Posted by</span>
                        <p class="mt-1 font-semibold">{{ $voucher->postedBy->name ?? ($voucher->status === 'pending' ? 'Awaiting receipt' : '—') }}</p>
                    </div>
                    @if($voucher->posted_at)
                        <div>
                            <span class="text-fg-muted">Posted at</span>
                            <p class="mt-1 font-semibold">{{ $voucher->posted_at->format('d M Y, h:i A') }}</p>
                        </div>
                    @endif
                </div>
            </aside>
        </div>

        <section class="border-t border-dashed border-line px-6 py-5">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold">Journal posting</h3>
                    <p class="text-[12.5px] text-fg-muted">Original posting and any subsequent reversal lines</p>
                </div>
                @if(in_array($voucher->status, ['posted','void']))
                    <x-ui.badge tone="success">Balanced</x-ui.badge>
                @endif
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="num">Debit</th>
                            <th class="num">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($voucher->entries as $entry)
                            <tr>
                                <td>
                                    <span class="id">{{ $entry->account->code }}</span>
                                    <span class="cell-strong">{{ $entry->account->name }}</span>
                                    @if($entry->is_reversal)
                                        <x-ui.badge tone="warning">Reversal</x-ui.badge>
                                    @endif
                                </td>
                                <td class="num">{{ $entry->entry_type === 'debit' ? number_format((float)$entry->amount, 2) : '—' }}</td>
                                <td class="num">{{ $entry->entry_type === 'credit' ? number_format((float)$entry->amount, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="muted">Journal lines will be created when this voucher is posted.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="grid grid-cols-2 gap-12 border-t border-line px-6 py-10 text-center text-[12px] text-fg-muted">
            <div class="border-t border-ink-400 pt-2">Prepared by</div>
            <div class="border-t border-ink-400 pt-2">Authorized / received by</div>
        </footer>
    </article>

    @if($voucher->isCashHandover() && $voucher->status === 'pending' && $canReceive)
        <div class="no-print mt-5 grid gap-5 md:grid-cols-2">
            <x-ui.panel title="Confirm physical cash receipt" icon="check-circle" flush>
                <form method="POST" action="{{ route('finance.vouchers.receive', $voucher) }}">
                    @csrf
                    <div class="panel-body space-y-3">
                        <x-ui.field label="Received amount" required>
                            <input type="number" name="received_amount" step="0.01" min="0.01" value="{{ $voucher->amount }}" required class="input input-num">
                        </x-ui.field>
                        <x-ui.field label="Receiver remarks">
                            <textarea name="receiver_remarks" rows="2" placeholder="Receiver note (optional)" class="input"></textarea>
                        </x-ui.field>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary" type="submit">Receive and post</button>
                    </div>
                </form>
            </x-ui.panel>
            <x-ui.panel title="Reject Contra Voucher" icon="ban" flush>
                <form method="POST" action="{{ route('finance.vouchers.reject', $voucher) }}">
                    @csrf
                    <div class="panel-body">
                        <x-ui.field label="Reason">
                            <textarea name="receiver_remarks" rows="2" placeholder="Reason for rejection" class="input"></textarea>
                        </x-ui.field>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-danger" data-confirm="Reject this Contra Voucher?" data-confirm-title="Reject voucher?" data-confirm-label="Reject">Reject voucher</button>
                    </div>
                </form>
            </x-ui.panel>
        </div>
    @endif

    @if($voucher->status === 'posted' && $canVoid)
        <x-ui.panel title="Reverse and void this voucher" class="no-print mt-5" flush>
            <form method="POST" action="{{ route('finance.vouchers.void', $voucher) }}">
                @csrf
                <div class="panel-body">
                    <x-ui.field label="Reversal reason" required>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input name="void_reason" required minlength="5" placeholder="Required reversal reason" class="input flex-1">
                            <button type="submit" class="btn btn-danger" data-confirm="Create a balanced Reversal Voucher and mark this document void?" data-confirm-title="Void this voucher?" data-confirm-label="Post reversal">Post reversal</button>
                        </div>
                    </x-ui.field>
                </div>
            </form>
        </x-ui.panel>
    @endif

    @if($voucher->status === 'void')
        <x-ui.alert tone="warning" class="no-print mt-5">
            <strong>Voided:</strong> {{ $voucher->void_reason }} · {{ $voucher->voidedBy->name ?? 'User' }} on {{ optional($voucher->voided_at)->format('d M Y, h:i A') }}
        </x-ui.alert>
    @endif
@endsection
