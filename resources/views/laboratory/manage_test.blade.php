@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Manage Pathology Test</h2></div>
        <a href="{{ route('pathology.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Pathology
        </a>
    </div>

    @include('partials.flash-alerts')

@if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Please fix the following errors:</span>
            <ul class="mt-3 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="hms-panel hms-panel-padded mb-5">
        @if(isset($test))
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Edit Pathology Test</h3>
            <form action="{{ route('laboratory.manage_test.update', $test->id) }}" method="POST">
                @csrf
                @method('PUT')
        @else
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Add New Pathology Test</h3>
            <form action="{{ route('laboratory.manage_test.store') }}" method="POST">
                @csrf
        @endif
            <input type="hidden" name="category" value="Pathology">
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="test_name" class="hms-label">Test Name:</label>
                    <input type="text" id="test_name" name="test_name" class="hms-input" placeholder="e.g., Complete Blood Count" value="{{ old('test_name', $test->name ?? '') }}" required>
                </div>
                <div>
                    <label for="test_type" class="hms-label">Test Type:</label>
                    <select id="test_type" name="test_type" class="hms-select" required>
                        <option value="Routine" {{ (old('test_type', $test->type ?? '') == 'Routine') ? 'selected' : '' }}>Routine</option>
                        <option value="Blood" {{ (old('test_type', $test->type ?? '') == 'Blood') ? 'selected' : '' }}>Blood</option>
                        <option value="Urine" {{ (old('test_type', $test->type ?? '') == 'Urine') ? 'selected' : '' }}>Urine</option>
                        <option value="Stool" {{ (old('test_type', $test->type ?? '') == 'Stool') ? 'selected' : '' }}>Stool</option>
                        <option value="Tissue" {{ (old('test_type', $test->type ?? '') == 'Tissue') ? 'selected' : '' }}>Tissue</option>
                        <option value="Fluid" {{ (old('test_type', $test->type ?? '') == 'Fluid') ? 'selected' : '' }}>Fluid</option>
                        <option value="Other" {{ (old('test_type', $test->type ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label for="test_head_id" class="hms-label">Test Head:</label>
                    <select id="test_head_id" name="test_head_id" class="hms-select" required>
                        <option value="">Select Test Head</option>
                        @foreach($testHeads as $head)
                            <option value="{{ $head->id }}" {{ (old('test_head_id', $test->test_head_id ?? '') == $head->id) ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="priority" class="hms-label">Priority:</label>
                    <select id="priority" name="priority" class="hms-select" required>
                        <option value="">Select Priority</option>
                        <option value="Routine" {{ (old('priority', $test->priority ?? '') == 'Routine') ? 'selected' : '' }}>Routine</option>
                        <option value="Urgent" {{ (old('priority', $test->priority ?? '') == 'Urgent') ? 'selected' : '' }}>Urgent</option>
                        <option value="STAT" {{ (old('priority', $test->priority ?? '') == 'STAT') ? 'selected' : '' }}>STAT (Critical)</option>
                    </select>
                </div>
                <div>
                    <label for="report_time" class="hms-label">Report Time (Hours):</label>
                    <input type="number" id="report_time" name="report_time" class="hms-input" placeholder="e.g., 24" min="0" value="{{ old('report_time', $test->report_time ?? '') }}" required>
                </div>
                <div class="md:col-span-2 lg:col-span-3 border-t border-gray-200 pt-4 mt-2">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Sample Collection Settings</h4>
                </div>
                <div>
                    <label for="sample_expiry_hours" class="hms-label">Sample Expiry Time (Hours):</label>
                    <input type="number" id="sample_expiry_hours" name="sample_expiry_hours" class="hms-input" placeholder="e.g., 24" min="1" value="{{ old('sample_expiry_hours', $test->sample_expiry_hours ?? 24) }}">
                    <p class="text-xs text-gray-500 mt-1">Hours after collection before sample expires</p>
                </div>
                <div>
                    <label for="sample_vial" class="hms-label">Sample Vial Type:</label>
                    <input type="text" id="sample_vial" name="sample_vial" class="hms-input" list="sample_vial_options"
                        placeholder="e.g., EDTA (Purple)"
                        value="{{ old('sample_vial', $test->sample_vial ?? '') }}">
                    <datalist id="sample_vial_options">
                        @foreach(['EDTA (Purple)', 'Plain (Red)', 'Fluoride (Gray)', 'Citrate (Blue)', 'Heparin (Green)', 'Urine Container', 'Stool Container', 'Serum Separator', 'General'] as $vialType)
                            <option value="{{ $vialType }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label for="vial_volume" class="hms-label">Vial Volume:</label>
                    <input type="text" id="vial_volume" name="vial_volume" class="hms-input" placeholder="e.g., 3-5 ml" value="{{ old('vial_volume', $test->vial_volume ?? '') }}">
                    <p class="text-xs text-gray-500 mt-1">Volume to collect for this sample</p>
                </div>
                <div>
                    <label for="vials_required" class="hms-label">Vials Required:</label>
                    <input type="number" id="vials_required" name="vials_required" class="hms-input" placeholder="e.g., 1" min="1" max="10" value="{{ old('vials_required', $test->vials_required ?? 1) }}">
                    <p class="text-xs text-gray-500 mt-1">Number of vials needed for this test</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="hms-btn hms-btn-primary">
                    {{ isset($test) ? 'Update Test' : 'Add Test' }}
                </button>
            </div>
        </form>
    </div>

    <div class="hms-panel hms-panel-padded">
        <div class="flex flex-col gap-4 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-2xl font-semibold text-gray-800">Existing Tests</h3>
            <div class="hms-search-bar w-full sm:max-w-md">
                <input type="text" id="existing-tests-search" class="hms-input flex-1" placeholder="Search by test name or test head…" autocomplete="off">
            </div>
        </div>
        <p id="existing-tests-empty" class="hidden text-sm text-gray-500 mb-3">No tests match your search.</p>
        <div class="hms-table-wrap">
            <table class="hms-table" id="existing-tests-table">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report (Hours)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sample Vial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vial Volume</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry (Hrs)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="existing-tests-body">
                    @foreach($tests as $index => $test)
                        <tr data-search="{{ strtolower($test->name . ' ' . ($test->testHead->name ?? '') . ' ' . $test->type . ' ' . $test->priority . ' ' . ($test->sample_vial ?? '')) }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->testHead->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->priority }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->report_time }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->sample_vial ?? '—' }} ({{ $test->vials_required ?? 1 }}x)</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->vial_volume ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->sample_expiry_hours ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('pathology.manage_test.edit', $test->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <form action="{{ route('laboratory.manage_test.destroy', $test->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this test?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('existing-tests-search');
            const tableBody = document.getElementById('existing-tests-body');
            const emptyMessage = document.getElementById('existing-tests-empty');

            if (!searchInput || !tableBody) {
                return;
            }

            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                tableBody.querySelectorAll('tr[data-search]').forEach(function (row) {
                    const matches = query === '' || row.dataset.search.includes(query);
                    row.classList.toggle('hidden', !matches);

                    if (matches) {
                        visibleCount++;
                    }
                });

                emptyMessage.classList.toggle('hidden', visibleCount > 0 || query === '');
            });
        });
    </script>
@endsection