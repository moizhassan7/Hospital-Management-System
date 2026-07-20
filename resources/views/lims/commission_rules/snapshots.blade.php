@extends('layouts.app')

@section('page_title', 'Commission Snapshots')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Commission Snapshots',
        'subtitle' => 'Immutable amounts frozen at booking time (read-only)',
        'backUrl' => route('pathology.commission_rules.index'),
        'backLabel' => 'Back to rules',
    ])

    <div class="hms-panel hms-panel-flush">
        @if($snapshots->isEmpty())
            <div class="hms-re-empty p-8">
                <p class="hms-empty-title">No snapshots yet</p>
                <p class="hms-empty-desc">Snapshots are created when referred bookings sync to LIMS.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">When</th>
                            <th class="px-4 py-3 font-semibold">Doctor</th>
                            <th class="px-4 py-3 font-semibold">CC</th>
                            <th class="px-4 py-3 font-semibold">Basis</th>
                            <th class="px-4 py-3 font-semibold">Base</th>
                            <th class="px-4 py-3 font-semibold">Commission</th>
                            <th class="px-4 py-3 font-semibold">Booking item</th>
                            <th class="px-4 py-3 font-semibold">Clawed back</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($snapshots as $snap)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ optional($snap->snapshotted_at)->timezone('Asia/Karachi')?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $snap->doctor?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $snap->collectionCenter?->code ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $snap->rule_basis }}</td>
                                <td class="px-4 py-3">{{ number_format((float) $snap->base_amount, 2) }}</td>
                                <td class="px-4 py-3 font-medium">{{ number_format((float) $snap->commission_amount, 2) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">#{{ $snap->booking_item_id }}</td>
                                <td class="px-4 py-3">{{ $snap->is_clawed_back ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
