@extends('layouts.app')

@section('page_title', 'Batch '.$batch->manifest_no)

@section('content')
@php
    use App\Models\LimsSampleBatch;
    use App\Models\LimsSampleBatchItem;
    use App\Models\LimsTransitEvent;
@endphp
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => $batch->manifest_no,
        'subtitle' => ($batch->collectionCenter?->code ?? '—').' → '.($batch->destinationSite?->code ?? 'Main Lab'),
        'backUrl' => route('pathology.sample_batches.index'),
        'backLabel' => 'Back to transit',
    ])

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-3 rounded-xl mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-4">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3 mb-4">
        <span class="hms-badge {{ LimsSampleBatch::statusBadgeClass($batch->status) }}">
            {{ LimsSampleBatch::statusLabel($batch->status) }}
        </span>
        <span class="text-sm text-gray-500">{{ $batch->sample_count }} sample{{ $batch->sample_count === 1 ? '' : 's' }}</span>
        @if($batch->notes)
            <span class="text-sm text-gray-400">{{ $batch->notes }}</span>
        @endif
    </div>

    {{-- Chain of custody (prominent) --}}
    <div class="hms-panel hms-panel-padded mb-6 border-l-4 border-orange-400">
        <h2 class="font-semibold text-gray-900 mb-3">Chain of custody</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Samples --}}
            <div class="hms-panel hms-panel-flush">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Samples in batch</h2>
                </div>

                @if($batch->isOpen() && $canUpdate)
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <form method="POST" action="{{ route('pathology.sample_batches.items.store', $batch) }}" class="flex flex-wrap gap-2 items-end">
                            @csrf
                            <div class="flex-1 min-w-[10rem]">
                                <label class="block text-xs text-gray-500 mb-1" for="barcode">Scan / barcode</label>
                                <input type="text" id="barcode" name="barcode" class="hms-input text-sm" placeholder="Sample barcode" autofocus autocomplete="off">
                            </div>
                            @if($availableSamples->isNotEmpty())
                                <div class="flex-1 min-w-[12rem]">
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
                    <div class="p-6 text-sm text-gray-500">No samples yet. Add collected samples before dispatch.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left text-gray-600">
                                <tr>
                                    <th class="px-4 py-2 font-semibold">Barcode</th>
                                    <th class="px-4 py-2 font-semibold">Vial</th>
                                    <th class="px-4 py-2 font-semibold">Collected by</th>
                                    <th class="px-4 py-2 font-semibold">Sample status</th>
                                    <th class="px-4 py-2 font-semibold">Receive</th>
                                    @if($batch->isOpen() && $canUpdate)
                                        <th class="px-4 py-2 font-semibold text-right"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($batch->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $item->sample?->barcode ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $item->sample?->vial_type ?? '—' }}</td>
                                        <td class="px-4 py-2 text-gray-800">
                                            {{ $item->sample?->collected_by_name ?: '—' }}
                                            @if($item->sample?->collected_at)
                                                <div class="text-xs text-gray-400">{{ $item->sample->collected_at->timezone('Asia/Karachi')->format('Y-m-d H:i') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">{{ $item->sample?->status ?? '—' }}</td>
                                        <td class="px-4 py-2">
                                            <span class="hms-badge {{ LimsSampleBatchItem::receiveStatusBadgeClass($item->receive_status) }}">
                                                {{ LimsSampleBatchItem::receiveStatusLabel($item->receive_status) }}
                                            </span>
                                            @if($item->receive_marked_by_name)
                                                <div class="text-xs text-gray-600 mt-0.5">Marked by {{ $item->receive_marked_by_name }}</div>
                                            @endif
                                            @if($item->receive_note)
                                                <div class="text-xs text-gray-400 mt-0.5">{{ $item->receive_note }}</div>
                                            @endif
                                        </td>
                                        @if($batch->isOpen() && $canUpdate)
                                            <td class="px-4 py-2 text-right">
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
                <div class="hms-panel hms-panel-padded">
                    <h2 class="font-semibold text-gray-900 mb-1">Dispatch to Main Lab</h2>
                    <p class="text-sm text-gray-500 mb-3">
                        Sent by (you): <strong class="text-gray-800">{{ auth()->user()?->name ?: auth()->user()?->username }}</strong>
                        — enter who is taking the samples (courier / dispatcher).
                    </p>
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
            @endif

            @if(in_array($batch->status, [LimsSampleBatch::STATUS_DISPATCHED], true) && $canMarkInTransit)
                <div class="hms-panel hms-panel-padded">
                    <h2 class="font-semibold text-gray-900 mb-3">Mark in transit</h2>
                    <p class="text-sm text-gray-500 mb-3">
                        Logged as: <strong class="text-gray-800">{{ auth()->user()?->name ?: auth()->user()?->username }}</strong>
                    </p>
                    <form method="POST" action="{{ route('pathology.sample_batches.in_transit', $batch) }}" class="flex flex-wrap gap-2 items-end">
                        @csrf
                        <div class="flex-1 min-w-[12rem]">
                            <label class="block text-xs text-gray-500 mb-1" for="location_label">Location (optional)</label>
                            <input type="text" id="location_label" name="location_label" class="hms-input text-sm" value="{{ old('location_label') }}" placeholder="e.g. Courier pickup" maxlength="191">
                        </div>
                        <button type="submit" class="hms-btn hms-btn-secondary">Mark in transit</button>
                    </form>
                </div>
            @endif

            @if(in_array($batch->status, [LimsSampleBatch::STATUS_DISPATCHED, LimsSampleBatch::STATUS_IN_TRANSIT], true) && $canReceive)
                <div class="hms-panel hms-panel-padded">
                    <h2 class="font-semibold text-gray-900 mb-1">Receive at Main Lab</h2>
                    <p class="text-sm text-gray-500 mb-1">
                        Receiving as: <strong class="text-gray-800">{{ auth()->user()?->name ?: auth()->user()?->username }}</strong>
                        — stamped on the batch and each item you mark.
                    </p>
                    <p class="text-sm text-gray-500 mb-4">Set each sample to received, missing, or rejected. All items must be marked.</p>
                    <form method="POST" action="{{ route('pathology.sample_batches.receive', $batch) }}">
                        @csrf
                        <div class="overflow-x-auto mb-4">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-left text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 font-semibold">Barcode</th>
                                        <th class="px-3 py-2 font-semibold">Collected by</th>
                                        <th class="px-3 py-2 font-semibold">Outcome</th>
                                        <th class="px-3 py-2 font-semibold">Note</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($batch->items as $idx => $item)
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-xs">
                                                {{ $item->sample?->barcode ?? '#'.$item->sample_id }}
                                                <input type="hidden" name="items[{{ $idx }}][sample_id]" value="{{ $item->sample_id }}">
                                            </td>
                                            <td class="px-3 py-2 text-gray-700">{{ $item->sample?->collected_by_name ?: '—' }}</td>
                                            <td class="px-3 py-2">
                                                <select name="items[{{ $idx }}][receive_status]" class="hms-input text-sm" required>
                                                    <option value="received" @selected(old("items.$idx.receive_status", 'received') === 'received')>Received</option>
                                                    <option value="missing" @selected(old("items.$idx.receive_status") === 'missing')>Missing</option>
                                                    <option value="rejected" @selected(old("items.$idx.receive_status") === 'rejected')>Rejected</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="text" name="items[{{ $idx }}][receive_note]" class="hms-input text-sm" value="{{ old("items.$idx.receive_note") }}" placeholder="Required for reject/missing" maxlength="500">
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
            @endif
        </div>

        {{-- Timeline --}}
        <div class="hms-panel hms-panel-flush">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Event timeline</h2>
            </div>
            <div class="p-4">
                @if($batch->events->isEmpty())
                    <p class="text-sm text-gray-500">No events yet.</p>
                @else
                    <ol class="relative border-l border-gray-200 ml-2 space-y-4">
                        @foreach($batch->events as $event)
                            <li class="ml-4">
                                <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-gray-300 ring-2 ring-white"></span>
                                <div class="text-sm font-medium text-gray-900">{{ LimsTransitEvent::typeLabel($event->event_type) }}</div>
                                <div class="text-xs text-gray-500">
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
                                    <div class="text-xs text-gray-600 mt-0.5">
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
@endsection
