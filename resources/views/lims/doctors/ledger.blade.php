@extends('layouts.app')

@section('page_title', 'Doctor Ledger')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Ledger — '.$doctor->name,
        'subtitle' => 'Balance, entries, and payouts',
        'backUrl' => route('pathology.lims_doctors.index'),
        'backLabel' => 'Back to doctors',
    ])

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-2 mb-5">
        <div class="hms-panel hms-panel-padded">
            <p class="text-sm text-gray-500 mb-1">Current balance</p>
            <p class="text-3xl font-bold text-gray-900">{{ number_format((float) ($ledger?->balance ?? 0), 2) }}</p>
            @if((float) ($ledger?->balance ?? 0) < 0)
                <p class="text-xs text-amber-700 mt-2">Negative balance can occur after clawback post-payout.</p>
            @endif
        </div>

        @if($canPayout)
            <div class="hms-panel hms-panel-padded">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Record payout</h2>
                <p class="text-xs text-gray-500 mb-4">Debits the ledger only — does not recalculate commissions.</p>
                <form method="POST" action="{{ route('pathology.lims_doctors.payout', $doctor) }}" class="space-y-4">
                    @csrf
                    <x-form.field label="Amount" for="amount" :required="true">
                        <input type="number" id="amount" name="amount" class="hms-input" value="{{ old('amount') }}"
                            step="0.01" min="0.01" required>
                    </x-form.field>
                    <x-form.field label="Method" for="method">
                        <select id="method" name="method" class="hms-input">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}" @selected(old('method', 'cash') === $method)>{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>
                    </x-form.field>
                    <x-form.field label="Notes" for="notes">
                        <textarea id="notes" name="notes" class="hms-input" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
                    </x-form.field>
                    <div class="hms-form-actions">
                        <button type="submit" class="hms-btn hms-btn-primary">Record payout</button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <div class="hms-panel hms-panel-flush">
        @if($entries->isEmpty())
            <div class="hms-re-empty p-8">
                <p class="hms-empty-title">No ledger entries</p>
                <p class="hms-empty-desc">Credits appear when bookings create commission snapshots.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">When</th>
                            <th class="px-4 py-3 font-semibold">Type</th>
                            <th class="px-4 py-3 font-semibold">Amount</th>
                            <th class="px-4 py-3 font-semibold">Ref</th>
                            <th class="px-4 py-3 font-semibold">Idempotency</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($entries as $entry)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ optional($entry->occurred_at)->timezone('Asia/Karachi')?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $entry->entry_type }}</td>
                                <td class="px-4 py-3 font-medium">{{ number_format((float) $entry->amount, 2) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $entry->ref_type }} #{{ $entry->ref_id }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-400">{{ $entry->idempotency_key }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
