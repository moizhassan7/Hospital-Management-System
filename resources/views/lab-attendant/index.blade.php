@extends('layouts.app')

@section('title', 'Laboratory attendant')

@section('content')
    <x-ui.page-header title="Laboratory attendant" subtitle="Look up a patient by MR number, complete pending tests and record the result.">
        <x-slot:actions>
            <a href="{{ route('laboratory.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Laboratory
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form id="lab_attendant_form" class="stack">
        <x-ui.panel title="Patient information" icon="user" flush>
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="MR no." for="mr_no" class="md:col-span-4">
                        <input type="text" id="mr_no" class="input input-mono" placeholder="Enter MR no. and press Enter" autocomplete="off">
                    </x-ui.field>
                    <x-ui.field label="Patient name" for="patient_name" class="md:col-span-8">
                        <input type="text" id="patient_name" class="input" placeholder="Auto-populated" readonly>
                    </x-ui.field>
                </div>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Tests to be completed" icon="flask" flush>
            <div class="panel-body">
                <div id="pending_tests_container">
                    <p class="text-[13px] text-fg-muted text-center">Enter a patient MR No. to see their pending tests.</p>
                </div>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Enter test result" icon="test-tube" flush>
            <div class="panel-body">
                <div id="result_entry_form" class="hidden form-grid">
                    <x-ui.field label="Selected test" for="test_name" class="md:col-span-6">
                        <input type="text" id="test_name" class="input" readonly>
                    </x-ui.field>
                    <x-ui.field label="Result" for="result_value" class="md:col-span-6">
                        <input type="text" id="result_value" class="input" placeholder="Enter result value">
                    </x-ui.field>
                    <x-ui.field label="Status" for="status" class="md:col-span-4">
                        <select id="status" class="select">
                            <option value="completed">Completed</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </x-ui.field>
                </div>
            </div>
            <div class="panel-footer">
                <button type="button" id="save_result_btn" class="hidden btn btn-primary" data-busy-label="Saving…">
                    <x-ui.icon name="save" />Save result
                </button>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Previous test history" icon="clock" flush>
            <div class="panel-body">
                <div id="test_history_container">
                    <p class="text-[13px] text-fg-muted text-center">Select a test to see its history.</p>
                </div>
            </div>
        </x-ui.panel>
    </form>

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
                    pendingTestsContainer.innerHTML = '<p class="text-[13px] text-fg-muted text-center">No pending tests for this patient.</p>';
                    return;
                }

                let html = '<ul class="divide-y divide-line rounded-lg border border-line">';
                tests.forEach(test => {
                    html += `
                        <li class="px-4 py-3 hover:bg-subtle cursor-pointer" data-result-id="${test.id}" data-test-id="${test.test.id}" data-test-name="${test.test.name}">
                            <strong class="text-fg">${test.test.name}</strong> <span class="muted">· Case ${test.case_number}</span>
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
                    testHistoryContainer.innerHTML = '<p class="text-[13px] text-fg-muted text-center">Failed to load history.</p>';
                }
            }

            function renderTestHistory(history) {
                if (history.length === 0) {
                    testHistoryContainer.innerHTML = '<p class="text-[13px] text-fg-muted text-center">No previous results found for this test.</p>';
                    return;
                }

                let html = '<div class="table-wrap -mx-5 -mb-5"><table class="table">';
                html += '<thead><tr><th>Date</th><th>Case number</th><th>Result</th><th>Status</th></tr></thead>';
                html += '<tbody>';
                history.forEach(item => {
                    html += `
                        <tr>
                            <td class="nowrap">${item.created_at}</td>
                            <td><span class="id">${item.case_number}</span></td>
                            <td class="cell-strong">${item.result}</td>
                            <td>${item.status}</td>
                        </tr>
                    `;
                });
                html += '</tbody></table></div>';
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
                pendingTestsContainer.innerHTML = '<p class="text-[13px] text-fg-muted text-center">Enter a patient MR No. to see their pending tests.</p>';
                resultEntryForm.classList.add('hidden');
                testHistoryContainer.innerHTML = '<p class="text-[13px] text-fg-muted text-center">Select a test to see its history.</p>';
                saveResultBtn.classList.add('hidden');
                selectedTestId = null;
            }
        });
    </script>
@endsection
