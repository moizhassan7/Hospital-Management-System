@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Add Test Particulars</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

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

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Define New Test Particular</h3>
        <form action="{{ route('laboratory.add_test_particulars.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="test_head" class="block text-gray-700 text-sm font-bold mb-2">Test Head:</label>
                    <select id="test_head" name="test_head_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Test Head</option>
                        @foreach($testHeads as $head)
                            <option value="{{ $head->id }}" {{ old('test_head_id') == $head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="test_name" class="block text-gray-700 text-sm font-bold mb-2">Test Name:</label>
                    <select id="test_name" name="test_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required disabled>
                        <option value="">Select Test Head First</option>
                    </select>
                </div>
                <div>
                    <label for="particular_name" class="block text-gray-700 text-sm font-bold mb-2">Particular Name:</label>
                    <input type="text" id="particular_name" name="particular_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Hemoglobin, Glucose" value="{{ old('particular_name') }}" required>
                </div>
                <div>
                    <label for="unit" class="block text-gray-700 text-sm font-bold mb-2">Unit:</label>
                    <input type="text" id="unit" name="unit" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., g/dL, mg/dL" value="{{ old('unit') }}">
                </div>
                <div>
                    <label for="normal_range_min" class="block text-gray-700 text-sm font-bold mb-2">Normal Range (Min):</label>
                    <input type="number" id="normal_range_min" name="normal_range_min" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 12.0" step="0.01" value="{{ old('normal_range_min') }}">
                </div>
                <div>
                    <label for="normal_range_max" class="block text-gray-700 text-sm font-bold mb-2">Normal Range (Max):</label>
                    <input type="number" id="normal_range_max" name="normal_range_max" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 16.0" step="0.01" value="{{ old('normal_range_max') }}">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="reference_text" class="block text-gray-700 text-sm font-bold mb-2">Reference Text/Notes:</label>
                    <textarea id="reference_text" name="reference_text" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Varies by age and gender">{{ old('reference_text') }}</textarea>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add Particular
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Test Particulars</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
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