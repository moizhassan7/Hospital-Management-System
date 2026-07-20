@extends('layouts.app')

@section('page_title', 'Referring Doctors')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Referring Doctors',
        'subtitle' => 'LIMS doctors used for commission rules and booking referrals',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back to Pathology',
    ])

    <div class="hms-page-toolbar flex items-center justify-between mb-4 gap-3 flex-wrap">
        <p class="text-sm text-gray-500">Separate from report-sign doctors in Lab Settings.</p>
        <div class="flex gap-2">
            <a href="{{ route('pathology.commission_rules.index') }}" class="hms-btn hms-btn-secondary">Commission rules</a>
            <a href="{{ route('pathology.lims_doctors.create') }}" class="hms-btn hms-btn-primary">Add doctor</a>
        </div>
    </div>

    <div class="hms-panel hms-panel-flush">
        @if($doctors->isEmpty())
            <div class="hms-re-empty p-8">
                <p class="hms-empty-title">No referring doctors yet</p>
                <p class="hms-empty-desc">Create a doctor, then attach commission rules by category or center.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Code</th>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">Phone</th>
                            <th class="px-4 py-3 font-semibold">Ledger balance</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($doctors as $doctor)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $doctor->code ?: '—' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $doctor->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $doctor->phone ?: '—' }}</td>
                                <td class="px-4 py-3">{{ number_format((float) ($doctor->ledger?->balance ?? 0), 2) }}</td>
                                <td class="px-4 py-3">
                                    @if($doctor->is_active)
                                        <span class="text-green-700 font-medium">Active</span>
                                    @else
                                        <span class="text-red-600 font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('pathology.lims_doctors.ledger', $doctor) }}" class="hms-btn hms-btn-secondary text-xs py-1 px-2">Ledger</a>
                                    <a href="{{ route('pathology.lims_doctors.edit', $doctor) }}" class="hms-btn hms-btn-secondary text-xs py-1 px-2">Edit</a>
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
