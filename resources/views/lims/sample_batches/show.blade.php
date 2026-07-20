@extends('layouts.app')

@section('page_title', 'Batch '.$batch->manifest_no)

@section('content')
@php
    use App\Models\LimsSampleBatch;
    use App\Models\LimsSampleBatchItem;
    use App\Models\LimsTransitEvent;
@endphp
{{-- Flash: only via page-shell-start -> flash-alerts. No local session('success') here. --}}
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => $batch->manifest_no,
        'subtitle' => ($batch->collectionCenter?->code ?? '—').' → '.($batch->destinationSite?->code ?? 'Main Lab'),
        'backUrl' => route('pathology.sample_batches.index'),
        'backLabel' => 'Back to transit',
    ])

    <div class="flex flex-wrap items-center gap-3 mb-5">
        <span class="hms-badge {{ LimsSampleBatch::statusBadgeClass($batch->status) }}">
            {{ LimsSampleBatch::statusLabel($batch->status) }}
        </span>
        <span class="text-sm text-gray-500">{{ $batch->sample_count }} sample{{ $batch->sample_count === 1 ? '' : 's' }}</span>
        @if($batch->notes)
            <span class="text-sm text-gray-400 truncate max-w-xl">{{ $batch->notes }}</span>
        @endif
    </div>

    {{-- Chain of custody --}}
    <div class="hms-panel mb-5" style="border-left: 4px solid #fb923c;">
        <div class="hms-panel-header">
            <h2 class="hms-panel-title">Chain of custody</h2>
        </div>
        <div class="hms-panel-body">
            <div class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-3" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr)); gap: 1.25rem;">
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Who sent / dispatched</div>
                    <div class="font-medium text-gray-900">
                        {{ $batch->dispatched_by_name ?: ($batch->dispatched_at ? '—' : 'Not dispatched yet') }}
                    </div>
                    @if($batch->dispatched_at)
                        <div class="text-xs text-gray-500 mt-0.5">{{ $batch->dispatched_at->timezone('Asia/Karachi')->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Who took (courier)</div>
                    <div class="font-medium text-gray-900">
                        {{ $batch->courier_name ?: ($batch->dispatched_at ? '—' : 'Not set') }}
                    </div>
                    @if($batch->courier_ref)
                        <div class="text-xs text-gray-500 mt-0.5">Ref: {{ $batch->courier_ref }}</div>
                    @endif
                </div>
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Who received (Main Lab)</div>
                    <div class="font-medium text-gray-900">
                        {{ $batch->received_by_name ?: ($batch->received_at ? '—' : 'Not received yet') }}
                    </div>
                    @if($batch->received_at)
                        <div class="text-xs text-gray-500 mt-0.5">{{ $batch->received_at->timezone('Asia/Karachi')->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="hms-batch-show-grid">
        <div class="hms-batch-show-main">
            {{-- Samples --}}
            <div class="hms-panel hms-panel-flush">
                <div class="hms-panel-header">
                    <div>
                        <h2 class="hms-panel-title">Samples in batch</h2>
                        <p class="hms-panel-subtitle">{{ $batch->items->count() }} listed on this manifest</p>
                    </div>
                </div>

                @if($batch->isOpen() && $canUpdate)
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                        <form method="POST" action="{{ route('pathology.sample_batches.items.store', $batch) }}" class="flex flex-wrap gap-3 items-end">
                            @csrf
                            <div class="flex-1" style="min-width: 12rem;">
                                <label class="block text-xs text-gray-500 mb-1" for="barcode">Scan / barcode</label>
                                <input type="text" id="barcode" name="barcode" class="hms-input text-sm" placeholder="Sample barcode" autofocus autocomplete="off">
                            </div>
                            @if($availableSamples->isNotEmpty())
                                <div class="flex-1" style="min-width: 14rem;">
                                    <label class="block text-xs text-gray-500 mb-1" for="sample_id">Or pick collected</label>
                                    <select id="sample_id" name="sample_id" class="hms-input text-sm">
                                        <option value="">Select…</option>
                                        @foreach($availableSamples as $sample)
                                            <option value="{{ $sample->id }}">
                                                {{ $sample->barcode }} ({{ $sample->vial_type }})
                                                @if($sample->collected_by_name)
                                                    — {{ $sample->collected_by_name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <button type="submit" class="hms-btn hms-btn-primary text-sm">Add</button>
                        </form>
                    </div>
                @endif

                @if($batch->items->isEmpty())
                    <div class="p-8 text-sm text-gray-500">No samples yet. Add collected samples before dispatch.</div>
                @else
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Barcode</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Vial</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Collected by</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sample status</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Receive</th>
                                    @if($batch->isOpen() && $canUpdate)
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($batch->items as $item)
                                    <tr class="hover:bg-blue-50/40">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-900 whitespace-nowrap">
                                            {{ $item->sample?->barcode ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                            {{ $item->sample?->vial_type ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700" style="white-space: normal; vertical-align: top;">
                                            <div class="font-medium text-gray-900">
                                                {{ $item->sample?->collected_by_name ?: '—' }}
                                            </div>
                                            @if($item->sample?->collected_at)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    {{ $item->sample->collected_at->timezone('Asia/Karachi')->format('Y-m-d H:i') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                            {{ $item->sample?->status ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3" style="white-space: normal; vertical-align: top;">
                                            <span class="hms-badge {{ LimsSampleBatchItem::receiveStatusBadgeClass($item->receive_status) }}">
                                                {{ LimsSampleBatchItem::receiveStatusLabel($item->receive_status) }}
                                            </span>
                                            @if($item->receive_marked_by_name)
                                                <div class="text-xs text-gray-500 mt-1">Marked by {{ $item->receive_marked_by_name }}</div>
                                            @endif
                                            @if($item->receive_note)
                                                <div class="text-xs text-gray-400 mt-0.5">{{ $item->receive_note }}</div>
                                            @endif
                                        </td>
                                        @if($batch->isOpen() && $canUpdate)
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                <form method="POST" action="{{ route('pathology.sample_batches.items.destroy', [$batch, $item->sample_id]) }}" onsubmit="return confirm('Remove this sample from the batch?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            @if($batch->isOpen() && $canDispatch && $batch->items->isNotEmpty())
                <div class="hms-panel">
                    <div class="hms-panel-header">
                        <div>
                            <h2 class="hms-panel-title">Dispatch to Main Lab</h2>
                            <p class="hms-panel-subtitle">
                                Sent by (you): {{ auth()->user()?->name ?: auth()->user()?->username }}
                                — enter who is taking the samples (courier / dispatcher).
                            </p>
                        </div>
                    </div>
                    <div class="hms-panel-body">
                        <form method="POST" action="{{ route('pathology.sample_batches.dispatch', $batch) }}" class="space-y-3 max-w-md">
                            @csrf
                            <x-form.field label="Who took (courier name)" for="courier_name" :required="true">
                                <input type="text" id="courier_name" name="courier_name" class="hms-input" value="{{ old('courier_name') }}" maxlength="191" required placeholder="e.g. City Runner / Ali Khan">
                            </x-form.field>
                            <x-form.field label="Courier reference (optional)" for="courier_ref">
                                <input type="text" id="courier_ref" name="courier_ref" class="hms-input" value="{{ old('courier_ref') }}" maxlength="191" placeholder="Trip / bag / AWB ref">
                            </x-form.field>
                            <button type="submit" class="hms-btn hms-btn-primary" onclick="return confirm('Dispatch this batch? Samples will be sealed as dispatched.')">
                                Dispatch batch
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if(in_array($batch->status, [LimsSampleBatch::STATUS_DISPATCHED], true) && $canMarkInTransit)
                <div class="hms-panel">
                    <div class="hms-panel-header">
                        <div>
                            <h2 class="hms-panel-title">Mark in transit</h2>
                            <p class="hms-panel-subtitle">
                                Logged as: {{ auth()->user()?->name ?: auth()->user()?->username }}
                            </p>
                        </div>
                    </div>
                    <div class="hms-panel-body">
                        <form method="POST" action="{{ route('pathology.sample_batches.in_transit', $batch) }}" class="flex flex-wrap gap-3 items-end">
                            @csrf
                            <div class="flex-1" style="min-width: 12rem;">
                                <label class="block text-xs text-gray-500 mb-1" for="location_label">Location (optional)</label>
                                <input type="text" id="location_label" name="location_label" class="hms-input text-sm" value="{{ old('location_label') }}" placeholder="e.g. Courier pickup" maxlength="191">
                            </div>
                            <button type="submit" class="hms-btn hms-btn-secondary">Mark in transit</button>
                        </form>
                    </div>
                </div>
            @endif

            @if(in_array($batch->status, [LimsSampleBatch::STATUS_DISPATCHED, LimsSampleBatch::STATUS_IN_TRANSIT], true) && $canReceive)
                <div class="hms-panel hms-panel-flush">
                    <div class="hms-panel-header">
                        <div>
                            <h2 class="hms-panel-title">Receive at Main Lab</h2>
                            <p class="hms-panel-subtitle">
                                Receiving as {{ auth()->user()?->name ?: auth()->user()?->username }}.
                                Set each sample to received, missing, or rejected.
                            </p>
                        </div>
                    </div>
                    <div class="hms-panel-body" style="padding-top: 1rem;">
                        <form method="POST" action="{{ route('pathology.sample_batches.receive', $batch) }}">
                            @csrf
                            <div class="overflow-x-auto w-full mb-4">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Barcode</th>
                                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Collected by</th>
                                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Outcome</th>
                                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Note</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($batch->items as $idx => $item)
                                            <tr>
                                                <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">
                                                    {{ $item->sample?->barcode ?? '#'.$item->sample_id }}
                                                    <input type="hidden" name="items[{{ $idx }}][sample_id]" value="{{ $item->sample_id }}">
                                                </td>
                                                <td class="px-4 py-3 text-gray-700" style="white-space: normal;">
                                                    {{ $item->sample?->collected_by_name ?: '—' }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    <select name="items[{{ $idx }}][receive_status]" class="hms-input text-sm" style="min-width: 8rem;" required>
                                                        <option value="received" @selected(old("items.$idx.receive_status", 'received') === 'received')>Received</option>
                                                        <option value="missing" @selected(old("items.$idx.receive_status") === 'missing')>Missing</option>
                                                        <option value="rejected" @selected(old("items.$idx.receive_status") === 'rejected')>Rejected</option>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="text" name="items[{{ $idx }}][receive_note]" class="hms-input text-sm" style="min-width: 10rem;" value="{{ old("items.$idx.receive_note") }}" placeholder="Required for reject/missing" maxlength="500">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="hms-btn hms-btn-primary" onclick="return confirm('Confirm receive for all items in this batch?')">
                                Confirm receive
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Timeline: content-sized (items-start on grid); do not stretch to left column height --}}
        <div class="hms-batch-show-aside">
            <div class="hms-panel hms-panel-flush">
                <div class="hms-panel-header">
                    <h2 class="hms-panel-title">Event timeline</h2>
                </div>
                <div class="p-5">
                    @if($batch->events->isEmpty())
                        <p class="text-sm text-gray-500">No events yet.</p>
                    @else
                        <ol class="relative border-l border-gray-200 space-y-5" style="margin-left: 0.375rem;">
                            @foreach($batch->events as $event)
                                <li style="margin-left: 1rem;">
                                    <span class="absolute bg-gray-300 ring-2 ring-white" style="left: -0.375rem; margin-top: 0.375rem; height: 0.75rem; width: 0.75rem; border-radius: 9999px;"></span>
                                    <div class="text-sm font-medium text-gray-900">{{ LimsTransitEvent::typeLabel($event->event_type) }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                        {{ $event->occurred_at?->timezone('Asia/Karachi')->format('Y-m-d H:i:s') }}
                                        @if($event->actor_name)
                                            · <span class="text-gray-700 font-medium">{{ $event->actor_name }}</span>
                                        @endif
                                        @if($event->location_label)
                                            · {{ $event->location_label }}
                                        @endif
                                    </div>
                                    @php $payload = is_array($event->payload) ? $event->payload : []; @endphp
                                    @if(($payload['courier_name'] ?? null) || ($payload['dispatched_by_name'] ?? null))
                                        <div class="text-xs text-gray-600 mt-1 leading-relaxed">
                                            @if(!empty($payload['dispatched_by_name']))
                                                Sent by {{ $payload['dispatched_by_name'] }}
                                            @endif
                                            @if(!empty($payload['courier_name']))
                                                · Courier {{ $payload['courier_name'] }}
                                                @if(!empty($payload['courier_ref']))
                                                    ({{ $payload['courier_ref'] }})
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
