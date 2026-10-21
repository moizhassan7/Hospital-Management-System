@extends('layouts.app')

@section('title', 'Indoor patient registration')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Indoor patient registration" subtitle="Admit a registered patient to a ward or private room and record the admission charges.">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
            <a href="{{ route('patients.register') }}" class="btn btn-secondary">
                <x-ui.icon name="user-plus" />Register new patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form id="indoor_patient_form" action="{{ route('patients.store_indoor') }}" method="POST">
        @csrf
        <input type="hidden" id="is_welfare" name="is_welfare" value="0">

        <div class="workspace">
            <div class="stack">
                {{-- A · Patient identification --}}
                <x-ui.panel title="Patient identification" description="Load the patient by MR number. Their details fill in automatically." icon="user" flush>
                    <div class="panel-body space-y-4">
                        <div class="form-grid">
                            <x-ui.field label="MR number" for="mr_no" hint="Press Enter to load. Ctrl + F searches by name, CNIC or mobile." class="md:col-span-7 xl:col-span-6">
                                <div class="flex gap-2">
                                    <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001" autocomplete="off">
                                    <button type="button" id="open_patient_search" class="btn btn-secondary">
                                        <x-ui.icon name="search" />Find patient
                                    </button>
                                </div>
                            </x-ui.field>
                            <x-ui.field label="Registration date" for="registration_date" required class="md:col-span-5 xl:col-span-3 xl:col-start-10">
                                <input type="date" id="registration_date" name="registration_date" class="input" value="{{ date('Y-m-d') }}" required>
                            </x-ui.field>
                        </div>

                        <div class="patient-band shadow-none" role="group" aria-label="Loaded patient">
                            <div class="band-cell band-cell-grow">
                                <label for="patient_name" class="band-label">Patient name <span class="field-tag ml-0">Auto</span></label>
                                <input type="text" id="patient_name" name="patient_name" class="band-input" placeholder="No patient loaded yet" readonly>
                            </div>
                            <div class="band-cell w-24">
                                <label for="age" class="band-label">Age</label>
                                <input type="number" id="age" name="age" class="band-input" placeholder="—" readonly>
                            </div>
                            <div class="band-cell w-28">
                                <label for="gender" class="band-label">Gender</label>
                                <input type="text" id="gender" name="gender" class="band-input" placeholder="—" readonly>
                            </div>
                            <div class="band-cell w-32">
                                <label for="patient_type" class="band-label">Patient type</label>
                                <input type="text" id="patient_type" name="patient_type" class="band-input" placeholder="—" readonly>
                            </div>
                        </div>
                    </div>
                </x-ui.panel>

                {{-- B · Admission details --}}
                <x-ui.panel title="Admission details" description="Where the patient will stay and who is responsible for their care." icon="bed" flush>
                    <x-ui.form-section>
                        <div class="form-grid">
                            <x-ui.field label="Admission type" class="md:col-span-12">
                                <div class="segmented" role="radiogroup" aria-label="Admission type">
                                    <label class="segmented-option">
                                        <input type="radio" name="admission_type" id="ward_radio" value="ward" checked>
                                        <span><x-ui.icon name="users" />Ward</span>
                                    </label>
                                    <label class="segmented-option">
                                        <input type="radio" name="admission_type" id="room_radio" value="room">
                                        <span><x-ui.icon name="door" />Private room</span>
                                    </label>
                                </div>
                            </x-ui.field>

                            <div id="ward_section" class="md:col-span-6 xl:col-span-5">
                                <x-ui.field label="Ward" for="ward_number">
                                    <select id="ward_number" name="ward_number" class="select">
                                        <option value="">Select ward</option>
                                        @foreach ($wards as $ward)
                                            <option value="{{ $ward->id }}" data-beds="{{ $ward->number_of_beds }}">{{ $ward->name }} ({{ $ward->number_of_beds }} beds)</option>
                                        @endforeach
                                    </select>
                                </x-ui.field>
                            </div>

                            <div id="room_section" class="hidden md:col-span-6 xl:col-span-5">
                                <x-ui.field label="Room" for="room_id">
                                    <select id="room_id" name="room_id" class="select">
                                        <option value="">Select room</option>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </x-ui.field>
                            </div>

                            <x-ui.field label="Number of beds" label-id="bed_info_label" for="bed_info" class="md:col-span-6 xl:col-span-3">
                                <input type="text" id="bed_info" name="bed_info" class="input" placeholder="—" readonly>
                                <input type="hidden" name="bed_no" id="bed_no">
                            </x-ui.field>
                        </div>
                    </x-ui.form-section>

                    <x-ui.form-section>
                        <div class="form-grid">
                            <x-ui.field label="Consultant" for="consultant_input" hint="Press Enter or use Find to choose the consultant." class="md:col-span-12 xl:col-span-8">
                                <div class="flex gap-2">
                                    <div class="input-group has-start flex-1">
                                        <span class="input-affix is-start"><x-ui.icon name="stethoscope" /></span>
                                        <input type="text" id="consultant_input" class="input" placeholder="Consultant name" autocomplete="off">
                                    </div>
                                    <button type="button" id="open_consultant_search" class="btn btn-secondary">
                                        <x-ui.icon name="search" />Find
                                    </button>
                                </div>
                                <input type="hidden" name="consultant_id" id="consultant_id">
                            </x-ui.field>
                        </div>
                    </x-ui.form-section>

                    <x-ui.form-section>
                        <div class="form-grid">
                            <x-ui.field label="File no." for="file_no" tag="Auto" class="md:col-span-6 xl:col-span-4">
                                <input type="text" id="file_no" name="file_no" class="input input-mono" placeholder="Generated after lookup" readonly>
                            </x-ui.field>
                            <x-ui.field label="Slip no." for="slip_no" tag="Auto" class="md:col-span-6 xl:col-span-4">
                                <input type="text" id="slip_no" name="slip_no" class="input input-mono" placeholder="Generated after lookup" readonly>
                            </x-ui.field>
                        </div>
                    </x-ui.form-section>
                </x-ui.panel>

                {{-- C · Procedure --}}
                <x-ui.panel title="Procedure" icon="scissors" flush>
                    <div class="panel-body">
                        <div class="option-row flex-wrap">
                            <label for="is_operation" class="flex min-w-0 flex-1 items-center gap-3">
                                <input type="checkbox" name="is_operation" id="is_operation" value="1" class="switch">
                                <span>
                                    <span class="option-row-title block">Is operation?</span>
                                    <span class="option-row-desc block">Turn on if this admission is for an operation.</span>
                                </span>
                            </label>
                            <div id="operation_date_field" class="hidden w-full sm:w-56">
                                <x-ui.field label="Operation date" for="operation_date" hint="Optional">
                                    <input type="date" id="operation_date" name="operation_date" class="input">
                                </x-ui.field>
                            </div>
                        </div>
                    </div>
                </x-ui.panel>
            </div>

            {{-- D · Financial summary + E · Actions --}}
            <aside class="workspace-rail">
                <x-ui.panel title="Admission charges" description="Total = admission fee + consultant fee − advance." icon="receipt" flush>
                    <div class="panel-body pt-3">
                        <div class="ledger">
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">+</span>
                                <label for="admission_fee" class="ledger-label">Admission fee</label>
                                <div class="input-group has-start">
                                    <span class="input-affix is-start">{{ $currency }}</span>
                                    <input type="number" id="admission_fee" name="admission_fee" class="input input-money" placeholder="0.00" min="0" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">+</span>
                                <label for="consultant_fee" class="ledger-label">Consultant fee <small>Set by consultant</small></label>
                                <div class="input-group has-start">
                                    <span class="input-affix is-start">{{ $currency }}</span>
                                    <input type="number" id="consultant_fee" name="consultant_fee" class="input input-money" placeholder="0.00" readonly>
                                </div>
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">−</span>
                                <label for="advance_fee" class="ledger-label">Advance fee</label>
                                <div class="input-group has-start">
                                    <span class="input-affix is-start">{{ $currency }}</span>
                                    <input type="number" id="advance_fee" name="advance_fee" class="input input-money" placeholder="0.00" min="0" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="ledger-row ledger-total">
                                <span class="ledger-op" aria-hidden="true">=</span>
                                <label for="total_amount" class="ledger-label">Total amount</label>
                                <div class="input-group has-start">
                                    <span class="input-affix is-start">{{ $currency }}</span>
                                    <input type="text" id="total_amount" name="total_amount" class="input input-money input-total" placeholder="0.00" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <x-slot:footer class="flex-col items-stretch">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" data-busy-label="Registering…">
                            <x-ui.icon name="check" />Register indoor patient
                        </button>
                        <a href="{{ route('patients.index') }}" class="btn btn-ghost btn-block">Cancel</a>
                    </x-slot:footer>
                </x-ui.panel>
            </aside>
        </div>
    </form>

    <x-ui.dialog id="patient_search_modal" title="Find patient" description="Search by MR number, name, CNIC or mobile number. Use ↑ ↓ and Enter to choose." size="lg" icon="search">
        <div class="input-group has-start mb-3">
            <span class="input-affix is-start"><x-ui.icon name="search" /></span>
            <input type="text" id="patient_search_query" class="input" placeholder="MR no., name, CNIC or mobile" autocomplete="off">
        </div>
        <div id="patient_search_results" class="result-list"></div>
        <x-slot:footer>
            <button type="button" id="close_patient_modal" class="btn btn-secondary" data-dialog-close>Close</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="consultant_search_modal" title="Find consultant" description="Search by code, name or department. Use ↑ ↓ and Enter to choose." size="lg" icon="stethoscope">
        <div class="input-group has-start mb-3">
            <span class="input-affix is-start"><x-ui.icon name="search" /></span>
            <input type="text" id="consultant_search_query" class="input" placeholder="Code, name or department" autocomplete="off">
        </div>
        <div id="consultant_search_results" class="result-list"></div>
        <x-slot:footer>
            <button type="button" id="close_consultant_modal" class="btn btn-secondary" data-dialog-close>Close</button>
        </x-slot:footer>
    </x-ui.dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get Elements
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const ageInput = document.getElementById('age');
            const genderInput = document.getElementById('gender');
            const patientTypeInput = document.getElementById('patient_type');
            const isWelfareInput = document.getElementById('is_welfare');
            const fileNoInput = document.getElementById('file_no');
            const slipNoInput = document.getElementById('slip_no');
            const wardRadio = document.getElementById('ward_radio');
            const roomRadio = document.getElementById('room_radio');
            const wardSection = document.getElementById('ward_section');
            const roomSection = document.getElementById('room_section');
            const wardNumberSelect = document.getElementById('ward_number');
            const roomNumberSelect = document.getElementById('room_id');
            const bedInfoInput = document.getElementById('bed_info');
            const bedNoLabel = document.getElementById('bed_info_label');
            const isOperationCheckbox = document.getElementById('is_operation');
            const operationDateField = document.getElementById('operation_date_field');
            const admissionFeeInput = document.getElementById('admission_fee');
            const advanceFeeInput = document.getElementById('advance_fee');
            const totalAmountInput = document.getElementById('total_amount');
            const consultantInput = document.getElementById('consultant_input');
            const consultantIdInput = document.getElementById('consultant_id');
            const consultantFeeInput = document.getElementById('consultant_fee');

            // Patient Search Modal Elements
            const patientSearchModal = document.getElementById('patient_search_modal');
            const patientSearchQueryInput = document.getElementById('patient_search_query');
            const patientSearchResults = document.getElementById('patient_search_results');
            const closePatientModalBtn = document.getElementById('close_patient_modal');

            // Consultant Search Modal Elements
            const consultantSearchModal = document.getElementById('consultant_search_modal');
            const consultantSearchQueryInput = document.getElementById('consultant_search_query');
            const consultantSearchResults = document.getElementById('consultant_search_results');
            const closeConsultantModalBtn = document.getElementById('close_consultant_modal');

            // Debounce function to prevent excessive API calls
            function debounce(func, delay) {
                let timeout;
                return function(...args) {
                    const context = this;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            }

            // --- Auto Number Generation ---
            function generateFileAndSlipNumber(gender) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const genderCode = (gender && gender.toLowerCase() === 'male') ? '1' : '0';

                const formattedNextNumber = '{{ $formattedNextNumber }}';

                const fileNumber = `${year}${month}${genderCode}${formattedNextNumber}`;
                const slipNumber = `${genderCode}${year}${month}${formattedNextNumber}`;

                fileNoInput.value = fileNumber;
                slipNoInput.value = slipNumber;
            }

            // --- AJAX Helpers ---
            async function fetchData(url) {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            // --- Patient and Consultant Search Logic ---

            // Global shortcut listener
            document.addEventListener('keydown', function(event) {
                if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
                    event.preventDefault();
                    patientSearchModal.classList.remove('hidden');
                    // Use setTimeout to ensure focus is applied after the modal has been rendered
                    setTimeout(() => patientSearchQueryInput.focus(), 100);
                    fetchAndDisplayPatients('');
                }
            });

            document.getElementById('open_patient_search').addEventListener('click', async function() {
                patientSearchModal.classList.remove('hidden');
                setTimeout(() => patientSearchQueryInput.focus(), 100);
                await fetchAndDisplayPatients(patientSearchQueryInput.value);
            });

            // Patient search modal logic
            patientSearchQueryInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                await fetchAndDisplayPatients(query);
            }, 300));

            // Keyboard navigation for patient search results
            patientSearchModal.addEventListener('keydown', function(event) {
                const results = patientSearchResults.querySelectorAll('.search-result-row');
                let activeIndex = -1;
                results.forEach((el, index) => {
                    if (el.classList.contains('bg-blue-100')) {
                        activeIndex = index;
                    }
                });

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    const nextIndex = (activeIndex + 1) % results.length;
                    results.forEach(el => el.classList.remove('bg-blue-100'));
                    results[nextIndex].classList.add('bg-blue-100');
                    results[nextIndex].focus();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    const prevIndex = (activeIndex - 1 + results.length) % results.length;
                    results.forEach(el => el.classList.remove('bg-blue-100'));
                    results[prevIndex].classList.add('bg-blue-100');
                    results[prevIndex].focus();
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    const selectedItem = patientSearchResults.querySelector('.bg-blue-100');
                    if (selectedItem) {
                        selectedItem.click();
                    }
                } else if (event.key === 'Escape') {
                    patientSearchModal.classList.add('hidden');
                }
            });

            async function fetchAndDisplayPatients(query) {
                const url = `{{ route('patients.api_search_patient') }}?query=${query}`;
                try {
                    const patients = await fetchData(url);
                    let tableHtml = '<table class="table table-compact">';
                    tableHtml += '<thead><tr><th>Name</th><th>MR No</th><th>CNIC</th><th>Mobile No</th></tr></thead>';
                    tableHtml += '<tbody>';
                    if (patients.length > 0) {
                        patients.forEach(patient => {
                            tableHtml += `
                                <tr class="search-result-row" data-mr-no="${patient.mr_number}" tabindex="0">
                                    <td class="cell-strong">${patient.name}</td>
                                    <td><span class="id">${patient.mr_number}</span></td>
                                    <td class="muted">${patient.cnic || 'N/A'}</td>
                                    <td class="muted">${patient.mobile_number || 'N/A'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableHtml += `<tr><td colspan="4" class="table-empty">No patients found.</td></tr>`;
                    }
                    tableHtml += '</tbody></table>';
                    patientSearchResults.innerHTML = tableHtml;

                    patientSearchResults.querySelectorAll('.search-result-row').forEach(row => {
                        row.addEventListener('click', () => {
                            const mrNumber = row.getAttribute('data-mr-no');
                            mrNoInput.value = mrNumber;
                            populatePatientFields(patients.find(p => p.mr_number === mrNumber));
                            patientSearchModal.classList.add('hidden');
                        });
                    });

                    // Do not auto-focus the first row. The user should start typing.
                    // The focus will be handled by setTimeout in the keydown listener.
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="result-message is-error">Failed to fetch patient data.</div>';
                }
            }

            closePatientModalBtn.addEventListener('click', () => {
                patientSearchModal.classList.add('hidden');
            });

            mrNoInput.addEventListener('keydown', async function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.value === '') {
                        patientSearchModal.classList.remove('hidden');
                        setTimeout(() => patientSearchQueryInput.focus(), 100);
                        await fetchAndDisplayPatients('');
                    } else {
                        await fetchAndPopulatePatientDetails(this.value);
                    }
                }
            });

            async function fetchAndPopulatePatientDetails(mrNo) {
                try {
                    const response = await fetch(`/api/get-by-mr-no/${mrNo}`);
                    const patient = await response.json();
                    if (patient) {
                        populatePatientFields(patient);
                    } else {
                        alert('No patient found with this MR No.');
                        clearPatientFields();
                    }
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    alert('Failed to fetch patient data. Please try again.');
                }
            }

            function populatePatientFields(patient) {
                patientNameInput.value = patient.name;
                ageInput.value = patient.age;
                genderInput.value = patient.gender;
                patientTypeInput.value = patient.is_welfare ? 'Welfare' : 'Normal';
                isWelfareInput.value = patient.is_welfare ? '1' : '0';
                generateFileAndSlipNumber(patient.gender);
                updateDoctorFee(); // Update fee based on patient type
            }

            function clearPatientFields() {
                patientNameInput.value = '';
                ageInput.value = '';
                genderInput.value = '';
                patientTypeInput.value = '';
                isWelfareInput.value = '0';
                fileNoInput.value = '';
                slipNoInput.value = '';
                updateDoctorFee(); // Recalculate fee to default
            }

            // Consultant search modal logic
            consultantInput.addEventListener('keydown', async function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    consultantSearchModal.classList.remove('hidden');
                    setTimeout(() => consultantSearchQueryInput.focus(), 100);
                    await fetchAndDisplayConsultants('');
                }
            });

            document.getElementById('open_consultant_search').addEventListener('click', async function() {
                consultantSearchModal.classList.remove('hidden');
                setTimeout(() => consultantSearchQueryInput.focus(), 100);
                await fetchAndDisplayConsultants(consultantSearchQueryInput.value);
            });

            consultantSearchQueryInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                await fetchAndDisplayConsultants(query);
            }, 300));

            // Keyboard navigation for consultant search results
            consultantSearchModal.addEventListener('keydown', function(event) {
                const results = consultantSearchResults.querySelectorAll('.search-result-row');
                let activeIndex = -1;
                results.forEach((el, index) => {
                    if (el.classList.contains('bg-blue-100')) {
                        activeIndex = index;
                    }
                });

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    const nextIndex = (activeIndex + 1) % results.length;
                    results.forEach(el => el.classList.remove('bg-blue-100'));
                    results[nextIndex].classList.add('bg-blue-100');
                    results[nextIndex].focus();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    const prevIndex = (activeIndex - 1 + results.length) % results.length;
                    results.forEach(el => el.classList.remove('bg-blue-100'));
                    results[prevIndex].classList.add('bg-blue-100');
                    results[prevIndex].focus();
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    const selectedItem = consultantSearchResults.querySelector('.bg-blue-100');
                    if (selectedItem) {
                        selectedItem.click();
                    }
                } else if (event.key === 'Escape') {
                    consultantSearchModal.classList.add('hidden');
                }
            });

            async function fetchAndDisplayConsultants(query) {
                const url = `{{ route('patients.api_search_doctor') }}?query=${query}`;
                try {
                    const doctors = await fetchData(url);
                    let tableHtml = '<table class="table table-compact">';
                    tableHtml += '<thead><tr><th>Code</th><th>Name</th><th>Department</th><th class="num">Fee</th></tr></thead>';
                    tableHtml += '<tbody>';

                    if (doctors.length > 0) {
                        doctors.forEach(doctor => {
                            // Determine which fee to display
                            const isWelfare = isWelfareInput.value === '1';
                            const fee = isWelfare ? (doctor.welfare_normal_fee || '0.00') : (doctor.general_normal_fee || '0.00');
                            tableHtml += `
                                <tr class="search-result-row" data-doctor-id="${doctor.id}" data-doctor-fee="${fee}" tabindex="0">
                                    <td><span class="id">${doctor.code}</span></td>
                                    <td class="cell-strong">${doctor.name}</td>
                                    <td class="muted">${doctor.department.name || 'N/A'}</td>
                                    <td class="num">{{ get_setting('currency_symbol', 'Rs') }}${fee}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableHtml += `<tr><td colspan="4" class="table-empty">No consultants found.</td></tr>`;
                    }
                    tableHtml += '</tbody></table>';
                    consultantSearchResults.innerHTML = tableHtml;

                    consultantSearchResults.querySelectorAll('.search-result-row').forEach(row => {
                        row.addEventListener('click', () => {
                            const doctorId = row.getAttribute('data-doctor-id');
                            const doctorName = row.querySelector('td:nth-child(2)').textContent;
                            const doctorFee = row.getAttribute('data-doctor-fee');

                            consultantInput.value = doctorName;
                            consultantIdInput.value = doctorId;
                            consultantFeeInput.value = parseFloat(doctorFee).toFixed(2);
                            calculateTotal();

                            consultantSearchModal.classList.add('hidden');
                        });
                    });

                    // Do not auto-focus the first row. The user should start typing.
                    // The focus will be handled by setTimeout in the keydown listener.
                } catch (error) {
                    console.error('Error searching for consultants:', error);
                    consultantSearchResults.innerHTML = '<div class="result-message is-error">Failed to load search results.</div>';
                }
            }

            closeConsultantModalBtn.addEventListener('click', () => {
                consultantSearchModal.classList.add('hidden');
            });

            // --- Admission Type & Bed Logic ---
            wardRadio.addEventListener('change', () => {
                wardSection.classList.remove('hidden');
                roomSection.classList.add('hidden');
                bedNoLabel.textContent = 'Number of beds';
                bedInfoInput.value = '';
                bedInfoInput.readOnly = true;
            });

            roomRadio.addEventListener('change', () => {
                roomSection.classList.remove('hidden');
                wardSection.classList.add('hidden');
                bedNoLabel.textContent = 'Bed no.';
                bedInfoInput.value = '';
                bedInfoInput.readOnly = false;
            });

            wardNumberSelect.addEventListener('change', () => {
                const selectedOption = wardNumberSelect.options[wardNumberSelect.selectedIndex];
                const beds = selectedOption.getAttribute('data-beds');
                if (beds) {
                    bedInfoInput.value = beds;
                    bedInfoInput.readOnly = true;
                } else {
                    bedInfoInput.value = '';
                    bedInfoInput.readOnly = true;
                }
            });

            roomNumberSelect.addEventListener('change', () => {
                const selectedRoomId = roomNumberSelect.value;
                if (selectedRoomId) {
                    bedInfoInput.value = 'Room ' + selectedRoomId;
                } else {
                    bedInfoInput.value = '';
                }
                bedInfoInput.readOnly = true;
            });

            // To handle the hidden bed_no field that will be submitted
            const indoorForm = document.getElementById('indoor_patient_form');
            indoorForm.addEventListener('submit', function(event) {
                if (wardRadio.checked) {
                    const selectedWard = wardNumberSelect.options[wardNumberSelect.selectedIndex];
                    const bedNumber = selectedWard.getAttribute('data-beds');
                    document.getElementById('bed_no').value = bedNumber;
                } else {
                    const bedNumber = bedInfoInput.value;
                    document.getElementById('bed_no').value = bedNumber;
                }
            });

            // --- Operation Date & Fee Calculation ---
            isOperationCheckbox.addEventListener('change', () => {
                if (isOperationCheckbox.checked) {
                    operationDateField.classList.remove('hidden');
                } else {
                    operationDateField.classList.add('hidden');
                    document.getElementById('operation_date').value = '';
                }
            });

            function calculateTotal() {
                const admission = parseFloat(admissionFeeInput.value) || 0;
                const advance = parseFloat(advanceFeeInput.value) || 0;
                const consultantFee = parseFloat(consultantFeeInput.value) || 0;
                const total = admission + consultantFee - advance;
                totalAmountInput.value = total.toFixed(2);
            }

            function updateDoctorFee() {
                const doctorId = consultantIdInput.value;
                const isWelfare = isWelfareInput.value === '1';

                if (doctorId) {
                    const url = `{{ route('patients.api_get_doctor_fee') }}?consultant_id=${doctorId}&is_welfare=${isWelfare}`;
                    fetchData(url)
                        .then(data => {
                            consultantFeeInput.value = parseFloat(data.fee).toFixed(2);
                            calculateTotal();
                        })
                        .catch(error => {
                            console.error('Error fetching doctor fee:', error);
                            consultantFeeInput.value = '0.00';
                            calculateTotal();
                        });
                } else {
                    consultantFeeInput.value = '0.00';
                    calculateTotal();
                }
            }

            consultantIdInput.addEventListener('change', updateDoctorFee);
            admissionFeeInput.addEventListener('input', calculateTotal);
            advanceFeeInput.addEventListener('input', calculateTotal);
            consultantFeeInput.addEventListener('input', calculateTotal); // Recalculate if fee is manually changed

            // Initial setup
            calculateTotal();
            wardRadio.checked = true;
            wardSection.classList.remove('hidden');
            roomSection.classList.add('hidden');
            bedNoLabel.textContent = 'Number of beds';
            bedInfoInput.readOnly = true;
        });
    </script>
@endsection
