@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Pathology Test Catalog</h2>
            <p class="text-gray-500 mt-2 text-lg">Detailed overview of all pathology tests and their parameters.</p>
        </div>
        <a href="{{ route('pathology.index') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2.5 px-6 rounded-xl shadow-sm transition-all duration-200 ease-in-out flex items-center group">
            <svg class="w-5 h-5 mr-2 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

    @forelse($testHeads as $head)
        <div class="mb-12">
            <!-- Test Head Header -->
            <div class="flex items-center mb-6 bg-gradient-to-r from-indigo-600 to-blue-500 p-4 rounded-2xl shadow-lg">
                <div class="bg-white/20 p-3 rounded-xl mr-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white">{{ $head->name }}</h3>
            </div>

            <div class="grid grid-cols-1 gap-8">
                @forelse($head->tests as $test)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-shadow hover:shadow-md">
                        <!-- Test Title Area -->
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-lg mr-3">
                                    {{ $test->test_id }}
                                </span>
                                <h4 class="text-xl font-bold text-gray-800">{{ $test->name }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="text-sm text-gray-500 block">Report Time</span>
                                <span class="font-semibold text-gray-700">{{ $test->report_time }} Hours</span>
                            </div>
                        </div>

                        <!-- Particulars Table -->
                        <div class="p-0">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50/30">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4">Particular Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6">Unit</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4">Normal Range</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference Note</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($test->testParticulars as $particular)
                                        <tr class="hover:bg-indigo-50/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $particular->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="hms-checkbox-row px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $particular->unit ?: 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($particular->normal_range_min !== null || $particular->normal_range_max !== null)
                                                    <div class="text-sm text-gray-700 font-medium">
                                                        {{ $particular->normal_range_min ?? '0' }} <span class="text-gray-400 mx-1">—</span> {{ $particular->normal_range_max ?? '∞' }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic text-sm">Qualitative/Text</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-500 italic">
                                                    {{ $particular->reference_text ?: 'No additional notes.' }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic text-sm bg-gray-50/20">
                                                No particulars defined for this test.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50 rounded-2xl p-12 text-center border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">No tests found under this category.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="bg-white rounded-3xl shadow-xl p-16 text-center">
            <div class="bg-indigo-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">No Categories Found</h3>
            <p class="text-gray-500 max-w-sm mx-auto">Start by adding Test Heads and Tests to build your catalog.</p>
        </div>
    @endforelse
@endsection
