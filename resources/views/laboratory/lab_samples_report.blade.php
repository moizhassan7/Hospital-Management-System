@extends('layouts.app')

@section('page_title', 'Lab Samples Report')

@section('content')
    @php
        $fmt = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d-M-Y h:i A') : '—';
        $queryParams = request()->except(['page', 'vpage']);
    @endphp

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Lab Samples Report</h2>
        <a href="{{ route('pathology.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Pathology Lab
        </a>
    </div>

    <div class="flex border-b border-gray-200 mb-6">
        <a href="{{ route('pathology.lab_samples_report', array_merge($queryParams, ['tab' => 'tests'])) }}"
            class="px-6 py-3 text-sm font-semibold border-b-2 {{ $tab === 'tests' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Tests Workflow
        </a>
        <a href="{{ route('pathology.lab_samples_report', array_merge($queryParams, ['tab' => 'vials'])) }}"
            class="px-6 py-3 text-sm font-semibold border-b-2 {{ $tab === 'vials' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Sample Vials
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filters</h3>
        <form method="GET" action="{{ route('pathology.lab_samples_report') }}" class="space-y-4">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" required class="w-full border rounded-lg py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" required class="w-full border rounded-lg py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lab Reg No</label>
                    <input type="text" name="lab_reg" value="{{ $filters['lab_reg'] }}" class="w-full border rounded-lg py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MR No</label>
                    <input type="text" name="mr" value="{{ $filters['mr'] }}" class="w-full border rounded-lg py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                    <input type="text" name="name" value="{{ $filters['name'] }}" class="w-full border rounded-lg py-2 px-3 text-sm">
                </div>
                @if($tab === 'vials')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ $filters['barcode'] }}" class="w-full border rounded-lg py-2 px-3 text-sm">
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sample Status</label>
                    <select name="sample_status" class="w-full border rounded-lg py-2 px-3 text-sm">
                        <option value="all">All</option>
                        @foreach($sampleStatuses as $key => $label)
                            <option value="{{ $key }}" @selected($filters['sample_status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if($tab === 'tests')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Result Status</label>
                    <select name="result_status" class="w-full border rounded-lg py-2 px-3 text-sm">
                        <option value="all" @selected($filters['result_status'] === 'all')>All</option>
                        <option value="pending" @selected($filters['result_status'] === 'pending')>Pending</option>
                        <option value="completed" @selected($filters['result_status'] === 'completed')>Completed</option>
                    </select>
                </div>
                @endif
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">Search</button>
                <a href="{{ route('pathology.lab_samples_report', ['tab' => $tab]) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg">Reset</a>
                <a href="{{ route('pathology.lab_samples_report.print', array_merge($queryParams, ['tab' => $tab])) }}" target="_blank"
                    class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-6 rounded-lg">Print</a>
                <a href="{{ route('pathology.lab_samples_report.pdf', array_merge($queryParams, ['tab' => $tab])) }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg">PDF</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500"><p class="text-xs text-gray-500 uppercase">Patients</p><p class="text-xl font-bold">{{ $summary['patients'] }}</p></div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-indigo-500"><p class="text-xs text-gray-500 uppercase">Tests</p><p class="text-xl font-bold">{{ $summary['tests'] }}</p></div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500"><p class="text-xs text-gray-500 uppercase">Vials</p><p class="text-xl font-bold">{{ $summary['vials'] }}</p></div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-gray-400"><p class="text-xs text-gray-500 uppercase">Not Collected</p><p class="text-xl font-bold text-gray-600">{{ $summary['not_collected'] }}</p></div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500"><p class="text-xs text-gray-500 uppercase">Collected</p><p class="text-xl font-bold text-green-600">{{ $summary['collected'] }}</p></div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-400"><p class="text-xs text-gray-500 uppercase">In Lab</p><p class="text-xl font-bold text-blue-600">{{ $summary['in_lab'] }}</p></div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-teal-500"><p class="text-xs text-gray-500 uppercase">Results Done</p><p class="text-xl font-bold text-teal-600">{{ $summary['completed'] }}</p></div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        @if($tab === 'tests')
            <h3 class="text-lg font-semibold text-gray-800 mb-4">All Tests — Sample, Receive in Lab &amp; Results ({{ $test_rows->total() }})</h3>
            @if($test_rows->isEmpty())
                <p class="text-gray-600">No records found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reg Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lab Reg</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sample Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Collected</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Received in Lab</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Result At</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($test_rows as $index => $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-500">{{ $test_rows->firstItem() + $index }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmt($row['registration_date']) }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $row['lab_registration_no'] ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $row['patient_name'] }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $row['test_name'] }}</td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ \App\Models\LabSampleVial::statusBadgeClass($row['sample_status']) }}">
                                            {{ $row['sample_status_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmt($row['sample_collected_at']) }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmt($row['sample_received_in_lab_at']) }}</td>
                                    <td class="px-3 py-2">
                                        @if($row['result_status'] === 'completed')
                                            <span class="text-teal-700 font-semibold">Completed</span>
                                        @else
                                            <span class="text-orange-600 font-semibold">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmt($row['result_completed_at']) }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        @if($row['result_status'] === 'completed')
                                            <a href="{{ route('pathology.print_report', ['lab_patient_id' => $row['lab_patient_id'], 'test_id' => $row['test_id']]) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">Report</a>
                                        @else
                                            <a href="{{ route('pathology.result_entry.show_form', ['lab_patient_id' => $row['lab_patient_id'], 'test_id' => $row['test_id']]) }}" class="text-blue-600 hover:text-blue-800 font-medium">Enter</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($test_rows->hasPages())
                    <div class="mt-4">{{ $test_rows->appends(array_merge($queryParams, ['tab' => 'tests']))->links() }}</div>
                @endif
            @endif
        @else
            <h3 class="text-lg font-semibold text-gray-800 mb-4">All Sample Vials — Collection, Receive &amp; Report ({{ $vial_rows->total() }})</h3>
            @if($vial_rows->isEmpty())
                <p class="text-gray-600">No vial records found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lab Reg</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vial</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tests</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Collected</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Received in Lab</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reported</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($vial_rows as $index => $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-500">{{ $vial_rows->firstItem() + $index }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $row['lab_registration_no'] ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $row['patient_name'] }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $row['barcode'] }}</td>
                                    <td class="px-3 py-2">{{ $row['vial_type'] }} #{{ $row['vial_number'] }}</td>
                                    <td class="px-3 py-2 text-xs max-w-xs">{{ $row['tests'] }}</td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ \App\Models\LabSampleVial::statusBadgeClass($row['status']) }}">
                                            {{ $row['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmt($row['collected_at']) }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmt($row['received_in_lab_at']) }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmt($row['reported_at']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($vial_rows->hasPages())
                    <div class="mt-4">{{ $vial_rows->appends(array_merge($queryParams, ['tab' => 'vials']))->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection
