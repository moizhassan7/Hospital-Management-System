@extends('layouts.app')

@section('page_title', 'Sample Transit')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Sample Transit',
        'subtitle' => 'Manifests from collection centers to Main Lab',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back to Pathology',
    ])

    <div class="hms-page-toolbar flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('pathology.sample_batches.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="status">Status</label>
                <select id="status" name="status" class="hms-input text-sm">
                    <option value="">All</option>
                    @foreach(['open','dispatched','in_transit','received','closed','cancelled'] as $st)
                        <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ \App\Models\LimsSampleBatch::statusLabel($st) }}</option>
                    @endforeach
                </select>
            </div>
            @if($centers)
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="collection_center_id">Collection center</label>
                    <select id="collection_center_id" name="collection_center_id" class="hms-input text-sm">
                        <option value="">All</option>
                        @foreach($centers as $cc)
                            <option value="{{ $cc->id }}" @selected((string) ($filters['collection_center_id'] ?? '') === (string) $cc->id)>{{ $cc->code }} — {{ $cc->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" class="hms-input text-sm" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" class="hms-input text-sm" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <button type="submit" class="hms-btn hms-btn-secondary text-sm">Filter</button>
        </form>

        @if($canCreate)
            <a href="{{ route('pathology.sample_batches.create') }}" class="hms-btn hms-btn-primary">New batch</a>
        @endif
    </div>

    <div class="hms-panel hms-panel-flush">
        @if($batches->isEmpty())
            <div class="hms-re-empty p-8">
                <p class="hms-empty-title">No sample batches yet</p>
                <p class="hms-empty-desc">Create an open manifest, add collected samples, then dispatch to Main Lab.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Manifest</th>
                            <th class="px-4 py-3 font-semibold">Origin</th>
                            <th class="px-4 py-3 font-semibold">Destination</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Samples</th>
                            <th class="px-4 py-3 font-semibold">Custody</th>
                            <th class="px-4 py-3 font-semibold">Created</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($batches as $batch)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">{{ $batch->manifest_no }}</td>
                                <td class="px-4 py-3">{{ $batch->collectionCenter?->code ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $batch->destinationSite?->code ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="hms-badge {{ \App\Models\LimsSampleBatch::statusBadgeClass($batch->status) }}">
                                        {{ \App\Models\LimsSampleBatch::statusLabel($batch->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $batch->items_count }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    @if($batch->courier_name || $batch->dispatched_by_name || $batch->received_by_name)
                                        @if($batch->dispatched_by_name)
                                            <div>Sent: {{ $batch->dispatched_by_name }}</div>
                                        @endif
                                        @if($batch->courier_name)
                                            <div>Took: {{ $batch->courier_name }}</div>
                                        @endif
                                        @if($batch->received_by_name)
                                            <div>Recv: {{ $batch->received_by_name }}</div>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $batch->created_at?->timezone('Asia/Karachi')->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('pathology.sample_batches.show', $batch) }}" class="hms-btn hms-btn-secondary text-xs py-1 px-2">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
