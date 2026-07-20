@extends('layouts.app')

@section('page_title', 'Doctor Ledger')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Ledger — '.$doctor->name,
        'subtitle' => 'Read-only balance and recent entries',
        'backUrl' => route('pathology.lims_doctors.index'),
        'backLabel' => 'Back to doctors',
    ])

    <div class="hms-panel hms-panel-padded mb-5 max-w-md">
        <p class="text-sm text-gray-500 mb-1">Current balance</p>
        <p class="text-3xl font-bold text-gray-900">{{ number_format((float) ($ledger?->balance ?? 0), 2) }}</p>
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
