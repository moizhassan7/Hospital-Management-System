<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get Elements
        const mrNumberInput = document.getElementById('mr_number');
        const procedureIdInput = document.getElementById('procedure_id');
        const patientNameInput = document.getElementById('patient_name');
        const ageInput = document.getElementById('age');
        const genderInput = document.getElementById('gender');
        const departmentSelect = document.getElementById('department_id');
        const consultantInput = document.getElementById('consultant_input');
        const consultantIdInput = document.getElementById('consultant_id');
        const isAnesthesiaCheckbox = document.getElementById('is_anesthesia');
        const anesthesiaSection = document.getElementById('anesthesia_section');
        const patientSearchModal = document.getElementById('patient_search_modal');
        const patientSearchQueryInput = document.getElementById('patient_search_query');
        const patientSearchResults = document.getElementById('patient_search_results');
        const closePatientModalBtn = document.getElementById('close_patient_modal');
        const consultantSearchModal = document.getElementById('consultant_search_modal');
        const consultantSearchQueryInput = document.getElementById('consultant_search_query');
        const consultantSearchResults = document.getElementById('consultant_search_results');
        const closeConsultantModalBtn = document.getElementById('close_consultant_modal');
        
        let currentActiveRow = -1;

        // Patient Search Logic
        mrNumberInput.addEventListener('keydown', async function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                if (this.value === '') {
                    patientSearchModal.classList.remove('hidden');
                    patientSearchQueryInput.focus();
                    await fetchAndDisplayPatients('');
                } else {
                    await fetchAndPopulatePatientDetails(this.value);
                }
            }
        });

        patientSearchQueryInput.addEventListener('input', async function() {
            const query = this.value;
            await fetchAndDisplayPatients(query);
        });

        async function fetchAndDisplayPatients(query) {
            const url = `{{ route('patients.api_search_patient') }}?query=${query}`;
            try {
                const response = await fetch(url);
                const patients = await response.json();
                patientSearchResults.innerHTML = '';
                if (patients.length > 0) {
                    const table = document.createElement('table');
                    table.className = 'min-w-full divide-y divide-gray-200';
                    table.innerHTML = `
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MR No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNIC</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="patient-search-table-body">
                        </tbody>
                    `;
                    patientSearchResults.appendChild(table);
                    const tbody = document.getElementById('patient-search-table-body');
                    patients.forEach(patient => {
                        const row = document.createElement('tr');
                        row.className = 'patient-row hover:bg-gray-100 cursor-pointer';
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${patient.mr_number}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${patient.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${patient.cnic || 'N/A'}</td>
                        `;
                        row.dataset.mr_number = patient.mr_number;
                        row.dataset.name = patient.name;
                        row.dataset.age = patient.age;
                        row.dataset.gender = patient.gender;
                        row.onclick = () => {
                            populatePatientFields(row.dataset);
                            patientSearchModal.classList.add('hidden');
                        };
                        tbody.appendChild(row);
                    });
                } else {
                    patientSearchResults.innerHTML = '<div class="p-2 text-gray-500">No patients found.</div>';
                }
            } catch (error) {
                console.error('Error fetching patient data:', error);
            }
        }
        
        async function fetchAndPopulatePatientDetails(mrNumber) {
            try {
                const response = await fetch(`/patients/api/get-by-mr-no/${mrNumber}`);
                const patient = await response.json();
                if (patient) {
                    populatePatientFields(patient);
                } else {
                    alert('No patient found with this MR Number.');
                    clearPatientFields();
                }
            } catch (error) {
                console.error('Error fetching patient data:', error);
                alert('Failed to fetch patient data. Please try again.');
            }
        }
        
        function populatePatientFields(data) {
            mrNumberInput.value = data.mr_number;
            patientNameInput.value = data.name;
            ageInput.value = data.age;
            genderInput.value = data.gender;
        }
        
        function clearPatientFields() {
            mrNumberInput.value = '';
            patientNameInput.value = '';
            ageInput.value = '';
            genderInput.value = '';
        }

        closePatientModalBtn.addEventListener('click', () => {
            patientSearchModal.classList.add('hidden');
        });

        // Consultant Search Logic
        consultantInput.addEventListener('keydown', async function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                if (this.value === '') {
                    consultantSearchModal.classList.remove('hidden');
                    consultantSearchQueryInput.focus();
                    await fetchAndDisplayConsultants('');
                }
            }
        });
        consultantSearchQueryInput.addEventListener('input', async function() {
            const query = this.value;
            await fetchAndDisplayConsultants(query);
        });
        
        async function fetchAndDisplayConsultants(query) {
            const url = `{{ route('patients.api_search_doctor') }}?query=${query}`;
            try {
                const response = await fetch(url);
                const doctors = await response.json();
                consultantSearchResults.innerHTML = '';
                if (doctors.length > 0) {
                    doctors.forEach(doctor => {
                        const resultItem = document.createElement('div');
                        resultItem.className = 'p-2 hover:bg-gray-100 cursor-pointer border-b';
                        resultItem.textContent = `${doctor.name} (${doctor.department || ''})`;
                        resultItem.onclick = () => {
                            consultantInput.value = doctor.name;
                            consultantIdInput.value = doctor.id;
                            consultantSearchModal.classList.add('hidden');
                        };
                        consultantSearchResults.appendChild(resultItem);
                    });
                } else {
                    consultantSearchResults.innerHTML = '<div class="p-2 text-gray-500">No consultants found.</div>';
                }
            } catch (error) {
                console.error('Error fetching consultant data:', error);
            }
        }

        closeConsultantModalBtn.addEventListener('click', () => {
            consultantSearchModal.classList.add('hidden');
        });

        // Dynamic Form Logic
        isAnesthesiaCheckbox.addEventListener('change', () => {
            if (isAnesthesiaCheckbox.checked) {
                anesthesiaSection.classList.remove('hidden');
            } else {
                anesthesiaSection.classList.add('hidden');
            }
        });
    });
</script>