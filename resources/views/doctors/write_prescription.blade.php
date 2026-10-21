@extends('layouts.doctor')

@section('title', 'Write prescription')

@push('styles')
    <style>
        .ts-wrapper.multi .ts-control {
            min-height: var(--control-h);
            padding: 4px 8px;
            border: 1px solid var(--color-line-strong);
            border-radius: var(--radius-control);
            box-shadow: inset 0 1px 1px rgb(18 43 59 / 0.03);
            font-size: 13.5px;
        }

        .ts-wrapper.focus .ts-control {
            border-color: var(--color-brand-600);
            box-shadow: 0 0 0 3px rgb(8 127 131 / 0.16);
        }

        .ts-wrapper.multi .ts-control > .item {
            padding: 1px 8px;
            border: 1px solid var(--color-brand-100);
            border-radius: 999px;
            background: var(--color-brand-50);
            font-size: 12.5px;
            font-weight: 600;
            color: var(--color-brand-800);
        }

        .ts-dropdown {
            border-color: var(--color-line);
            border-radius: 8px;
            box-shadow: var(--shadow-pop);
            font-size: 13px;
        }

        .ts-dropdown .active {
            background: var(--color-brand-50);
            color: var(--color-brand-800);
        }
    </style>
@endpush

@section('content')
    <x-ui.page-header title="Write prescription" subtitle="Vitals, complaints, diagnoses and medicines for this visit.">
        <x-slot:meta>
            <x-ui.badge tone="warning" dot>Preview</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('doctors.dashboard') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Doctor dashboard
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="warning" title="Patients, reports and saving are simulated in this preview" class="mb-5 max-w-[1180px]">
        Try MR number MRN-001 or MRN-002. Shortcuts: Ctrl + S save · Ctrl + P print · Ctrl + M medicines · Ctrl + D diagnosis · Ctrl + R report.
    </x-ui.alert>

    <form id="prescription_form" action="#" method="POST" class="max-w-[1180px]">
        @csrf

        <x-ui.panel flush>
            <x-ui.form-section title="Patient" icon="user">
                <div class="space-y-4">
                    <div class="form-grid">
                        <x-ui.field label="MR number" for="mr_number" required class="md:col-span-4 xl:col-span-3">
                            <input type="text" id="mr_number" name="mr_number" class="input input-mono" placeholder="e.g. MRN-001" autocomplete="off" required>
                        </x-ui.field>
                        <x-ui.field label="Date" for="current_date" class="md:col-span-4 xl:col-span-2 xl:col-start-9">
                            <input type="text" id="current_date" name="current_date" class="input tabular-nums" value="{{ date('Y-m-d') }}" readonly>
                        </x-ui.field>
                        <x-ui.field label="Time" for="current_time" class="md:col-span-4 xl:col-span-2">
                            <input type="text" id="current_time" name="current_time" class="input tabular-nums" value="{{ date('H:i') }}" readonly>
                        </x-ui.field>
                    </div>

                    <div class="patient-band shadow-none" role="group" aria-label="Loaded patient">
                        <div class="band-cell band-cell-grow">
                            <label for="patient_name" class="band-label">Patient name <span class="field-tag ml-0">Auto</span></label>
                            <input type="text" id="patient_name" name="patient_name" class="band-input" placeholder="No patient loaded yet" value="" readonly>
                        </div>
                        <div class="band-cell w-24">
                            <label for="age" class="band-label">Age</label>
                            <input type="text" id="age" name="age" class="band-input" placeholder="—" value="" readonly>
                        </div>
                        <div class="band-cell w-28">
                            <label for="sex" class="band-label">Sex</label>
                            <input type="text" id="sex" name="sex" class="band-input" placeholder="—" value="" readonly>
                        </div>
                    </div>

                    <x-ui.field label="Previous visits">
                        <div id="history_list" class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-line bg-subtle p-3">
                            <p class="text-[13px] text-fg-muted">Enter an MR number to view previous visits.</p>
                        </div>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Vitals" icon="heart-pulse">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                    <x-ui.field label="Blood pressure" for="bp">
                        <div class="input-group has-end">
                            <input type="text" id="bp" name="bp" class="input tabular-nums" placeholder="120/80">
                            <span class="input-affix is-end">mmHg</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Pulse" for="pulse">
                        <div class="input-group has-end">
                            <input type="number" id="pulse" name="pulse" class="input input-num" placeholder="72">
                            <span class="input-affix is-end">bpm</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Oxygen" for="oxygen">
                        <div class="input-group has-end">
                            <input type="number" id="oxygen" name="oxygen" class="input input-num" placeholder="98">
                            <span class="input-affix is-end">%</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Temperature" for="temperature">
                        <div class="input-group has-end">
                            <input type="number" id="temperature" name="temperature" class="input input-num" placeholder="98.6" step="0.1">
                            <span class="input-affix is-end">°F</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Weight" for="weight">
                        <div class="input-group has-end">
                            <input type="number" id="weight" name="weight" class="input input-num" placeholder="70">
                            <span class="input-affix is-end">kg</span>
                        </div>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Complaints, diagnoses & reports" icon="clipboard">
                <div class="form-grid">
                    <x-ui.field label="Patient complaints" for="complaints" class="md:col-span-12">
                        <textarea id="complaints" name="complaints" rows="3" class="textarea" placeholder="Describe the patient's complaints"></textarea>
                    </x-ui.field>
                    <div class="field md:col-span-6">
                        <div class="flex items-center justify-between">
                            <label for="diagnoses_select" class="field-label">Diagnoses</label>
                            <button type="button" id="add_diagnoses_btn" class="btn btn-ghost btn-sm">New diagnosis</button>
                        </div>
                        <select id="diagnoses_select" multiple></select>
                    </div>
                    <div class="field md:col-span-6">
                        <div class="flex items-center justify-between">
                            <label for="reports_select" class="field-label">Reports</label>
                            <button type="button" id="add_reports_btn" class="btn btn-ghost btn-sm">New report type</button>
                        </div>
                        <select id="reports_select" multiple></select>
                    </div>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Prescription (Rx)" icon="pill">
                <div class="space-y-4">
                    <div class="form-grid">
                        <x-ui.field label="Medicine" for="medicine_search" hint="Use the search button or Ctrl + M." class="md:col-span-6">
                            <div class="flex gap-2">
                                <input type="text" id="medicine_search" name="medicine_search" class="input" placeholder="Search for a medicine" readonly>
                                <button type="button" id="search_medicine_btn" class="btn btn-secondary btn-icon" aria-label="Search medicines"><x-ui.icon name="search" /></button>
                            </div>
                        </x-ui.field>
                        <x-ui.field label="Medicine group" for="medicine_groups" hint="Choosing a group adds all of its medicines." class="md:col-span-6">
                            <div class="flex gap-2">
                                <select id="medicine_groups" name="medicine_groups" class="select">
                                    <option value="">Select a group</option>
                                    <option value="group_fever">Fever</option>
                                    <option value="group_headache">Headache</option>
                                    <option value="group_cold_flu">Cold & Flu</option>
                                </select>
                                <button type="button" id="add_medicine_group_btn" class="btn btn-secondary btn-icon" title="Add New Medicine Group" aria-label="Add new medicine group"><x-ui.icon name="plus" /></button>
                            </div>
                        </x-ui.field>
                        <x-ui.field label="Dosage & frequency" for="dosage_frequency" class="md:col-span-6">
                            <input type="text" id="dosage_frequency" name="dosage_frequency" class="input" placeholder="e.g. 1 tablet twice a day">
                        </x-ui.field>
                        <x-ui.field label="Duration" for="duration" class="md:col-span-3">
                            <div class="input-group has-end">
                                <input type="number" id="duration" name="duration" class="input input-num" placeholder="7">
                                <span class="input-affix is-end">days</span>
                            </div>
                        </x-ui.field>
                        <div class="flex items-end md:col-span-3">
                            <button type="button" id="add_medicine_btn" class="btn btn-primary btn-block">
                                <x-ui.icon name="plus" />Add medicine
                            </button>
                        </div>
                    </div>

                    <div class="table-wrap rounded-lg border border-line">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="num w-12">#</th>
                                    <th>Medicine</th>
                                    <th>Dosage & frequency</th>
                                    <th>Duration</th>
                                    <th class="actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="prescription_table_body">
                                {{-- Prescribed medicines will be added here dynamically --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="form-grid">
                        <x-ui.field label="Doctor's notes" for="notes" class="md:col-span-9">
                            <textarea id="notes" name="notes" rows="3" class="textarea" placeholder="Final notes for the patient"></textarea>
                        </x-ui.field>
                        <x-ui.field label="Next visit date" for="next_visit_date" class="md:col-span-3">
                            <input type="date" id="next_visit_date" name="next_visit_date" class="input">
                        </x-ui.field>
                    </div>
                </div>
            </x-ui.form-section>

            <x-slot:footer class="is-sticky">
                <button type="button" id="print_btn" class="btn btn-secondary"><x-ui.icon name="printer" />Print</button>
                <button type="submit" id="save_btn" class="btn btn-primary"><x-ui.icon name="save" />Save</button>
            </x-slot:footer>
        </x-ui.panel>
    </form>

    <x-ui.dialog id="medicineSearchModal" title="Search medicines" size="lg" icon="pill">
        <div class="input-group has-start mb-3">
            <span class="input-affix is-start"><x-ui.icon name="search" /></span>
            <input type="text" id="medicine_search_input_modal" class="input" placeholder="Search by name" autocomplete="off">
        </div>
        <div class="result-list">
            <table class="table table-compact">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Unit</th>
                        <th class="actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="medicine_search_results_body">
                    {{-- Static Medicine Data will be rendered here --}}
                </tbody>
            </table>
        </div>
    </x-ui.dialog>

    <x-ui.dialog id="addDiagnosisModal" title="Add new diagnosis" size="sm" icon="plus">
        <x-ui.field label="Diagnosis name" for="new_diagnosis_name">
            <input type="text" id="new_diagnosis_name" class="input" placeholder="e.g. Pneumonia">
        </x-ui.field>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" onclick="closeModal('addDiagnosisModal')">Cancel</button>
            <button type="button" id="save_new_diagnosis_btn" class="btn btn-primary">Add diagnosis</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="addReportModal" title="Add new report type" size="sm" icon="plus">
        <x-ui.field label="Report name" for="new_report_name">
            <input type="text" id="new_report_name" class="input" placeholder="e.g. Blood culture">
        </x-ui.field>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" onclick="closeModal('addReportModal')">Cancel</button>
            <button type="button" id="save_new_report_btn" class="btn btn-primary">Add report type</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="addMedicineGroupModal" title="Add new medicine group" size="xl" icon="layers">
        <div class="space-y-4">
            <x-ui.field label="Group name" for="new_group_name" class="max-w-md">
                <input type="text" id="new_group_name" class="input" placeholder="e.g. Common cold treatment">
            </x-ui.field>
            <div>
                <p class="field-label mb-2">Medicines in this group</p>
                <div id="new_group_medicines_container" class="space-y-3"></div>
                <button type="button" id="add_group_medicine_field_btn" class="btn btn-secondary btn-sm mt-3">+ Add medicine</button>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" onclick="closeModal('addMedicineGroupModal')">Cancel</button>
            <button type="button" id="save_new_group_btn" class="btn btn-primary">Save group</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="historyModal" title="Visit details" size="lg" icon="file-text">
        <div id="history_modal_content"></div>
    </x-ui.dialog>

    <x-ui.dialog id="reportModal" title="Lab report details" size="lg" icon="flask">
        <div id="report_content" class="text-fg"></div>
    </x-ui.dialog>

    <x-ui.dialog id="messageBox" title="Message" size="sm" icon="info">
        <p id="messageText" class="text-[13.5px] text-fg"></p>
        <x-slot:footer>
            <button type="button" class="btn btn-primary" onclick="closeModal('messageBox')">OK</button>
        </x-slot:footer>
    </x-ui.dialog>

    <div id="printable-prescription" class="print-only p-8">
    </div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script>
    // --- DATA STRUCTURES (SIMULATED) ---
    const ALL_PATIENT_DATA = {
        'MRN-001': {
            name: 'Jane Doe',
            age: 35,
            sex: 'Female',
            visits: [
                {
                    date: '2024-05-10',
                    complaints: 'Seasonal allergies, sneezing, and watery eyes.',
                    notes: 'Initial consultation for seasonal allergies. Patient reports sneezing and watery eyes. No fever or cough.',
                    vitals: { bp: '118/78', pulse: 75, oxygen: 99, temp: 98.4, weight: 65 },
                    diagnoses: ['Seasonal Allergies'],
                    reports: ['RPT001'],
                    medicines: [{ name: 'Cetirizine', dosage: '1 tablet Daily', duration: 7 }]
                },
                {
                    date: '2023-11-15',
                    complaints: 'Occasional shortness of breath with physical exertion.',
                    notes: 'Follow-up for mild asthma. Patient reports occasional shortness of breath with physical exertion. Lung sounds clear.',
                    vitals: { bp: '122/80', pulse: 78, oxygen: 98, temp: 98.6, weight: 64 },
                    diagnoses: ['Mild Asthma'],
                    reports: [],
                    medicines: [{ name: 'Inhaler', dosage: '2 puffs As needed', duration: 30 }]
                }
            ]
        },
        'MRN-002': {
            name: 'John Smith',
            age: 52,
            sex: 'Male',
            visits: [
                {
                    date: '2024-06-20',
                    complaints: 'Routine check-up, no major issues.',
                    notes: 'Routine check-up. Patient reports no major issues. Blood pressure elevated.',
                    vitals: { bp: '140/90', pulse: 85, oxygen: 98, temp: 98.7, weight: 85 },
                    diagnoses: ['Mild Hypertension'],
                    reports: ['RPT003'],
                    medicines: [{ name: 'Amlodipine', dosage: '5mg Daily', duration: 30 }]
                }
            ]
        }
    };

    let ALL_DIAGNOSES = ['Fever', 'Headache', 'Cough', 'Body Aches', 'Seasonal Allergies', 'Mild Asthma', 'Mild Hypertension'];
    let ALL_REPORTS = [
        { id: 'RPT001', name: 'Complete Blood Count (CBC)', content: 'White Blood Cell (WBC) Count: 8.5 x 10^9/L (Normal: 4.5-11.0). Red Blood Cell (RBC) Count: 4.8 x 10^12/L (Normal: 4.2-5.4). Hemoglobin: 14.2 g/dL (Normal: 12.0-16.0). All results are within normal limits.' },
        { id: 'RPT002', name: 'Chest X-ray Report', content: 'FINDINGS: Normal chest radiograph. The lungs are clear without evidence of consolidation, effusion, or pneumothorax. The cardiac silhouette is of normal size. Bony structures appear unremarkable. IMPRESSION: No acute cardiopulmonary disease.' },
        { id: 'RPT003', name: 'Lipid Panel', content: 'Total Cholesterol: 215 mg/dL (High). HDL Cholesterol: 45 mg/dL (Low). LDL Cholesterol: 140 mg/dL (High). Triglycerides: 150 mg/dL (Normal).' },
    ];
    let ALL_MEDICINES = [
        { id: 'MED001', name: 'Paracetamol', unit: '500mg' },
        { id: 'MED002', name: 'Ibuprofen', unit: '200mg' },
        { id: 'MED003', name: 'Amoxicillin', unit: '250mg' },
        { id: 'MED004', name: 'Cetirizine', unit: '10mg' },
        { id: 'MED005', name: 'Omeprazole', unit: '20mg' },
        { id: 'MED006', name: 'Atorvastatin', unit: '10mg' },
        { id: 'MED007', name: 'Lisinopril', unit: '2.5mg' },
        { id: 'MED008', name: 'Metformin', unit: '500mg' },
        { id: 'MED009', name: 'Ciprofloxacin', unit: '500mg' },
    ];
    let MEDICINE_GROUPS = {
        'group_fever': [{ name: 'Paracetamol', dosage: '1-2 tablets Every 6 hours', duration: 3 }],
        'group_headache': [{ name: 'Ibuprofen', dosage: '1 tablet As needed', duration: 1 }],
        'group_cold_flu': [
            { name: 'Cetirizine', dosage: '1 tablet Daily', duration: 5 },
            { name: 'Paracetamol', dosage: '1 tablet Twice daily', duration: 5 }
        ]
    };

    // --- GLOBAL STATE ---
    let prescribedMedicines = [];
    let diagnosesSelect, reportsSelect;

    // --- HELPER FUNCTIONS ---
    function showMessage(message) {
        const messageBox = document.getElementById('messageBox');
        document.getElementById('messageText').textContent = message;
        messageBox.classList.remove('hidden');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function clearMedicineFields() {
        document.getElementById('medicine_search').value = '';
        document.getElementById('dosage_frequency').value = '';
        document.getElementById('duration').value = '';
    }

    function renderPrescriptionTable() {
        const prescriptionTableBody = document.getElementById('prescription_table_body');
        prescriptionTableBody.innerHTML = '';
        prescribedMedicines.forEach((med, index) => {
            const row = prescriptionTableBody.insertRow();
            row.innerHTML = `
                <td class="num muted">${index + 1}</td>
                <td class="cell-strong">${med.name}</td>
                <td>${med.dosage}</td>
                <td class="nowrap">${med.duration} days</td>
                <td class="actions">
                    <button type="button" class="btn btn-danger-ghost btn-sm remove-medicine-btn" data-index="${index}">Remove</button>
                </td>
            `;
        });
        prescriptionTableBody.querySelectorAll('.remove-medicine-btn').forEach(button => {
            button.addEventListener('click', (event) => {
                const indexToRemove = parseInt(event.target.dataset.index);
                prescribedMedicines.splice(indexToRemove, 1);
                renderPrescriptionTable();
            });
        });
    }

    function renderMedicineSearchResults(medicines) {
        const medicineSearchResultsBody = document.getElementById('medicine_search_results_body');
        medicineSearchResultsBody.innerHTML = '';
        medicines.forEach(med => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><span class="id">${med.id}</span></td>
                <td class="cell-strong">${med.name}</td>
                <td class="muted">${med.unit}</td>
                <td class="actions">
                    <button type="button" class="btn btn-primary btn-sm select-medicine-btn"
                        data-name="${med.name}">Select</button>
                </td>
            `;
            medicineSearchResultsBody.appendChild(row);
        });
    }

    // --- PRINTING LOGIC ---
    function generatePrintContent() {
        const selectedDiagnoses = diagnosesSelect.getValue();
        const selectedReports = reportsSelect.getValue().map(id => {
            const report = ALL_REPORTS.find(r => r.id === id);
            return report ? report.name : '';
        });

        let content = `
            <div class="p-8 font-sans text-gray-800 leading-relaxed">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold">{{ get_setting('hospital_name', 'Hospital Management System') }}</h1>
                    <p class="text-lg">Dr. Moiz Hassan, M.D. - General Practitioner</p>
                    <p class="text-sm">123 Medical, Pakistan | Phone: (123) 456-7890</p>
                </div>
                <div class="grid grid-cols-2 gap-x-6 border-b pb-4 mb-4">
                    <div>
                        <p><strong>Patient Name:</strong> ${document.getElementById('patient_name').value}</p>
                        <p><strong>MR Number:</strong> ${document.getElementById('mr_number').value}</p>
                    </div>
                    <div class="text-right">
                        <p><strong>Date:</strong> ${document.getElementById('current_date').value}</p>
                        <p><strong>Time:</strong> ${document.getElementById('current_time').value}</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-x-6 border-b pb-4 mb-4">
                    <p><strong>Age:</strong> ${document.getElementById('age').value}</p>
                    <p><strong>Sex:</strong> ${document.getElementById('sex').value}</p>
                    <p><strong>B.P:</strong> ${document.getElementById('bp').value || 'N/A'}</p>
                    <p><strong>Pulse:</strong> ${document.getElementById('pulse').value || 'N/A'}</p>
                    <p><strong>Temp:</strong> ${document.getElementById('temperature').value || 'N/A'}</p>
                    <p><strong>Weight:</strong> ${document.getElementById('weight').value || 'N/A'}</p>
                    <p><strong>Oxygen:</strong> ${document.getElementById('oxygen').value || 'N/A'}</p>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Complaints</h2>
                    <p>${document.getElementById('complaints').value || 'N/A'}</p>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Diagnoses</h2>
                    <p>${selectedDiagnoses.join(', ') || 'N/A'}</p>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Reports</h2>
                    <p>${selectedReports.join(', ') || 'N/A'}</p>
                </div>
                <div class="mb-6">
                    <h2 class="text-xl font-bold border-b pb-1">Rx</h2>
                    <ol class="list-decimal pl-5 space-y-2">
                        ${prescribedMedicines.map((med, index) => `
                            <li>
                                <p><strong>${med.name}</strong> - ${med.dosage}, for ${med.duration} days.</p>
                            </li>
                        `).join('')}
                    </ol>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Notes</h2>
                    <p>${document.getElementById('notes').value || 'N/A'}</p>
                </div>
                <div class="mb-6">
                    <p><strong>Next Visit Date:</strong> ${document.getElementById('next_visit_date').value || 'N/A'}</p>
                </div>
                <div class="text-right mt-16">
                    <p class="font-semibold border-t pt-2">Signature: __________________________</p>
                </div>
            </div>
        `;
        return content;
    }

    function printPrescription() {
        const contentToPrint = generatePrintContent();

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Prescription</title>
                <style>
                    body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; font-size: 14px; }
                    h1, h2, h3 { color: #1f2937; margin: 0; }
                    p { margin: 0; }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .mb-6 { margin-bottom: 1.5rem; }
                    .mb-4 { margin-bottom: 1rem; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mt-16 { margin-top: 4rem; }
                    .border-b { border-bottom: 1px solid #e5e7eb; }
                    .font-bold { font-weight: 700; }
                    .font-semibold { font-weight: 600; }
                    .text-sm { font-size: 0.875rem; }
                    .text-lg { font-size: 1.125rem; }
                    .text-xl { font-size: 1.25rem; }
                    .text-3xl { font-size: 1.875rem; }
                    .grid { display: grid; }
                    .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                    .gap-2 { gap: 0.5rem; }
                    .border-t { border-top: 1px solid #6b7280; }
                    .pt-2 { padding-top: 0.5rem; }
                    .pl-5 { padding-left: 1.25rem; }
                    .space-y-2 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.5rem; }
                </style>
            </head>
            <body>
                ${contentToPrint}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    // --- EVENT LISTENERS ---
    document.addEventListener('DOMContentLoaded', () => {
        const mrNumberInput = document.getElementById('mr_number');
        const patientNameInput = document.getElementById('patient_name');
        const ageInput = document.getElementById('age');
        const sexInput = document.getElementById('sex');
        const historyList = document.getElementById('history_list');
        const searchMedicineBtn = document.getElementById('search_medicine_btn');
        const addMedicineBtn = document.getElementById('add_medicine_btn');
        const medicineGroupsSelect = document.getElementById('medicine_groups');
        const medicineSearchInput = document.getElementById('medicine_search');
        const medicineSearchInputModal = document.getElementById('medicine_search_input_modal');
        const medicineSearchResultsBody = document.getElementById('medicine_search_results_body');
        const saveBtn = document.getElementById('save_btn');
        const printBtn = document.getElementById('print_btn');
        const addDiagnosisBtn = document.getElementById('add_diagnoses_btn');
        const saveNewDiagnosisBtn = document.getElementById('save_new_diagnosis_btn');
        const addReportsBtn = document.getElementById('add_reports_btn');
        const saveNewReportBtn = document.getElementById('save_new_report_btn');
        const addMedicineGroupBtn = document.getElementById('add_medicine_group_btn');
        const addGroupMedicineFieldBtn = document.getElementById('add_group_medicine_field_btn');
        const newGroupMedicinesContainer = document.getElementById('new_group_medicines_container');
        const saveNewGroupBtn = document.getElementById('save_new_group_btn');

        // Initialize Tom Select for diagnoses and reports
        diagnosesSelect = new TomSelect('#diagnoses_select', {
            plugins: ['remove_button'],
            options: ALL_DIAGNOSES.map(d => ({ value: d, text: d })),
            create: true,
        });

        reportsSelect = new TomSelect('#reports_select', {
            plugins: ['remove_button'],
            options: ALL_REPORTS.map(r => ({ value: r.id, text: r.name })),
            render: {
                option: function(data, escape) {
                    return `<div>
                        <span>${escape(data.text)}</span>
                        <button type="button" class="ml-2 text-[12.5px] font-semibold text-brand-700 hover:underline view-report-btn" data-report-id="${escape(data.value)}">View</button>
                    </div>`;
                }
            }
        });

        // Patient data lookup simulation
        mrNumberInput.addEventListener('input', (e) => {
            const mrNumber = e.target.value.toUpperCase();
            const patient = ALL_PATIENT_DATA[mrNumber];

            if (patient) {
                patientNameInput.value = patient.name;
                ageInput.value = patient.age;
                sexInput.value = patient.sex;
                historyList.innerHTML = '';

                patient.visits.forEach((visit, index) => {
                    const historyItem = document.createElement('div');
                    historyItem.className = "flex items-center justify-between gap-3 rounded-lg border border-line bg-white px-3 py-2.5";
                    historyItem.innerHTML = `
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-fg">Visit on ${visit.date}</p>
                            <p class="max-w-xl truncate text-[12.5px] text-fg-muted">${visit.notes}</p>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm view-history-btn" data-index="${index}" data-mr-number="${mrNumber}">View details</button>
                    `;
                    historyList.appendChild(historyItem);
                });
            } else {
                patientNameInput.value = '';
                ageInput.value = '';
                sexInput.value = '';
                historyList.innerHTML = `<p class="text-[13px] text-fg-muted">No patient found with this MR Number.</p>`;
            }
        });

        // View History Details Modal (Event delegation)
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.view-history-btn')) {
                const index = parseInt(e.target.dataset.index);
                const mrNumber = e.target.dataset.mrNumber;
                const visit = ALL_PATIENT_DATA[mrNumber].visits[index];

                const historyModalContent = document.getElementById('history_modal_content');
                const historyHtml = `
                    <dl class="kv">
                        <dt>Date</dt><dd>${visit.date}</dd>
                        <dt>Complaints</dt><dd>${visit.complaints || 'N/A'}</dd>
                        <dt>Notes</dt><dd>${visit.notes}</dd>
                        <dt>Vitals</dt><dd>B.P. ${visit.vitals.bp} · Pulse ${visit.vitals.pulse} · O2 ${visit.vitals.oxygen}% · Temp ${visit.vitals.temp}°F · Weight ${visit.vitals.weight} kg</dd>
                        <dt>Diagnoses</dt><dd>${visit.diagnoses.join(', ') || 'N/A'}</dd>
                        <dt>Reports</dt><dd>${visit.reports.join(', ') || 'N/A'}</dd>
                        <dt>Prescription</dt><dd><ul class="list-disc pl-4">${visit.medicines.map(med => `<li>${med.name} - ${med.dosage}</li>`).join('')}</ul></dd>
                    </dl>
                `;
                historyModalContent.innerHTML = historyHtml;
                openModal('historyModal');
            }
        });

        // View Report Details Modal (Event delegation)
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.view-report-btn')) {
                const reportId = e.target.dataset.reportId;
                const report = ALL_REPORTS.find(r => r.id === reportId);
                const reportContent = document.getElementById('report_content');
                if (report) {
                    reportContent.innerHTML = `<h4 class="section-heading">${report.name}</h4><pre class="whitespace-pre-wrap font-sans text-[13px] leading-relaxed">${report.content}</pre>`;
                    openModal('reportModal');
                }
            }
        });

        // Add Diagnosis button
        addDiagnosisBtn.addEventListener('click', () => {
            openModal('addDiagnosisModal');
        });

        // Save new diagnosis
        saveNewDiagnosisBtn.addEventListener('click', () => {
            const newDiagnosisName = document.getElementById('new_diagnosis_name').value.trim();
            if (newDiagnosisName) {
                if (!ALL_DIAGNOSES.includes(newDiagnosisName)) {
                    ALL_DIAGNOSES.push(newDiagnosisName);
                    diagnosesSelect.addOption({ value: newDiagnosisName, text: newDiagnosisName });
                    showMessage(`Diagnosis "${newDiagnosisName}" added.`);
                } else {
                    showMessage(`Diagnosis "${newDiagnosisName}" already exists.`);
                }
                document.getElementById('new_diagnosis_name').value = '';
                closeModal('addDiagnosisModal');
            } else {
                showMessage('Please enter a diagnosis name.');
            }
        });

        // Add Report button
        addReportsBtn.addEventListener('click', () => {
            openModal('addReportModal');
        });

        // Save new report
        saveNewReportBtn.addEventListener('click', () => {
            const newReportName = document.getElementById('new_report_name').value.trim();
            if (newReportName) {
                const newReportId = 'RPT' + Math.floor(Math.random() * 10000);
                const newReport = { id: newReportId, name: newReportName, content: `Simulated report content for ${newReportName}.` };
                ALL_REPORTS.push(newReport);
                reportsSelect.addOption({ value: newReportId, text: newReportName });
                showMessage(`Report type "${newReportName}" added.`);
                document.getElementById('new_report_name').value = '';
                closeModal('addReportModal');
            } else {
                showMessage('Please enter a report name.');
            }
        });

        // Add Medicine Group button
        addMedicineGroupBtn.addEventListener('click', () => {
            // Clear existing fields
            newGroupMedicinesContainer.innerHTML = '';
            document.getElementById('new_group_name').value = '';
            // Add one empty field by default
            addGroupMedicineFieldBtn.click();
            openModal('addMedicineGroupModal');
        });

        // Add medicine field to the group creation modal
        addGroupMedicineFieldBtn.addEventListener('click', () => {
            const index = newGroupMedicinesContainer.children.length;
            const newField = document.createElement('div');
            newField.className = "flex items-center space-x-2";
            newField.innerHTML = `
                <div class="field flex-1">
                    <label class="field-label">Medicine name</label>
                    <input type="text" name="group_medicine_name[]" class="input" placeholder="e.g. Paracetamol">
                </div>
                <div class="field flex-1">
                    <label class="field-label">Dosage & frequency</label>
                    <input type="text" name="group_dosage_frequency[]" class="input" placeholder="e.g. 1 tablet twice a day">
                </div>
                <div class="field w-32">
                    <label class="field-label">Duration (days)</label>
                    <input type="number" name="group_duration[]" class="input input-num" placeholder="7">
                </div>
                <button type="button" class="btn btn-danger-ghost btn-icon self-end remove-group-medicine-field" aria-label="Remove medicine">
                    <svg class="lc-icon pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </button>
            `;
            newGroupMedicinesContainer.appendChild(newField);
        });

        // Remove medicine field from the group creation modal
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.remove-group-medicine-field')) {
                e.target.closest('.flex.items-center.space-x-2').remove();
            }
        });

        // Save new medicine group
        saveNewGroupBtn.addEventListener('click', () => {
            const newGroupName = document.getElementById('new_group_name').value.trim();
            const medicineNames = Array.from(document.querySelectorAll('input[name="group_medicine_name[]"]')).map(el => el.value);
            const dosages = Array.from(document.querySelectorAll('input[name="group_dosage_frequency[]"]')).map(el => el.value);
            const durations = Array.from(document.querySelectorAll('input[name="group_duration[]"]')).map(el => el.value);

            if (!newGroupName) {
                showMessage('Please enter a group name.');
                return;
            }

            if (medicineNames.every(name => !name)) {
                showMessage('Please add at least one medicine to the group.');
                return;
            }

            const newGroupKey = 'group_' + newGroupName.toLowerCase().replace(/\s/g, '_');
            const newGroupMedicines = [];
            for (let i = 0; i < medicineNames.length; i++) {
                if (medicineNames[i] && dosages[i] && durations[i]) {
                    newGroupMedicines.push({
                        name: medicineNames[i],
                        dosage: dosages[i],
                        duration: parseInt(durations[i])
                    });
                }
            }

            if (newGroupMedicines.length > 0) {
                MEDICINE_GROUPS[newGroupKey] = newGroupMedicines;
                const medicineGroupsSelect = document.getElementById('medicine_groups');
                const newOption = new Option(newGroupName, newGroupKey);
                medicineGroupsSelect.add(newOption);
                showMessage(`New group "${newGroupName}" saved successfully!`);
                closeModal('addMedicineGroupModal');
            } else {
                showMessage('Please fill out all fields for the medicines in the group.');
            }
        });

        // Medicine search modal
        searchMedicineBtn.addEventListener('click', () => {
            medicineSearchInputModal.value = '';
            renderMedicineSearchResults(ALL_MEDICINES);
            openModal('medicineSearchModal');
        });

        medicineSearchInputModal.addEventListener('keyup', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredMedicines = ALL_MEDICINES.filter(med =>
                med.name.toLowerCase().includes(searchTerm)
            );
            renderMedicineSearchResults(filteredMedicines);
        });

        // Select a medicine from the modal
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.select-medicine-btn')) {
                const medicineName = e.target.dataset.name;
                document.getElementById('medicine_search').value = medicineName;
                closeModal('medicineSearchModal');
            }
        });

        // Add medicine to table logic
        addMedicineBtn.addEventListener('click', () => {
            const medicineName = medicineSearchInput.value;
            const dosageFrequency = document.getElementById('dosage_frequency').value;
            const duration = document.getElementById('duration').value;
            if (medicineName && dosageFrequency && duration) {
                const newMed = { name: medicineName, dosage: dosageFrequency, duration };
                prescribedMedicines.push(newMed);
                renderPrescriptionTable();
                clearMedicineFields();
            } else {
                showMessage('Please fill out all prescription details (Medicine, Dosage & Frequency, Duration).');
            }
        });

        // Medicine group selection
        medicineGroupsSelect.addEventListener('change', (e) => {
            const selectedGroup = e.target.value;
            if (selectedGroup && MEDICINE_GROUPS[selectedGroup]) {
                const medsToAdd = MEDICINE_GROUPS[selectedGroup];
                prescribedMedicines = [...prescribedMedicines, ...medsToAdd];
                renderPrescriptionTable();
                clearMedicineFields();
                showMessage(`Added ${medsToAdd.length} medicine(s) from the "${selectedGroup.replace('group_', '').replace(/_/g, ' ')}" group.`);
            }
        });

        // Save and Print buttons
        saveBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showMessage('Prescription saved successfully! (Simulated)');
        });

        printBtn.addEventListener('click', (e) => {
            e.preventDefault();
            printPrescription();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('save_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                document.getElementById('print_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
                e.preventDefault();
                document.getElementById('search_medicine_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                document.getElementById('add_diagnoses_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                document.getElementById('add_reports_btn').click();
            }
        });

        // Initial load for date/time fields
        document.getElementById('current_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('current_time').value = new Date().toLocaleTimeString('en-US', {hour12: false, hour: '2-digit', minute: '2-digit'});
    });
</script>
@endsection
