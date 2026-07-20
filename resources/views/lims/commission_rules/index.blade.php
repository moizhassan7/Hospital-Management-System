@extends('layouts.app')

@section('page_title', 'Commission Rules')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Commission Rules',
        'subtitle' => 'Fixed or percent rules by doctor, category, and optional collection center',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back to Pathology',
    ])

    <div class="hms-page-toolbar flex items-center justify-between mb-4 gap-3 flex-wrap">
        <form method="GET" class="flex items-center gap-2">
            <select name="doctor_id" class="hms-select text-sm" onchange="this.form.submit()">
                <option value="">All doctors</option>
                @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ (string) request('doctor_id') === (string) $doctor->id ? 'selected' : '' }}>
                        {{ $doctor->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('pathology.commission_snapshots.index') }}" class="hms-btn hms-btn-secondary">Snapshots</a>
            <a href="{{ route('pathology.lims_doctors.index') }}" class="hms-btn hms-btn-secondary">Doctors</a>
            <a href="{{ route('pathology.commission_rules.create') }}" class="hms-btn hms-btn-primary">Add rule</a>
        </div>
    </div>

    <div class="hms-panel hms-panel-flush">
        @if($rules->isEmpty())
            <div class="hms-re-empty p-8">
                <p class="hms-empty-title">No commission rules</p>
                <p class="hms-empty-desc">Create a rule so booking dual-write can snapshot doctor commission.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Doctor</th>
                            <th class="px-4 py-3 font-semibold">Scope</th>
                            <th class="px-4 py-3 font-semibold">Basis</th>
                            <th class="px-4 py-3 font-semibold">Value</th>
                            <th class="px-4 py-3 font-semibold">Effective</th>
                            <th class="px-4 py-3 font-semibold">Priority</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rules as $rule)
                            <tr>
                                <td class="px-4 py-3">{{ $rule->doctor?->name ?? 'Any doctor' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <div>{{ $rule->testCategory?->name ?? 'Any category' }}</div>
                                    <div>{{ $rule->collectionCenter?->code ?? 'Any CC' }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $rule->basis }}</td>
                                <td class="px-4 py-3 font-medium">
                                    @if($rule->basis === 'fixed')
                                        {{ number_format((float) $rule->amount, 2) }}
                                    @else
                                        {{ rtrim(rtrim(number_format((float) $rule->percent, 4), '0'), '.') }}%
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                    {{ optional($rule->effective_from)?->format('Y-m-d') }}
                                    →
                                    {{ optional($rule->effective_to)?->format('Y-m-d') ?? 'open' }}
                                </td>
                                <td class="px-4 py-3">{{ $rule->priority }}</td>
                                <td class="px-4 py-3">
                                    @if($rule->is_active)
                                        <span class="text-green-700 font-medium">Active</span>
                                    @else
                                        <span class="text-red-600 font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('pathology.commission_rules.edit', $rule) }}" class="hms-btn hms-btn-secondary text-xs py-1 px-2">Edit</a>
                                    @if($rule->is_active)
                                        <form action="{{ route('pathology.commission_rules.deactivate', $rule) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="hms-btn hms-btn-secondary text-xs py-1 px-2" onclick="return confirm('Deactivate this rule?')">Deactivate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
