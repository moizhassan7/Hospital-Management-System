@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Add Test Particulars</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    <!-- Add Test Particulars Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Define New Test Particular</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="test_head" class="block text-gray-700 text-sm font-bold mb-2">Test Head:</label>
                    <select id="test_head" name="test_head" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Test Head</option>
                        {{-- Static Test Heads --}}
                        <option value="Hematology">Hematology</option>
                        <option value="Biochemistry">Biochemistry</option>
                        <option value="Microbiology">Microbiology</option>
                        <option value="Pathology">Pathology</option>
                        <option value="Serology">Serology</option>
                    </select>
                </div>
                <div>
                    <label for="test_name" class="block text-gray-700 text-sm font-bold mb-2">Test Name:</label>
                    <select id="test_name" name="test_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required disabled>
                        <option value="">Select Test Head First</option>
                    </select>
                </div>
                <div>
                    <label for="particular_name" class="block text-gray-700 text-sm font-bold mb-2">Particular Name:</label>
                    <input type="text" id="particular_name" name="particular_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Hemoglobin, Glucose" required>
                </div>
                <div>
                    <label for="unit" class="block text-gray-700 text-sm font-bold mb-2">Unit:</label>
                    <input type="text" id="unit" name="unit" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., g/dL, mg/dL">
                </div>
                <div>
                    <label for="normal_range_min" class="block text-gray-700 text-sm font-bold mb-2">Normal Range (Min):</label>
                    <input type="number" id="normal_range_min" name="normal_range_min" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 12.0" step="0.01">
                </div>
                <div>
                    <label for="normal_range_max" class="block text-gray-700 text-sm font-bold mb-2">Normal Range (Max):</label>
                    <input type="number" id="normal_range_max" name="normal_range_max" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 16.0" step="0.01">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="reference_text" class="block text-gray-700 text-sm font-bold mb-2">Reference Text/Notes:</label>
                    <textarea id="reference_text" name="reference_text" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Varies by age and gender"></textarea>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add Particular
                </button>
            </div>
        </form>
    </div>

    <!-- Test Particulars List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Test Particulars</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Normal Range</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Test Particular Data --}}
                    @php
                        $particulars = [
                            ['id' => 'P001', 'name' => 'Hemoglobin', 'test' => 'Complete Blood Count (CBC)', 'unit' => 'g/dL', 'range' => '12.0 - 16.0'],
                            ['id' => 'P002', 'name' => 'White Blood Cell Count', 'test' => 'Complete Blood Count (CBC)', 'unit' => 'x10^9/L', 'range' => '4.0 - 11.0'],
                            ['id' => 'P003', 'name' => 'Fasting Glucose', 'test' => 'Blood Glucose Fasting', 'unit' => 'mg/dL', 'range' => '70 - 99'],
                            ['id' => 'P004', 'name' => 'Creatinine', 'test' => 'Kidney Function Test', 'unit' => 'mg/dL', 'range' => '0.6 - 1.2'],
                            ['id' => 'P005', 'name' => 'Urinalysis pH', 'test' => 'Urine Routine Examination', 'unit' => '', 'range' => '4.5 - 8.0'],
                        ];
                    @endphp
                    @foreach($particulars as $index => $particular)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['test'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['unit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['range'] }}</td>
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

            // Static data for tests based on test heads
            const testsByHead = {
                'Hematology': [
                    { id: 'T001', name: 'Complete Blood Count (CBC)' },
                    { id: 'T006', name: 'Erythrocyte Sedimentation Rate (ESR)' }
                ],
                'Biochemistry': [
                    { id: 'T002', name: 'Blood Glucose Fasting' },
                    { id: 'T007', name: 'Kidney Function Test (KFT)' },
                    { id: 'T008', name: 'Liver Function Test (LFT)' }
                ],
                'Microbiology': [
                    { id: 'T003', name: 'Urine Routine Examination' },
                    { id: 'T009', name: 'Blood Culture' }
                ],
                'Pathology': [
                    { id: 'T010', name: 'Biopsy' },
                    { id: 'T011', name: 'Cytology' }
                ],
                'Serology': [
                    { id: 'T005', name: 'Hepatitis B Surface Antigen (HBsAg)' },
                    { id: 'T012', name: 'HIV Screening' }
                ]
            };

            function populateTestNames() {
                const selectedHead = testHeadSelect.value;
                testNameSelect.innerHTML = '<option value="">Select Test Name</option>'; // Clear previous options
                testNameSelect.disabled = true; // Disable until a head is selected

                if (selectedHead && testsByHead[selectedHead]) {
                    testsByHead[selectedHead].forEach(test => {
                        const option = document.createElement('option');
                        option.value = test.id;
                        option.textContent = test.name;
                        testNameSelect.appendChild(option);
                    });
                    testNameSelect.disabled = false; // Enable if tests are available
                }
            }

            // Event listener for Test Head dropdown change
            testHeadSelect.addEventListener('change', populateTestNames);

            // Initial population if a test head is pre-selected (e.g., after form submission with errors)
            populateTestNames();
        });
    </script>
@endsection
