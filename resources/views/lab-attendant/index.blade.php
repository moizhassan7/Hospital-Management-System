@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Laboratory Attendant Dashboard</h2>
        <a href="#" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="lab_attendant_form">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No:</label>
                    <input type="text" id="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter MR No.">
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Tests to be Completed</h3>
            <div id="pending_tests_container" class="mb-6">
                <p class="text-gray-500 text-center">Enter a patient MR No. to see their pending tests.</p>
            </div>
            
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Enter Test Result</h3>
            <div id="result_entry_form" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="test_name" class="block text-gray-700 text-sm font-bold mb-2">Selected Test:</label>
                    <input type="text" id="test_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
                <div>
                    <label for="result_value" class="block text-gray-700 text-sm font-bold mb-2">Result:</label>
                    <input type="text" id="result_value" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter result value">
                </div>
                <div>
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                    <select id="status" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="completed">Completed</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" id="save_result_btn" class="hidden bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Save Result
                </button>
            </div>
            
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Previous Test History</h3>
            <div id="test_history_container" class="overflow-x-auto mb-6">
                <p class="text-gray-500 text-center">Select a test to see its history.</p>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const pendingTestsContainer = document.getElementById('pending_tests_container');
            const resultEntryForm = document.getElementById('result_entry_form');
            const testNameInput = document.getElementById('test_name');
            const resultValueInput = document.getElementById('result_value');
            const statusSelect = document.getElementById('status');
            const saveResultBtn = document.getElementById('save_result_btn');
            const testHistoryContainer = document.getElementById('test_history_container');
            
            let selectedTestId = null;

            mrNoInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    fetchPatientTests(this.value);
                }
            });

            async function fetchPatientTests(mrNo) {
                try {
                    const response = await fetch(`/lab-attendant/get-patient-tests/${mrNo}`);
                    const data = await response.json();

                    if (response.ok) {
                        patientNameInput.value = data.patient.name;
                        renderPendingTests(data.tests);
                    } else {
                        alert(data.error);
                        clearForm();
                    }
                } catch (error) {
                    console.error('Error fetching patient tests:', error);
                    alert('Failed to fetch patient data.');
                    clearForm();
                }
            }

            function renderPendingTests(tests) {
                if (tests.length === 0) {
                    pendingTestsContainer.innerHTML = '<p class="text-gray-500 text-center">No pending tests for this patient.</p>';
                    return;
                }

                let html = '<ul class="divide-y divide-gray-200 rounded-lg border border-gray-200">';
                tests.forEach(test => {
                    html += `
                        <li class="p-4 hover:bg-gray-50 cursor-pointer" data-result-id="${test.id}" data-test-id="${test.test.id}" data-test-name="${test.test.name}">
                            <strong>${test.test.name}</strong> - Case: ${test.case_number}
                        </li>
                    `;
                });
                html += '</ul>';
                pendingTestsContainer.innerHTML = html;

                pendingTestsContainer.querySelectorAll('li').forEach(item => {
                    item.addEventListener('click', function() {
                        const testId = this.dataset.testId;
                        const testName = this.dataset.testName;
                        selectedTestId = this.dataset.resultId;
                        
                        testNameInput.value = testName;
                        resultEntryForm.classList.remove('hidden');
                        saveResultBtn.classList.remove('hidden');
                        resultValueInput.focus();
                        
                        fetchTestHistory(testId);
                    });
                });
            }

            async function fetchTestHistory(testId) {
                try {
                    const response = await fetch(`/lab-attendant/get-test-history/${testId}`);
                    const history = await response.json();
                    renderTestHistory(history);
                } catch (error) {
                    console.error('Error fetching test history:', error);
                    testHistoryContainer.innerHTML = '<p class="p-4 text-gray-500 text-center">Failed to load history.</p>';
                }
            }

            function renderTestHistory(history) {
                if (history.length === 0) {
                    testHistoryContainer.innerHTML = '<p class="text-gray-500 text-center">No previous results found for this test.</p>';
                    return;
                }

                let html = '<table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">';
                html += '<thead class="bg-gray-100 border-b border-gray-200"><tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Case Number</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th></tr></thead>';
                html += '<tbody class="divide-y divide-gray-200">';
                history.forEach(item => {
                    html += `
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.created_at}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.case_number}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.result}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.status}</td>
                        </tr>
                    `;
                });
                html += '</tbody></table>';
                testHistoryContainer.innerHTML = html;
            }

            saveResultBtn.addEventListener('click', async function() {
                if (!selectedTestId || !resultValueInput.value) {
                    alert('Please select a test and enter a result.');
                    return;
                }

                const payload = {
                    result_id: selectedTestId,
                    result_value: resultValueInput.value,
                    status: statusSelect.value,
                };
                
                try {
                    const response = await fetch(`/lab-attendant/save-result`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (response.ok) {
                        alert(data.message);
                        clearForm();
                    } else {
                        alert(data.error || 'Failed to save result.');
                    }
                } catch (error) {
                    console.error('Error saving result:', error);
                    alert('An error occurred while saving the result.');
                }
            });

            function clearForm() {
                mrNoInput.value = '';
                patientNameInput.value = '';
                pendingTestsContainer.innerHTML = '<p class="text-gray-500 text-center">Enter a patient MR No. to see their pending tests.</p>';
                resultEntryForm.classList.add('hidden');
                testHistoryContainer.innerHTML = '<p class="text-gray-500 text-center">Select a test to see its history.</p>';
                saveResultBtn.classList.add('hidden');
                selectedTestId = null;
            }
        });
    </script>
@endsection
