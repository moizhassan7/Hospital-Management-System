@extends('layouts.app')

@section('page_title', 'Lab Booking')

@section('content')
    <div class="hms-page-toolbar flex items-center justify-between mb-6">
        <div>
            <h2 class="hms-page-heading">Lab Booking</h2>
            <p class="hms-page-subheading">Register a patient and book pathology tests</p>
        </div>
        <a href="{{ route('pathology.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

    @include('partials.flash-alerts')

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6">
            <strong class="font-bold text-red-900 block mb-1">Please fix the following errors:</strong>
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pathology.bookings.store') }}" method="POST" id="booking-form">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Side: Patient details -->
            <div class="lg:col-span-7 space-y-6">
                <div class="hms-panel hms-panel-padded">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">Collection Center</h3>
                    <p class="text-sm text-gray-500 mb-4">Choose where this booking is registered before entering patient details.</p>

                    @if($lockedCollectionCenter)
                        <input type="hidden" name="collection_center_id" value="{{ $lockedCollectionCenter->id }}">
                        <div class="hms-field">
                            <label class="hms-label">Booking at</label>
                            <input type="text" class="hms-input bg-gray-50" value="{{ $lockedCollectionCenter->code }} — {{ $lockedCollectionCenter->name }}" readonly>
                            <p class="text-xs text-gray-400 mt-1">Your account is locked to this collection center.</p>
                        </div>
                    @else
                        <div class="hms-field">
                            <label for="collection_center_id" class="hms-label">Collection Center <span class="hms-required">*</span></label>
                            <select id="collection_center_id" name="collection_center_id" class="hms-select" required>
                                <option value="">Select collection center…</option>
                                @forelse($collectionCenters as $center)
                                    <option value="{{ $center->id }}" {{ (string) old('collection_center_id', $defaultCollectionCenterId) === (string) $center->id ? 'selected' : '' }}>
                                        {{ $center->code }} — {{ $center->name }}
                                        @if($center->kind === 'main_lab') (Main Lab) @endif
                                    </option>
                                @empty
                                    <option value="" disabled>No active collection centers found</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Lab numbers and dual-write will use this center’s prefix.</p>
                        </div>
                    @endif
                </div>

                <div class="hms-panel hms-panel-padded">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">Patient Registration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="hms-field relative">
                            <label for="mr_no" class="hms-label">MR Number / Search by Phone</label>
                            <div class="relative">
                                <input type="text" id="mr_no" name="mr_no" class="hms-input pr-10" placeholder="Type MR or Phone..." value="{{ old('mr_no', $nextMrNo ?? '') }}" autocomplete="off">
                                <span id="mr-spinner" class="absolute right-3 top-3 hidden">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </div>
                            
                            <!-- MR Search Dropdown Results -->
                            <div id="mr-search-results" class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto z-50 hidden">
                                <!-- Populated by JS -->
                            </div>

                            <p class="text-xs text-gray-400 mt-0.5">Search by MR number or phone number to load existing details.</p>
                            <div id="mr-lookup-badge" class="mt-1 hidden">
                                <span class="hms-badge hms-badge-green font-medium">Record Found & loaded!</span>
                            </div>
                        </div>

                        <div class="hms-field">
                            <label for="patient_name" class="hms-label">Patient Name <span class="hms-required">*</span></label>
                            <input type="text" id="patient_name" name="patient_name" class="hms-input" placeholder="e.g. Muhammad Ali" value="{{ old('patient_name') }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="hms-field">
                            <label for="age" class="hms-label">Age (Years) <span class="hms-required">*</span></label>
                            <input type="number" id="age" name="age" class="hms-input" placeholder="e.g. 35" min="0" max="150" value="{{ old('age') }}" required>
                        </div>

                        <div class="hms-field">
                            <label for="gender" class="hms-label">Gender <span class="hms-required">*</span></label>
                            <select id="gender" name="gender" class="hms-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="hms-field">
                            <label for="contact_no" class="hms-label">Contact Number</label>
                            <input type="text" id="contact_no" name="contact_no" class="hms-input" placeholder="e.g. 03001234567" value="{{ old('contact_no') }}">
                        </div>
                    </div>

                </div>

                <div class="hms-panel hms-panel-padded">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">Referrer Details</h3>
                    
                    <div class="mb-4">
                        <label class="hms-checkbox-row inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="self_referred" name="self_referred" value="1" class="hms-checkbox" {{ (old('_token') ? old('self_referred') : true) ? 'checked' : '' }}>
                            <span class="hms-checkbox-label">Self Referred (No Doctor reference needed)</span>
                        </label>
                    </div>

                    <div class="hms-field relative" id="doctor-field-container">
                        <label for="doctor_search" class="hms-label">Referred By Doctor Name <span class="hms-required">*</span></label>
                        <input type="hidden" id="doctor_id" name="doctor_id" value="{{ old('doctor_id') }}">
                        <input type="hidden" id="refer_by_doctor_name" name="refer_by_doctor_name" value="{{ old('refer_by_doctor_name') }}">
                        <div class="relative">
                            <input type="text" id="doctor_search" class="hms-input pr-10"
                                placeholder="{{ $doctors->isEmpty() ? 'No doctors saved — add under Referring Doctors' : 'Search or select referring doctor…' }}"
                                value="{{ old('refer_by_doctor_name') }}"
                                autocomplete="off"
                                role="combobox"
                                aria-expanded="false"
                                aria-controls="doctor-search-results"
                                {{ $doctors->isEmpty() ? 'disabled' : '' }}>
                            <span class="absolute right-3 top-3.5 text-gray-400 pointer-events-none" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                        </div>
                        <div id="doctor-search-results"
                            class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto z-50 hidden"
                            role="listbox"></div>
                        <p class="text-xs text-gray-400 mt-1">
                            @if($doctors->isEmpty())
                                No referring doctors saved yet. Add them under <strong>Referring Doctors</strong>, then return here.
                            @else
                                {{ $doctors->count() }} doctor{{ $doctors->count() === 1 ? '' : 's' }} available — type to search, or open the list and pick one.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Test search and pricing calculation -->
            <div class="lg:col-span-5 space-y-6">
                <div class="hms-panel hms-panel-padded">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">Pathology Test Selection</h3>
                    
                    @if(isset($testPackages) && $testPackages->isNotEmpty())
                    <div class="hms-field relative mb-4">
                        <label for="package-select" class="hms-label">Add a Test Package</label>
                        <select id="package-select" class="hms-select">
                            <option value="">Select a package to add its tests...</option>
                            @foreach($testPackages as $package)
                                <option value="{{ $package->id }}" data-price="{{ $package->price }}" data-tests="{{ json_encode($package->tests->map(fn($t) => ['id' => $t->id, 'name' => $name = $t->name, 'price' => $t->price])) }}">
                                    {{ $package->name }} (PKR {{ number_format($package->price, 0) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Selecting a package will add all its tests to the list and apply any package discount.</p>
                    </div>
                    @endif
                    
                    <div class="hms-field relative mb-4">
                        <label for="test-search" class="hms-label">Search Pathology Tests</label>
                        <div class="relative">
                            <input type="text" id="test-search" class="hms-input pr-10" placeholder="Type test name to search..." autocomplete="off">
                            <span class="absolute right-3 top-3.5 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                        </div>
                        
                        <!-- Search Dropdown Results -->
                        <div id="search-results" class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto z-50 hidden">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- Selected Tests List -->
                    <div class="hms-table-wrap mb-4">
                        <table class="hms-table border border-gray-100 min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 text-left">Test Name</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 text-right w-24">Base Price</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 text-right w-40">Discount</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 text-right w-28">Net Price</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-gray-600 text-center w-12"></th>
                                </tr>
                            </thead>
                            <tbody id="selected-tests-body">
                                <tr id="no-tests-row">
                                    <td colspan="3" class="text-center py-6 text-sm text-gray-400 italic">No tests selected yet. Search above to add.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Billing Summary Card -->
                <div class="hms-panel hms-panel-padded bg-slate-50 border-slate-200">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-slate-200 pb-3 mb-4">Billing Summary</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Sub-Total:</span>
                            <span class="text-base font-bold text-gray-800" id="sub-total-text">PKR 0</span>
                            <input type="hidden" name="sub_total" id="sub_total_val" value="0">
                        </div>

                        <div class="grid grid-cols-2 gap-4 items-center border-t border-slate-200 pt-3">
                            <label for="discount" class="text-sm font-medium text-gray-600">Discount:</label>
                            <div class="flex">
                                <input type="number" id="discount_input" class="hms-input !py-1 px-2.5 text-right font-semibold rounded-r-none" min="0" value="0">
                                <select id="discount_type" class="hms-select !py-1 px-2 !bg-gray-100 border-l-0 rounded-l-none text-sm font-medium focus:ring-0">
                                    <option value="flat">PKR</option>
                                    <option value="percentage">%</option>
                                </select>
                            </div>
                            <input type="hidden" id="discount" name="discount" value="0">
                            <input type="hidden" id="discount_type_hidden" name="discount_type" value="flat">
                            <input type="hidden" id="discount_value_hidden" name="discount_value" value="0">
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                            <span class="text-sm font-semibold text-gray-700">Grand Total:</span>
                            <span class="text-lg font-extrabold text-blue-700" id="grand-total-text">PKR 0</span>
                            <input type="hidden" name="grand_total" id="grand_total_val" value="0">
                        </div>

                        <div class="grid grid-cols-2 gap-4 items-center border-t border-slate-200 pt-3">
                            <label for="paid_amount" class="text-sm font-medium text-gray-600">Paid Amount (PKR):</label>
                            <input type="number" id="paid_amount" name="paid_amount" class="hms-input !py-1 px-2.5 text-right font-semibold text-green-700" min="0" value="0">
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                            <span class="text-sm font-medium text-gray-600">Due Amount:</span>
                            <span class="text-base font-bold text-red-600" id="due-amount-text">PKR 0</span>
                            <input type="hidden" name="due_amount" id="due_amount_val" value="0">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full hms-btn hms-btn-primary hms-btn-lg justify-center shadow-md">
                            Confirm Booking & Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @php
        $formattedDoctors = $doctors->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'phone' => $d->phone,
        ])->values();
    @endphp

    @push('scripts')
    <script>
        window.pathologyTests = @json($tests);

        document.addEventListener('DOMContentLoaded', function () {
            // Elements
            const mrInput = document.getElementById('mr_no');
            const mrSpinner = document.getElementById('mr-spinner');
            const mrBadge = document.getElementById('mr-lookup-badge');
            
            const nameInput = document.getElementById('patient_name');
            const ageInput = document.getElementById('age');
            const genderSelect = document.getElementById('gender');
            const contactInput = document.getElementById('contact_no');
            
            const selfReferredCheckbox = document.getElementById('self_referred');
            const doctorFieldContainer = document.getElementById('doctor-field-container');
            const doctorNameInput = document.getElementById('refer_by_doctor_name');
            const doctorIdInput = document.getElementById('doctor_id');
            const doctorSearchInput = document.getElementById('doctor_search');
            const doctorSearchResults = document.getElementById('doctor-search-results');
            const referringDoctors = @json($formattedDoctors);
            let doctorFocusIndex = -1;

            function clearDoctorSelection() {
                if (doctorIdInput) doctorIdInput.value = '';
                if (doctorNameInput) doctorNameInput.value = '';
                if (doctorSearchInput) doctorSearchInput.value = '';
                hideDoctorResults();
            }

            function selectDoctor(doctor) {
                if (!doctor) return;
                doctorIdInput.value = doctor.id;
                doctorNameInput.value = doctor.name;
                doctorSearchInput.value = doctor.name;
                hideDoctorResults();
            }

            function hideDoctorResults() {
                if (!doctorSearchResults) return;
                doctorSearchResults.classList.add('hidden');
                doctorSearchResults.innerHTML = '';
                doctorFocusIndex = -1;
                if (doctorSearchInput) doctorSearchInput.setAttribute('aria-expanded', 'false');
            }

            function renderDoctorResults(query = '') {
                if (!doctorSearchResults || !doctorSearchInput || doctorSearchInput.disabled) return;

                const q = (query || '').trim().toLowerCase();
                const matches = referringDoctors.filter((d) => {
                    if (!q) return true;
                    const hay = [d.name, d.phone].filter(Boolean).join(' ').toLowerCase();
                    return hay.includes(q);
                });

                doctorSearchResults.innerHTML = '';
                doctorFocusIndex = -1;

                if (referringDoctors.length === 0) {
                    doctorSearchResults.innerHTML = '<div class="p-3 text-sm text-gray-500 italic text-center">No referring doctors saved.</div>';
                } else if (matches.length === 0) {
                    doctorSearchResults.innerHTML = '<div class="p-3 text-sm text-gray-500 italic text-center">No doctors match your search.</div>';
                } else {
                    matches.forEach((doctor) => {
                        const div = document.createElement('div');
                        div.className = 'p-3 border-b border-gray-100 text-sm hover:bg-blue-50 cursor-pointer doctor-search-item';
                        div.setAttribute('role', 'option');
                        div.dataset.doctorId = doctor.id;
                        div.innerHTML = `
                            <span class="font-semibold text-gray-800">${doctor.name}</span>
                            ${doctor.phone ? `<span class="text-xs text-gray-400 block mt-0.5">${doctor.phone}</span>` : ''}
                        `;
                        div.addEventListener('click', function () {
                            selectDoctor(doctor);
                        });
                        doctorSearchResults.appendChild(div);
                    });
                }

                doctorSearchResults.classList.remove('hidden');
                doctorSearchInput.setAttribute('aria-expanded', 'true');
            }

            function highlightDoctorItem(items) {
                items.forEach((el) => {
                    el.classList.remove('bg-blue-100', 'text-blue-900');
                    el.classList.add('hover:bg-blue-50');
                });
                if (doctorFocusIndex < 0 || doctorFocusIndex >= items.length) return;
                const active = items[doctorFocusIndex];
                active.classList.remove('hover:bg-blue-50');
                active.classList.add('bg-blue-100', 'text-blue-900');
                active.scrollIntoView({ block: 'nearest' });
            }

            if (doctorSearchInput) {
                doctorSearchInput.addEventListener('focus', function () {
                    if (!doctorSearchInput.disabled) renderDoctorResults(doctorSearchInput.value);
                });

                doctorSearchInput.addEventListener('click', function () {
                    if (!doctorSearchInput.disabled) renderDoctorResults(doctorSearchInput.value);
                });

                doctorSearchInput.addEventListener('input', function () {
                    // Typing means selection is not confirmed until an option is chosen.
                    doctorIdInput.value = '';
                    doctorNameInput.value = '';
                    renderDoctorResults(doctorSearchInput.value);
                });

                doctorSearchInput.addEventListener('keydown', function (e) {
                    const items = doctorSearchResults.querySelectorAll('.doctor-search-item');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (doctorSearchResults.classList.contains('hidden')) {
                            renderDoctorResults(doctorSearchInput.value);
                        }
                        doctorFocusIndex = Math.min(doctorFocusIndex + 1, items.length - 1);
                        highlightDoctorItem(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        doctorFocusIndex = Math.max(doctorFocusIndex - 1, 0);
                        highlightDoctorItem(items);
                    } else if (e.key === 'Enter') {
                        if (!doctorSearchResults.classList.contains('hidden') && doctorFocusIndex >= 0 && items[doctorFocusIndex]) {
                            e.preventDefault();
                            items[doctorFocusIndex].click();
                        }
                    } else if (e.key === 'Escape') {
                        hideDoctorResults();
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!doctorFieldContainer.contains(e.target)) {
                        hideDoctorResults();
                    }
                });
            }
            
            const testSearchInput = document.getElementById('test-search');
            const searchResults = document.getElementById('search-results');
            const selectedTestsBody = document.getElementById('selected-tests-body');
            const noTestsRow = document.getElementById('no-tests-row');

            const subTotalText = document.getElementById('sub-total-text');
            const subTotalVal = document.getElementById('sub_total_val');
            const discountInput = document.getElementById('discount');
            const grandTotalText = document.getElementById('grand-total-text');
            const grandTotalVal = document.getElementById('grand_total_val');
            const paidAmountInput = document.getElementById('paid_amount');
            const dueAmountText = document.getElementById('due-amount-text');
            const dueAmountVal = document.getElementById('due_amount_val');

            const bookingForm = document.getElementById('booking-form');

            let selectedTestsList = [];
            let mrLookupTimeout = null;

            // --- MR Number / Phone Autocomplete & Suggestions ---
            const mrSearchResults = document.getElementById('mr-search-results');
            let currentMrFocus = -1;

            mrInput.addEventListener('input', function () {
                clearTimeout(mrLookupTimeout);
                const query = mrInput.value.trim();
                mrSearchResults.innerHTML = '';
                currentMrFocus = -1;
                
                if (query.length < 2) {
                    mrBadge.classList.add('hidden');
                    mrSearchResults.classList.add('hidden');
                    return;
                }

                mrSpinner.classList.remove('hidden');
                
                mrLookupTimeout = setTimeout(function () {
                    fetch(`/pathology/api/search-patients?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(patients => {
                            mrSpinner.classList.add('hidden');
                            mrSearchResults.innerHTML = '';

                            if (patients.length === 0) {
                                mrSearchResults.innerHTML = '<div class="p-3 text-sm text-gray-500 italic text-center">No matching patients found.</div>';
                            } else {
                                patients.forEach(patient => {
                                    const div = document.createElement('div');
                                    div.className = 'p-3 border-b border-gray-100 flex items-center justify-between text-sm hover:bg-blue-50 cursor-pointer mr-search-item';
                                    div.innerHTML = `
                                        <div>
                                            <span class="font-semibold text-gray-800">${patient.patient_name}</span>
                                            <span class="text-xs text-gray-400 block">MR: ${patient.mr_no || '—'} | Phone: ${patient.contact_no || '—'}</span>
                                        </div>
                                        <div>
                                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">${patient.gender}, ${patient.age} yrs</span>
                                        </div>
                                    `;
                                    
                                    div.addEventListener('click', function () {
                                        selectPatient(patient);
                                    });

                                    mrSearchResults.appendChild(div);
                                });
                            }
                            mrSearchResults.classList.remove('hidden');
                        })
                        .catch(err => {
                            console.error('Patient search error:', err);
                            mrSpinner.classList.add('hidden');
                        });
                }, 400);
            });

            function selectPatient(patient) {
                mrInput.value = patient.mr_no || '';
                nameInput.value = patient.patient_name || '';
                ageInput.value = patient.age || '';
                genderSelect.value = patient.gender || '';
                contactInput.value = patient.contact_no || '';
                
                mrBadge.classList.remove('hidden');
                mrSearchResults.classList.add('hidden');

                // Shift focus to the test-search input field
                setTimeout(() => testSearchInput.focus(), 50);
            }

            // Keyboard navigation for MR suggestions
            mrInput.addEventListener('keydown', function (e) {
                const items = mrSearchResults.querySelectorAll('.mr-search-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentMrFocus++;
                    setMrActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentMrFocus--;
                    setMrActive(items);
                } else if (e.key === 'Enter') {
                    if (currentMrFocus > -1) {
                        e.preventDefault();
                        if (items[currentMrFocus]) {
                            items[currentMrFocus].click();
                        }
                    }
                }
            });

            function setMrActive(items) {
                if (!items || items.length === 0) return;

                items.forEach(item => {
                    item.classList.remove('bg-blue-100', 'text-blue-900');
                    item.classList.add('hover:bg-blue-50');
                });

                if (currentMrFocus >= items.length) currentMrFocus = 0;
                if (currentMrFocus < 0) currentMrFocus = items.length - 1;

                const activeItem = items[currentMrFocus];
                activeItem.classList.remove('hover:bg-blue-50');
                activeItem.classList.add('bg-blue-100', 'text-blue-900');
                activeItem.scrollIntoView({ block: 'nearest' });
            }

            // --- Self Referred Toggle ---
            function setDoctorFieldEnabled(enabled) {
                if (!doctorSearchInput) return;
                if (enabled && referringDoctors.length > 0) {
                    doctorFieldContainer.classList.remove('opacity-50');
                    doctorSearchInput.disabled = false;
                    doctorSearchInput.required = true;
                } else {
                    doctorFieldContainer.classList.add('opacity-50');
                    doctorSearchInput.disabled = true;
                    doctorSearchInput.required = false;
                    clearDoctorSelection();
                    hideDoctorResults();
                }
            }

            selfReferredCheckbox.addEventListener('change', function () {
                if (selfReferredCheckbox.checked) {
                    setDoctorFieldEnabled(false);
                } else {
                    setDoctorFieldEnabled(true);
                    if (!doctorSearchInput.disabled) doctorSearchInput.focus();
                }
            });

            // Trigger once on load in case of validation back
            if (selfReferredCheckbox.checked || referringDoctors.length === 0) {
                setDoctorFieldEnabled(false);
            } else {
                setDoctorFieldEnabled(true);
            }

            // Require a list selection (not free-typed name only)
            if (bookingForm) {
                bookingForm.addEventListener('submit', function (e) {
                    if (selfReferredCheckbox.checked) return;
                    if (!doctorIdInput.value || !doctorNameInput.value) {
                        e.preventDefault();
                        alert('Please select a referring doctor from the list.');
                        if (!doctorSearchInput.disabled) {
                            doctorSearchInput.focus();
                            renderDoctorResults(doctorSearchInput.value);
                        }
                    }
                });
            }

            // --- Package Selection Logic ---
            const packageSelect = document.getElementById('package-select');
            if (packageSelect) {
                packageSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    if (!selectedOption.value) return;

                    const packagePrice = parseFloat(selectedOption.dataset.price) || 0;
                    const packageTests = JSON.parse(selectedOption.dataset.tests || '[]');

                    let sumAddedPrices = 0;

                    // Add all tests in the package to the selectedTestsList
                    packageTests.forEach(test => {
                        const isAdded = selectedTestsList.some(item => item.id === test.id);
                        if (!isAdded) {
                            selectedTestsList.push({
                                id: test.id,
                                name: test.name,
                                list_price: Number(test.price),
                                discount_type: 'flat',
                                discount_value: 0,
                                price: Number(test.price)
                            });
                            sumAddedPrices += Number(test.price);
                        }
                    });

                    renderSelectedTests();
                    
                    // Automatically apply discount to match the package price
                    if (sumAddedPrices > packagePrice) {
                        const discountDiff = sumAddedPrices - packagePrice;
                        document.getElementById('discount_type').value = 'flat';
                        document.getElementById('discount_input').value = discountDiff;
                    }
                    
                    recalculateTotals();

                    // Reset package select back to placeholder
                    this.value = '';
                });
            }

            // --- Test Search Logic ---
            let currentFocus = -1;

            testSearchInput.addEventListener('input', function () {
                const query = testSearchInput.value.toLowerCase().trim();
                searchResults.innerHTML = '';
                currentFocus = -1; // Reset keyboard selection focus

                if (query.length === 0) {
                    searchResults.classList.add('hidden');
                    return;
                }

                const matches = window.pathologyTests.filter(test => 
                    test.name.toLowerCase().includes(query) || 
                    test.test_id.toLowerCase().includes(query)
                );

                if (matches.length === 0) {
                    searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500 italic text-center">No tests found.</div>';
                } else {
                    matches.forEach(test => {
                        const isAdded = selectedTestsList.some(item => item.id === test.id);
                        const disabledClass = isAdded ? 'opacity-40 cursor-default pointer-events-none bg-slate-50' : 'hover:bg-blue-50 cursor-pointer search-item';
                        const addedIndicator = isAdded ? '<span class="text-xs text-green-600 font-semibold bg-green-50 px-2 py-0.5 rounded">Added</span>' : '';
                        
                        const div = document.createElement('div');
                        div.className = `p-3 border-b border-gray-100 flex items-center justify-between text-sm transition-colors ${disabledClass}`;
                        div.innerHTML = `
                            <div>
                                <span class="font-semibold text-gray-800">${test.name}</span>
                                <span class="text-xs text-gray-400 block">${test.test_id}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-700">PKR ${Number(test.price).toLocaleString()}</span>
                                ${addedIndicator}
                            </div>
                        `;
                        
                        if (!isAdded) {
                            div.addEventListener('click', function () {
                                addTest(test);
                                testSearchInput.value = '';
                                searchResults.classList.add('hidden');
                                testSearchInput.focus();
                            });
                        }
                        
                        searchResults.appendChild(div);
                    });
                }

                searchResults.classList.remove('hidden');
            });

            // Keyboard navigation on search input
            testSearchInput.addEventListener('keydown', function (e) {
                const items = searchResults.querySelectorAll('.search-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentFocus++;
                    setActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentFocus--;
                    setActive(items);
                } else if (e.key === 'Enter') {
                    if (currentFocus > -1) {
                        e.preventDefault();
                        if (items[currentFocus]) {
                            items[currentFocus].click();
                        }
                    }
                }
            });

            function setActive(items) {
                if (!items || items.length === 0) return;

                // Remove active classes
                items.forEach(item => {
                    item.classList.remove('bg-blue-100', 'text-blue-900');
                    item.classList.add('hover:bg-blue-50');
                });

                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = items.length - 1;

                const activeItem = items[currentFocus];
                activeItem.classList.remove('hover:bg-blue-50');
                activeItem.classList.add('bg-blue-100', 'text-blue-900');

                // Scroll the highlighted item into view if container has scroll
                activeItem.scrollIntoView({ block: 'nearest' });
            }

            // Close results dropdown on outside click
            document.addEventListener('click', function (e) {
                if (!testSearchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
                if (!mrInput.contains(e.target) && !mrSearchResults.contains(e.target)) {
                    mrSearchResults.classList.add('hidden');
                }
            });

            // --- Add Test to Selection ---
            function addTest(test) {
                selectedTestsList.push({
                    id: test.id,
                    name: test.name,
                    list_price: Number(test.price),
                    discount_type: 'flat',
                    discount_value: 0,
                    price: Number(test.price)
                });

                renderSelectedTests();
                recalculateTotals();
            }

            // --- Remove Test from Selection ---
            window.removeTest = function (testId) {
                selectedTestsList = selectedTestsList.filter(test => test.id !== testId);
                renderSelectedTests();
                recalculateTotals();
            };

            // --- Update Selected Test Discount (On-The-Fly) ---
            window.updateTestDiscount = function (testId, discountValue, discountType) {
                const test = selectedTestsList.find(test => test.id === testId);
                if (test) {
                    let val = Math.max(0, parseFloat(discountValue) || 0);
                    test.discount_type = discountType;
                    test.discount_value = val;
                    
                    let discountAmt = 0;
                    if (discountType === 'percentage') {
                        val = Math.min(100, val);
                        test.discount_value = val;
                        discountAmt = (test.list_price * val) / 100;
                    } else {
                        val = Math.min(test.list_price, val);
                        test.discount_value = val;
                        discountAmt = val;
                    }
                    
                    test.price = Math.max(0, test.list_price - discountAmt);
                    
                    const valInput = document.getElementById(`val_${test.id}`);
                    if (valInput && valInput.value != val) valInput.value = val;
                    
                    const priceDisplay = document.getElementById(`net-price-display-${test.id}`);
                    const priceInput = document.getElementById(`net-price-input-${test.id}`);
                    
                    if (priceDisplay) priceDisplay.textContent = test.price.toLocaleString();
                    if (priceInput) priceInput.value = test.price;
                    
                    recalculateTotals();
                }
            };

            // --- Render Selected Tests Table ---
            function renderSelectedTests() {
                if (selectedTestsList.length === 0) {
                    noTestsRow.classList.remove('hidden');
                    // Remove all test rows
                    selectedTestsBody.querySelectorAll('.test-row').forEach(row => row.remove());
                    return;
                }

                noTestsRow.classList.add('hidden');
                selectedTestsBody.querySelectorAll('.test-row').forEach(row => row.remove());

                selectedTestsList.forEach((test, index) => {
                    const listPriceFormatted = test.list_price ? Number(test.list_price).toLocaleString() : Number(test.price).toLocaleString();
                    const tr = document.createElement('tr');
                    tr.className = 'test-row border-b border-gray-100 hover:bg-slate-50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-3 py-2.5 text-sm text-gray-800 font-medium">
                            ${test.name}
                            <input type="hidden" name="tests[${index}][id]" value="${test.id}">
                            <input type="hidden" name="tests[${index}][list_price]" value="${test.list_price}">
                        </td>
                        <td class="px-3 py-2.5 text-right font-semibold text-gray-600">
                            ${listPriceFormatted}
                        </td>
                        <td class="px-3 py-2.5">
                            <div class="flex justify-end">
                                <input type="number" name="tests[${index}][discount_value]" id="val_${test.id}"
                                    class="hms-input !py-1 px-2 text-right rounded-r-none w-20" 
                                    min="0" value="${test.discount_value}" 
                                    oninput="updateTestDiscount(${test.id}, this.value, document.getElementById('type_${test.id}').value)">
                                <select name="tests[${index}][discount_type]" id="type_${test.id}"
                                    class="hms-select !py-1 px-1 !bg-gray-100 border-l-0 rounded-l-none text-xs focus:ring-0 w-14"
                                    onchange="updateTestDiscount(${test.id}, this.previousElementSibling.value, this.value)">
                                    <option value="flat" ${test.discount_type === 'flat' ? 'selected' : ''}>PKR</option>
                                    <option value="percentage" ${test.discount_type === 'percentage' ? 'selected' : ''}>%</option>
                                </select>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-right font-bold text-gray-800">
                            <span id="net-price-display-${test.id}">${Number(test.price).toLocaleString()}</span>
                            <input type="hidden" name="tests[${index}][price]" id="net-price-input-${test.id}" value="${test.price}">
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            <button type="button" class="text-red-500 hover:text-red-700 text-lg leading-none" 
                                onclick="removeTest(${test.id})" title="Remove test">&times;</button>
                        </td>
                    `;
                    selectedTestsBody.appendChild(tr);
                });
            }

            // --- Recalculate Billing Summary ---
            function recalculateTotals() {
                const subTotal = selectedTestsList.reduce((sum, test) => sum + test.price, 0);
                
                const discountRaw = Math.max(0, parseFloat(document.getElementById('discount_input').value) || 0);
                const discountType = document.getElementById('discount_type').value;
                
                let discount = 0;
                if (discountType === 'percentage') {
                    discount = (subTotal * discountRaw) / 100;
                } else {
                    discount = discountRaw;
                }
                
                // Set hidden input for backend
                document.getElementById('discount').value = discount;
                document.getElementById('discount_type_hidden').value = discountType;
                document.getElementById('discount_value_hidden').value = discountRaw;
                
                const grandTotal = Math.max(0, subTotal - discount);
                
                subTotalText.textContent = `PKR ${subTotal.toLocaleString()}`;
                subTotalVal.value = subTotal;

                grandTotalText.textContent = `PKR ${grandTotal.toLocaleString()}`;
                grandTotalVal.value = grandTotal;

                // Sync paid amount with grand total if it hasn't been set or equals grand total before calculation
                if (paidAmountInput.dataset.touched !== 'true') {
                    paidAmountInput.value = grandTotal;
                }

                const paidAmount = Math.max(0, parseFloat(paidAmountInput.value) || 0);
                const dueAmount = Math.max(0, grandTotal - paidAmount);

                dueAmountText.textContent = `PKR ${dueAmount.toLocaleString()}`;
                dueAmountVal.value = dueAmount;
            }

            // Detect manual edits on Paid Amount to prevent auto-syncing
            paidAmountInput.addEventListener('input', function () {
                paidAmountInput.dataset.touched = 'true';
                recalculateTotals();
            });

            document.getElementById('discount_input').addEventListener('input', function () {
                recalculateTotals();
            });
            document.getElementById('discount_type').addEventListener('change', function () {
                recalculateTotals();
            });

            // Prevent form submit if no tests are selected
            bookingForm.addEventListener('submit', function (e) {
                if (selectedTestsList.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one pathology test before saving.');
                }
            });

            // Global Ctrl + S to save the booking
            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                    e.preventDefault();
                    bookingForm.requestSubmit();
                }
            });
        });
    </script>
    @endpush
@endsection
