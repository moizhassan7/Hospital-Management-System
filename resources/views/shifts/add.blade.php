@extends('layouts.app')

@section('title', isset($shift) ? 'Edit shift' : 'Shifts')

@section('content')
    <x-ui.page-header :title="isset($shift) ? 'Edit shift' : 'Shifts'" subtitle="Working shifts for hospital staff and their hours.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[380px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($shift) ? 'Edit ' . $shift->description : 'Add shift'" :description="isset($shift) ? 'Changes apply immediately after saving.' : 'For example Morning, 08:00 to 14:00.'" :icon="isset($shift) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($shift) ? route('shifts.update', $shift->id) : route('shifts.store') }}" method="POST">
                @csrf
                @isset($shift)
                    @method('PUT')
                @endisset
                <div class="panel-body">
                    <div class="form-grid">
                        <x-ui.field label="Description" for="description" error="description" required class="col-span-8">
                            <input type="text" id="description" name="description" @class(['input', 'is-invalid' => $errors->has('description')]) placeholder="e.g. Morning" value="{{ old('description', $shift->description ?? '') }}" autocomplete="off" required>
                        </x-ui.field>
                        <x-ui.field label="Short code" for="abbreviation" error="abbreviation" class="col-span-4">
                            <input type="text" id="abbreviation" name="abbreviation" @class(['input input-mono', 'is-invalid' => $errors->has('abbreviation')]) placeholder="M" value="{{ old('abbreviation', $shift->abbreviation ?? '') }}" autocomplete="off">
                        </x-ui.field>
                        <x-ui.field label="Start time" for="start_time" error="start_time" required class="col-span-6">
                            <input type="time" id="start_time" name="start_time" @class(['input', 'is-invalid' => $errors->has('start_time')]) value="{{ old('start_time', $shift?->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '') }}" required>
                        </x-ui.field>
                        <x-ui.field label="End time" for="end_time" error="end_time" required class="col-span-6">
                            <input type="time" id="end_time" name="end_time" @class(['input', 'is-invalid' => $errors->has('end_time')]) value="{{ old('end_time', $shift?->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '') }}" required>
                        </x-ui.field>
                    </div>
                </div>
                <div class="panel-footer">
                    @isset($shift)
                        <a href="{{ route('shifts.add') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($shift) ? 'save' : 'plus' }}" />{{ isset($shift) ? 'Update shift' : 'Save shift' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing shifts" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#shifts-table">{{ count($shifts ?? []) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter shifts" data-table-filter="#shifts-table" />
            </div>
            <div class="table-wrap">
                <table id="shifts-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Shift ID</th>
                            <th>Description</th>
                            <th>Code</th>
                            <th>Hours</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shifts ?? [] as $index => $item)
                            <tr @class(['bg-brand-50/50' => isset($shift) && $shift->id === $item->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $item->id }}</span></td>
                                <td class="cell-strong">{{ $item->description }}</td>
                                <td>@if ($item->abbreviation)<x-ui.badge>{{ $item->abbreviation }}</x-ui.badge>@else<span class="muted">—</span>@endif</td>
                                <td class="nowrap tabular-nums">
                                    @if ($item->start_time && $item->end_time)
                                        {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                                    @else
                                        <span class="muted">N/A</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('shifts.edit', $item->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <form action="{{ route('shifts.destroy', $item->id) }}" method="POST" class="inline" data-confirm="This permanently deletes the {{ $item->description }} shift." data-confirm-title="Delete this shift?" data-confirm-label="Delete shift">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="6" class="p-0">
                                    <x-ui.empty-state icon="clock" title="No shifts yet" description="Add the first shift using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
@endsection
