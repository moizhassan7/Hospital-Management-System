@extends('layouts.app')

@section('page_title', $title)

@section('content')
    @php
        $fmt = function ($val) {
            if ($val === null || $val === '') {
                return '—';
            }
            if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
                try {
                    return \Carbon\Carbon::parse($val)->format('d-M-Y h:i A');
                } catch (\Throwable) {
                    return $val;
                }
            }

            return $val;
        };
    @endphp

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $title }}</h2>
        <a href="{{ route('ipd.index', ['tab' => $tab]) }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to IPD List
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Full Record</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($record->getAttributes() as $field => $value)
                <div class="border border-gray-100 rounded-lg p-3 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ str_replace('_', ' ', $field) }}</p>
                    <p class="text-sm text-gray-900 break-words">{{ $fmt($value) }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
