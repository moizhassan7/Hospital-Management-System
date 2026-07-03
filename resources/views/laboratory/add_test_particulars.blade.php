@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Pathology Test Particulars</h2></div>
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
        @if(isset($particular))
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Edit Test Particular</h3>
            <form action="{{ route('laboratory.add_test_particulars.update', $particular->id) }}" method="POST">
                @csrf
                @method('PUT')
        @else
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Define New Test Particular</h3>
            <form action="{{ route('laboratory.add_test_particulars.store') }}" method="POST">
                @csrf
        @endif
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="test_head" class="hms-label">Test Head:</label>
                    <select id="test_head" name="test_head_id" class="hms-select" required>
                        <option value="">Select Test Head</option>
                        @foreach($testHeads as $head)
                            <option value="{{ $head->id }}" {{ (old('test_head_id', $particular->test->test_head_id ?? '') == $head->id) ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="test_name" class="hms-label">Test Name:</label>
                    <select id="test_name" name="test_id" class="hms-select" required @unless(isset($particular)) disabled @endunless>
                        @if(isset($particular) && $particular->test)
                            <option value="{{ $particular->test_id }}" selected>{{ $particular->test->name }}</option>
                        @else
                            <option value="">Select Test Head First</option>
                        @endif
                    </select>
                    <input type="hidden" id="preselected_test_id" value="{{ old('test_id', isset($particular) ? $particular->test_id : '') }}">
                </div>
                <div>
                    <label for="particular_name" class="hms-label">Particular Name:</label>
                    <input type="text" id="particular_name" name="particular_name" class="hms-input" placeholder="e.g., Hemoglobin, Glucose" value="{{ old('particular_name', $particular->name ?? '') }}" required>
                </div>
                <div>
                    <label for="unit" class="hms-label">Unit:</label>
                    <input type="text" id="unit" name="unit" class="hms-input" placeholder="e.g., g/dL, mg/dL" value="{{ old('unit', $particular->unit ?? '') }}">
                </div>
                <div>
                    <label for="normal_range_min" class="hms-label">Normal Range (Min):</label>
                    <input type="number" id="normal_range_min" name="normal_range_min" class="hms-input" placeholder="e.g., 12.0" step="0.01" value="{{ old('normal_range_min', $particular->normal_range_min ?? '') }}">
                </div>
                <div>
                    <label for="normal_range_max" class="hms-label">Normal Range (Max):</label>
                    <input type="number" id="normal_range_max" name="normal_range_max" class="hms-input" placeholder="e.g., 16.0" step="0.01" value="{{ old('normal_range_max', $particular->normal_range_max ?? '') }}">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="reference_text" class="hms-label">Reference Text/Notes:</label>
                    <textarea id="reference_text" name="reference_text" rows="2" class="hms-input" placeholder="e.g., Varies by age and gender">{{ old('reference_text', $particular->reference_text ?? '') }}</textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                @if(isset($particular))
                    <a href="{{ route('pathology.add_test_particulars') }}" class="hms-btn hms-btn-ghost">Cancel</a>
                @endif
                <button type="submit" class="hms-btn hms-btn-primary">
                    {{ isset($particular) ? 'Update Particular' : 'Add Particular' }}
                </button>
            </div>
        </form>
    </div>

    <div class="hms-panel hms-panel-padded">
        <div class="flex flex-col gap-4 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-2xl font-semibold text-gray-800">Existing Test Particulars</h3>
            <div class="hms-search-bar w-full sm:max-w-md">
                <input type="text" id="existing-particulars-search" class="hms-input flex-1" placeholder="Search by particular, test or test head…" autocomplete="off">
            </div>
        </div>
        <p id="existing-particulars-empty" class="hidden text-sm text-gray-500 mb-3">No particulars match your search.</p>
        <div class="hms-table-wrap">
            <table class="hms-table" id="existing-particulars-table">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Normal Range</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="existing-particulars-body">
                    @foreach($testParticulars as $index => $listedParticular)
                        <tr data-search="{{ strtolower($listedParticular->name . ' ' . ($listedParticular->test->name ?? '') . ' ' . ($listedParticular->test->testHead->name ?? '') . ' ' . ($listedParticular->unit ?? '')) }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->test->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->test->testHead->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->normal_range_min }} - {{ $listedParticular->normal_range_max }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('pathology.add_test_particulars.edit', $listedParticular->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <form action="{{ route('laboratory.add_test_particulars.destroy', $listedParticular->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this particular?');">
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
            const particularsSearchInput = document.getElementById('existing-particulars-search');
            const particularsTableBody = document.getElementById('existing-particulars-body');
            const particularsEmptyMessage = document.getElementById('existing-particulars-empty');

            if (particularsSearchInput && particularsTableBody) {
                particularsSearchInput.addEventListener('input', function () {
                    const query = particularsSearchInput.value.trim().toLowerCase();
                    let visibleCount = 0;

                    particularsTableBody.querySelectorAll('tr[data-search]').forEach(function (row) {
                        const matches = query === '' || row.dataset.search.includes(query);
                        row.classList.toggle('hidden', !matches);

                        if (matches) {
                            visibleCount++;
                        }
                    });

                    particularsEmptyMessage.classList.toggle('hidden', visibleCount > 0 || query === '');
                });
            }

            const testHeadSelect = document.getElementById('test_head');
            const testNameSelect = document.getElementById('test_name');
            const preselectedTestIdInput = document.getElementById('preselected_test_id');

            function getPreselectedTestId() {
                return preselectedTestIdInput ? String(preselectedTestIdInput.value || '').trim() : '';
            }

            function populateTestNames(testHeadId, testIdToSelect) {
                if (!testHeadId) {
                    testNameSelect.innerHTML = '<option value="">Select Test Head First</option>';
                    testNameSelect.disabled = true;
                    return;
                }

                const selectedId = String(testIdToSelect || getPreselectedTestId() || '');
                const hasCurrentSelection = selectedId !== '' && testNameSelect.value === selectedId;

                if (!hasCurrentSelection) {
                    testNameSelect.innerHTML = '<option value="">Loading Tests...</option>';
                    testNameSelect.disabled = true;
                }

                const url = `{{ route('api.tests_by_head', ['testHeadId' => 'test_head_id_placeholder']) }}`.replace('test_head_id_placeholder', testHeadId);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        testNameSelect.innerHTML = '<option value="">Select Test Name</option>';
                        data.forEach(test => {
                            const option = document.createElement('option');
                            option.value = String(test.id);
                            option.textContent = test.name;
                            testNameSelect.appendChild(option);
                        });
                        testNameSelect.disabled = false;

                        if (selectedId) {
                            testNameSelect.value = selectedId;

                            if (testNameSelect.value !== selectedId) {
                                const fallback = data.find(test => String(test.id) === selectedId);
                                if (fallback) {
                                    const option = document.createElement('option');
                                    option.value = String(fallback.id);
                                    option.textContent = fallback.name;
                                    testNameSelect.appendChild(option);
                                    testNameSelect.value = String(fallback.id);
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching tests:', error);
                        testNameSelect.innerHTML = '<option value="">Error loading tests</option>';
                        testNameSelect.disabled = true;
                    });
            }

            testHeadSelect.addEventListener('change', (e) => {
                if (preselectedTestIdInput) {
                    preselectedTestIdInput.value = '';
                }
                populateTestNames(e.target.value, '');
            });

            testNameSelect.addEventListener('change', (e) => {
                if (preselectedTestIdInput) {
                    preselectedTestIdInput.value = e.target.value;
                }
            });

            const initialTestHeadId = testHeadSelect.value;
            if (initialTestHeadId) {
                populateTestNames(initialTestHeadId, getPreselectedTestId());
            }
        });
    </script>
@endsection