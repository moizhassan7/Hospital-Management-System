@extends('layouts.app')

@section('page_title', 'Collection Centers')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Collection Centers',
        'subtitle' => 'Sites for booking, lab numbers, and tenancy',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back to Pathology',
    ])

    <div class="hms-page-toolbar flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">Main Lab admins manage hubs and spokes. Deactivating a center hides it from booking.</p>
        <a href="{{ route('pathology.collection_centers.create') }}" class="hms-btn hms-btn-primary">Add center</a>
    </div>

    <div class="hms-panel hms-panel-flush">
        @if($centers->isEmpty())
            <div class="hms-re-empty p-8">
                <p class="hms-empty-title">No collection centers yet</p>
                <p class="hms-empty-desc">Seed the organization or create the Main Lab site first.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Code</th>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">Kind</th>
                            <th class="px-4 py-3 font-semibold">Lab prefix</th>
                            <th class="px-4 py-3 font-semibold">Contact</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($centers as $center)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $center->code }}</td>
                                <td class="px-4 py-3">
                                    <div>{{ $center->name }}</div>
                                    @if($center->address)
                                        <div class="text-xs text-gray-400">{{ $center->address }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($center->kind === 'main_lab')
                                        <span class="hms-badge hms-badge-green">Main Lab</span>
                                    @else
                                        <span class="hms-badge">Collection Center</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $center->lab_number_prefix }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $center->phone ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($center->is_active)
                                        <span class="text-green-700 font-medium">Active</span>
                                    @else
                                        <span class="text-red-600 font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('pathology.collection_centers.edit', $center) }}" class="hms-btn hms-btn-secondary text-xs py-1 px-2">Edit</a>
                                    @unless($center->isMainLab() && $center->is_active)
                                        <form action="{{ route('pathology.collection_centers.toggle', $center) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="hms-btn text-xs py-1 px-2 {{ $center->is_active ? 'hms-btn-secondary' : 'hms-btn-primary' }}">
                                                {{ $center->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endunless
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
