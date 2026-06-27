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
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Define New Test Particular</h3>
        <form action="{{ route('laboratory.add_test_particulars.store') }}" method="POST">
            @csrf
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="test_head" class="hms-label">Test Head:</label>
                    <select id="test_head" name="test_head_id" class="hms-select" required>
                        <option value="">Select Test Head</option>
                        @foreach($testHeads as $head)
                            <option value="{{ $head->id }}" {{ old('test_head_id') == $head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="test_name" class="hms-label">Test Name:</label>
                    <select id="test_name" name="test_id" class="hms-select" required disabled>
                        <option value="">Select Test Head First</option>
                    </select>
                </div>
                <div>
                    <label for="particular_name" class="hms-label">Particular Name:</label>
                    <input type="text" id="particular_name" name="particular_name" class="hms-input" placeholder="e.g., Hemoglobin, Glucose" value="{{ old('particular_name') }}" required>
                </div>
                <div>
                    <label for="unit" class="hms-label">Unit:</label>
                    <input type="text" id="unit" name="unit" class="hms-input" placeholder="e.g., g/dL, mg/dL" value="{{ old('unit') }}">
                </div>
                <div>
                    <label for="normal_range_min" class="hms-label">Normal Range (Min):</label>
                    <input type="number" id="normal_range_min" name="normal_range_min" class="hms-input" placeholder="e.g., 12.0" step="0.01" value="{{ old('normal_range_min') }}">
                </div>
                <div>
                    <label for="normal_range_max" class="hms-label">Normal Range (Max):</label>
                    <input type="number" id="normal_range_max" name="normal_range_max" class="hms-input" placeholder="e.g., 16.0" step="0.01" value="{{ old('normal_range_max') }}">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="reference_text" class="hms-label">Reference Text/Notes:</label>
                    <textarea id="reference_text" name="reference_text" rows="2" class="hms-input" placeholder="e.g., Varies by age and gender">{{ old('reference_text') }}</textarea>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="hms-btn hms-btn-primary">
                    Add Particular
                </button>
            </div>
        </form>
    </div>

    <div class="hms-panel hms-panel-padded">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Test Particulars</h3>
        <div class="hms-table-wrap">
            <table class="hms-table">
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
                <tbody class="divide-y divide-gray-200">
                    @foreach($testParticulars as $index => $particular)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular->test->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular->test->testHead->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular->normal_range_min }} - {{ $particular->normal_range_max }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const testHeadSelect = document.getElementById('test_head');
            const testNameSelect = document.getElementById('test_name');

            function populateTestNames(testHeadId) {
                testNameSelect.innerHTML = '<option value="">Loading Tests...</option>';
                testNameSelect.disabled = true;

                if (!testHeadId) {
                    testNameSelect.innerHTML = '<option value="">Select Test Head First</option>';
                    return;
                }

                const url = `{{ route('api.tests_by_head', ['testHeadId' => 'test_head_id_placeholder']) }}`.replace('test_head_id_placeholder', testHeadId);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        testNameSelect.innerHTML = '<option value="">Select Test Name</option>';
                        data.forEach(test => {
                            const option = document.createElement('option');
                            option.value = test.id;
                            option.textContent = test.name;
                            testNameSelect.appendChild(option);
                        });
                        testNameSelect.disabled = false;
                        
                        // Re-select the old value after form submission
                        const oldTestId = "{{ old('test_id') }}";
                        if (oldTestId) {
                            testNameSelect.value = oldTestId;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching tests:', error);
                        testNameSelect.innerHTML = '<option value="">Error loading tests</option>';
                    });
            }

            // Event listener for Test Head dropdown change
            testHeadSelect.addEventListener('change', (e) => {
                populateTestNames(e.target.value);
            });

            // Initial population on page load if a test head was selected
            const initialTestHeadId = testHeadSelect.value;
            if (initialTestHeadId) {
                populateTestNames(initialTestHeadId);
            }
        });
    </script>
@endsection