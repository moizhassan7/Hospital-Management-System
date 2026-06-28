@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">
            @if(!empty($isEdit))
                Edit Results for
            @elseif($isReadOnly)
                Report for
            @else
                Enter Results for
            @endif
            {{ $test->name }}
        </h2></div>
        <div class="flex items-center space-x-3">
            @if($isReadOnly)
                @if(Auth::user()->isSuperAdmin() || Auth::user()->hasPermission(\App\Support\LabPermissions::RESULT_EDIT))
                    <a href="{{ route('pathology.result_entry.edit', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-6 rounded-lg shadow-md transition-all flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Results
                    </a>
                @endif
                <a href="{{ route('pathology.print_report', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-md transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4"></path></svg>
                    Print Report
                </a>
            @endif
            <a href="{{ route('pathology.result_entry.search') }}" class="hms-back-btn">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Patient Search
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="hms-panel hms-panel-padded mb-5">
        <h3 class="hms-section-title">Patient: {{ $labPatient->patient_name }} (Lab Reg: {{ $labPatient->lab_registration_no ?? 'N/A' }})</h3>

        <form id="result-entry-form" action="{{ route('pathology.result_entry.save', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @if($test->report_format === 'Quantitative' || !$test->report_format)
                <div class="hms-table-wrap mb-5">
                    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide w-12">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Parameter</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Reference Value</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide w-24">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide w-48">Result</th>
                                @if($test->testParticulars->contains('is_calculated', true) && !$isReadOnly)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide w-28">Include</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($test->testParticulars as $index => $particular)
                                @php
                                    $existingVal = $existingResults[$particular->id] ?? '';
                                    $isAbnormal = false;
                                    if ($existingVal !== '' && is_numeric($existingVal)) {
                                        if ($particular->normal_range_min !== null && $existingVal < $particular->normal_range_min) $isAbnormal = true;
                                        if ($particular->normal_range_max !== null && $existingVal > $particular->normal_range_max) $isAbnormal = true;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 {{ $isAbnormal ? 'bg-red-50' : '' }}" data-particular-row="{{ $particular->id }}">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $particular->name }}
                                        @if($particular->is_calculated)
                                            <span class="ml-1 text-xs text-indigo-600">(Auto)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @if($particular->normal_range_min !== null || $particular->normal_range_max !== null)
                                            {{ $particular->normal_range_min ?? '—' }} – {{ $particular->normal_range_max ?? '—' }}
                                        @else
                                            {{ $particular->reference_text ?: '—' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $particular->unit ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($isReadOnly || $particular->is_calculated)
                                            <span class="text-sm font-semibold result-display {{ $isAbnormal ? 'text-red-600' : 'text-gray-900' }}" id="display_{{ $particular->id }}">{{ $existingVal ?: '—' }}</span>
                                            @if($particular->is_calculated)
                                                <input type="hidden" id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" value="{{ $existingVal }}">
                                            @endif
                                        @else
                                            <input type="text"
                                                id="result_{{ $particular->id }}"
                                                name="result_{{ $particular->id }}"
                                                value="{{ $existingVal }}"
                                                data-min="{{ $particular->normal_range_min }}"
                                                data-max="{{ $particular->normal_range_max }}"
                                                data-critical-min="{{ $particular->critical_range_min }}"
                                                data-critical-max="{{ $particular->critical_range_max }}"
                                                data-particular-name="{{ $particular->name }}"
                                                data-unit="{{ $particular->unit }}"
                                                data-result-key="{{ $particular->result_key }}"
                                                data-calculated="0"
                                                class="result-input shadow appearance-none border rounded-lg w-full py-2 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $isAbnormal ? 'border-red-400 bg-red-50' : '' }}"
                                                placeholder="Enter value">
                                        @endif
                                    </td>
                                    @if($particular->is_calculated && !$isReadOnly)
                                    @php
                                        $includeCalcDefault = $hasExistingResults
                                            ? ($existingVal !== '' && $existingVal !== null)
                                            : true;
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <label class="hms-checkbox-row justify-center cursor-pointer" title="Include this value on report">
                                            <input type="checkbox"
                                                name="include_calculated_{{ $particular->id }}"
                                                value="1"
                                                class="include-calc-checkbox w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                                                data-particular-id="{{ $particular->id }}"
                                                @checked(old('include_calculated_' . $particular->id, $includeCalcDefault))>
                                        </label>
                                    </td>
                                    @elseif($test->testParticulars->contains('is_calculated', true) && !$isReadOnly)
                                    <td class="px-4 py-3"></td>
                                    @endif
                                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-pre-line max-w-xs">{{ $particular->remarks }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mb-6">
                    <label for="test_comment" class="hms-label">Test Comment / Interpretation</label>
                    @if($isReadOnly)
                        <div class="text-sm text-gray-800 whitespace-pre-line border rounded-lg p-3 bg-gray-50">{{ $testComment ?: '—' }}</div>
                    @else
                        <textarea id="test_comment" name="test_comment" rows="5"
                            class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Additional interpretation or clinical comment for this report">{{ old('test_comment', $testComment) }}</textarea>
                    @endif
                </div>
            @else
                <!-- Descriptive Format -->
                <div class="hms-table-wrap mb-5">
                    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide w-12">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide w-1/3">Parameter</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @php $rowNum = 0; @endphp
                            @foreach($test->testParticulars as $particular)
                                @if(strtolower($particular->name) !== 'findings')
                                    @php $rowNum++; @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $rowNum }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $particular->name }}</td>
                                        <td class="px-4 py-3">
                                            @if($isReadOnly)
                                                <span class="text-sm text-gray-900">{{ $existingResults[$particular->id] ?? '—' }}</span>
                                            @else
                                                <input type="text" id="result_{{ $particular->id }}" name="result_{{ $particular->id }}"
                                                    class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Enter {{ $particular->name }}"
                                                    value="{{ $existingResults[$particular->id] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @foreach($test->testParticulars as $particular)
                    @if(strtolower($particular->name) === 'findings')
                        <div class="mb-6">
                            <label for="result_{{ $particular->id }}" class="hms-label">Findings</label>
                            <div class="quill_editor bg-white border rounded-lg" style="height: 300px;"></div>
                            <textarea id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" class="quill-hidden" style="display: none;">{{ $existingResults[$particular->id] ?? $test->template }}</textarea>
                        </div>
                    @endif
                @endforeach
                
                @if($test->report_format === 'Radiology')
                    <div class="mb-6">
                        @if(!$isReadOnly)
                            <label for="test_images" class="hms-label">Upload Images/DICOM Screenshots (Optional):</label>
                            <input type="file" id="test_images" name="test_images[]" multiple accept="image/*" class="hms-input">
                            <p class="text-xs text-gray-500 mt-1">You can select multiple images.</p>
                        @endif

                        @if(isset($testImages) && $testImages->count() > 0)
                            <div class="mt-4">
                                <h4 class="font-bold text-gray-800 mb-2">Attached Images:</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach($testImages as $image)
                                        <div class="relative group">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-32 object-cover rounded-lg shadow-sm">
                                            <a href="{{ asset('storage/' . $image->image_path) }}" target="_blank" class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center rounded-lg">
                                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            @if(!$isReadOnly)
                <div class="flex justify-end">
                    <button type="submit" class="hms-btn hms-btn-primary">
                        {{ !empty($isEdit) ? 'Update Results' : 'Save Results' }}
                    </button>
                </div>
            @endif
        </form>
    </div>

    @if(!$isReadOnly && ($test->report_format === 'Quantitative' || !$test->report_format))
        @include('partials.pathology-result-alert-modals')
    @endif

    @if(!$isReadOnly && ($test->report_format === 'Quantitative' || !$test->report_format))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const patientAge = {{ (int) $labPatient->age }};
            const patientGender = @json(strtolower((string) $labPatient->gender));
            const particulars = @json($formulaParticulars);
            const form = document.getElementById('result-entry-form');

            const abnormalModal = document.getElementById('abnormal-alert-modal');
            const criticalModal = document.getElementById('critical-alert-modal');
            const abnormalValuesList = document.getElementById('abnormal-modal-values');
            const criticalValuesList = document.getElementById('critical-modal-values');
            const criticalDoctorInput = document.getElementById('critical-doctor-input');
            const criticalDoctorError = document.getElementById('critical-doctor-error');
            const defaultDoctorName = @json($labPatient->refer_by_doctor_name ?? '');
            let bypassAlerts = false;

            function num(id) {
                const el = document.getElementById('result_' + id);
                if (!el) return null;
                const v = parseFloat(el.value);
                return isNaN(v) ? null : v;
            }

            function isCalcIncluded(id) {
                const cb = document.querySelector('[name="include_calculated_' + id + '"]');
                return cb ? cb.checked : true;
            }

            function parseBound(value) {
                if (value === null || value === undefined || value === '') {
                    return null;
                }

                const parsed = parseFloat(value);
                return isNaN(parsed) ? null : parsed;
            }

            function classifyValue(val, p) {
                if (val === null || isNaN(val)) return null;
                const min = parseBound(p.min);
                const max = parseBound(p.max);
                const cMin = parseBound(p.critical_min);
                const cMax = parseBound(p.critical_max);

                if (cMin !== null && val < cMin) return 'critical';
                if (cMax !== null && val > cMax) return 'critical';

                if (min !== null && max !== null && cMin === null && cMax === null) {
                    const span = Math.max(max - min, 0.0001);
                    if (val < min - span * 0.5) return 'critical';
                    if (val > max + span * 0.5) return 'critical';
                }

                if (min !== null && val < min) return 'abnormal';
                if (max !== null && val > max) return 'abnormal';
                return 'normal';
            }

            function refText(p) {
                if (p.min !== null || p.max !== null) {
                    return (p.min ?? '—') + ' – ' + (p.max ?? '—') + (p.unit ? ' ' + p.unit : '');
                }
                return '—';
            }

            function setCalculated(id, value) {
                let hidden = document.getElementById('result_' + id);
                const display = document.getElementById('display_' + id);
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.id = 'result_' + id;
                    hidden.name = 'result_' + id;
                    document.querySelector('form').appendChild(hidden);
                }
                hidden.value = value ?? '';
                if (display) display.textContent = value ?? '—';
                highlightRow(id, value);
            }

            function highlightRow(id, value) {
                const row = document.querySelector('[data-particular-row="' + id + '"]');
                const p = particulars.find(x => x.id === id);
                if (!row || !p) return;
                const val = parseFloat(value);
                const level = classifyValue(isNaN(val) ? null : val, p);
                row.classList.remove('bg-red-50', 'bg-amber-50', 'bg-red-100');
                if (level === 'critical') row.classList.add('bg-red-100');
                else if (level === 'abnormal') row.classList.add('bg-amber-50');
            }

            function formatNum(value) {
                if (value === null || isNaN(value)) return null;
                return Number(value.toFixed(2)).toString();
            }

            function recalculate() {
                const byKey = {};
                particulars.forEach(p => {
                    if (!p.is_calculated && p.key) {
                        byKey[p.key] = num(p.id);
                    }
                });

                particulars.filter(p => p.is_calculated).forEach(p => {
                    if (!isCalcIncluded(p.id)) {
                        setCalculated(p.id, null);
                        return;
                    }

                    let value = null;
                    if (p.formula === 'bun_from_urea' && byKey.urea > 0) {
                        value = formatNum(byKey.urea / 2.14);
                    } else if (p.formula === 'egfr_mdrd' && byKey.creatinine > 0) {
                        let gfr = 175 * Math.pow(byKey.creatinine, -1.154) * Math.pow(Math.max(patientAge, 1), -0.203);
                        if (patientGender.includes('f')) gfr *= 0.742;
                        value = formatNum(gfr);
                    } else if (p.formula === 'inr_from_pt' && byKey.pt > 0 && byKey.control > 0) {
                        value = formatNum(byKey.pt / byKey.control);
                    } else if (p.formula === 'ag_ratio' && byKey.albumin > 0 && byKey.globulins > 0) {
                        value = formatNum(byKey.albumin / byKey.globulins);
                    } else if (p.formula === 'indirect_bilirubin' && byKey.bilirubin_total !== null && byKey.bilirubin_direct !== null) {
                        value = formatNum(Math.max(0, byKey.bilirubin_total - byKey.bilirubin_direct));
                    }
                    setCalculated(p.id, value);
                });
            }

            document.querySelectorAll('.include-calc-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', recalculate);
            });

            document.querySelectorAll('.result-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    const val = parseFloat(this.value);
                    const p = particulars.find(x => x.id === parseInt(this.id.replace('result_', ''), 10));
                    const row = this.closest('tr');
                    let level = p ? classifyValue(isNaN(val) ? null : val, p) : null;
                    this.classList.remove('border-red-400', 'border-amber-400', 'bg-red-50', 'bg-amber-50');
                    row.classList.remove('bg-red-50', 'bg-amber-50', 'bg-red-100');
                    if (level === 'critical') {
                        this.classList.add('border-red-400', 'bg-red-50');
                        row.classList.add('bg-red-100');
                    } else if (level === 'abnormal') {
                        this.classList.add('border-amber-400', 'bg-amber-50');
                        row.classList.add('bg-amber-50');
                    }
                    recalculate();
                });
            });

            function collectValueAlerts() {
                const critical = [];
                const abnormal = [];

                particulars.forEach(function (p) {
                    if (p.is_calculated && !isCalcIncluded(p.id)) return;
                    const val = num(p.id);
                    const level = classifyValue(val, p);
                    if (level === 'critical') critical.push({ p: p, val: val });
                    else if (level === 'abnormal') abnormal.push({ p: p, val: val });
                });

                return { critical, abnormal };
            }

            function showModal(modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function hideModal(modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function showAbnormalModal(abnormalItems) {
                return new Promise(function (resolve, reject) {
                    abnormalValuesList.innerHTML = abnormalItems.map(function (item) {
                        return '<li><strong>' + item.p.name + ':</strong> ' + item.val +
                            (item.p.unit ? ' ' + item.p.unit : '') +
                            ' (Reference: ' + refText(item.p) + ')</li>';
                    }).join('');
                    showModal(abnormalModal);

                    document.getElementById('abnormal-modal-confirm').onclick = function () {
                        hideModal(abnormalModal);
                        resolve(true);
                    };
                    document.getElementById('abnormal-modal-cancel').onclick = function () {
                        hideModal(abnormalModal);
                        reject(new Error('cancelled'));
                    };
                });
            }

            function showCriticalModal(criticalItems) {
                return new Promise(function (resolve, reject) {
                    criticalValuesList.innerHTML = criticalItems.map(function (item) {
                        return '<li><strong>' + item.p.name + ':</strong> ' + item.val +
                            (item.p.unit ? ' ' + item.p.unit : '') +
                            ' — CRITICAL (Reference: ' + refText(item.p) + ')</li>';
                    }).join('');
                    criticalDoctorInput.value = defaultDoctorName || '';
                    criticalDoctorError.classList.add('hidden');
                    showModal(criticalModal);
                    criticalDoctorInput.focus();

                    document.getElementById('critical-modal-confirm').onclick = function () {
                        const doctor = criticalDoctorInput.value.trim();
                        if (!doctor) {
                            criticalDoctorError.classList.remove('hidden');
                            return;
                        }
                        hideModal(criticalModal);
                        resolve(doctor);
                    };
                    document.getElementById('critical-modal-cancel').onclick = function () {
                        hideModal(criticalModal);
                        reject(new Error('cancelled'));
                    };
                });
            }

            function clearAlertFields() {
                form.querySelectorAll(
                    'input[type="hidden"][name^="critical_doctor_"],' +
                    'input[type="hidden"][name^="ack_abnormal_"],' +
                    'input[type="hidden"][name="critical_reported_doctor"],' +
                    'input[type="hidden"][name="abnormal_acknowledged"],' +
                    'input[type="hidden"][name="alerts_reviewed"],' +
                    'input[type="hidden"][name="result_alert_ack"]'
                ).forEach(function (el) {
                    el.remove();
                });
            }

            function setHiddenField(name, value) {
                let el = form.querySelector('input[type="hidden"][name="' + name + '"]');
                if (!el) {
                    el = document.createElement('input');
                    el.type = 'hidden';
                    el.name = name;
                    form.appendChild(el);
                }
                el.value = value;
            }

            function submitFormWithAlerts(alertData) {
                setHiddenField('result_alert_ack', JSON.stringify(alertData));

                if (alertData.critical_doctor) {
                    setHiddenField('critical_reported_doctor', alertData.critical_doctor);
                }

                if (alertData.abnormal_acknowledged) {
                    setHiddenField('abnormal_acknowledged', '1');
                }

                if (alertData.alerts_reviewed) {
                    setHiddenField('alerts_reviewed', '1');
                }

                alertData.ack_particular_ids.forEach(function (id) {
                    setHiddenField('ack_abnormal_' + id, '1');
                    if (alertData.critical_doctor) {
                        setHiddenField('critical_doctor_' + id, alertData.critical_doctor);
                    }
                });

                bypassAlerts = true;
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn && typeof form.requestSubmit === 'function') {
                    form.requestSubmit(submitBtn);
                } else {
                    form.submit();
                }
            }

            form.addEventListener('submit', async function (e) {
                if (bypassAlerts) return;
                e.preventDefault();
                recalculate();

                clearAlertFields();
                const alerts = collectValueAlerts();

                try {
                    const criticalIds = new Set();
                    const ackParticularIds = [];
                    let criticalDoctor = null;
                    let abnormalAcknowledged = false;

                    if (alerts.critical.length > 0) {
                        criticalDoctor = await showCriticalModal(alerts.critical);
                        for (const item of alerts.critical) {
                            criticalIds.add(item.p.id);
                            ackParticularIds.push(item.p.id);
                        }
                    }

                    const abnormalOnly = alerts.abnormal.filter(function (item) {
                        return !criticalIds.has(item.p.id);
                    });

                    if (abnormalOnly.length > 0) {
                        await showAbnormalModal(abnormalOnly);
                        abnormalAcknowledged = true;
                        for (const item of abnormalOnly) {
                            ackParticularIds.push(item.p.id);
                        }
                    }

                    submitFormWithAlerts({
                        critical_doctor: criticalDoctor,
                        abnormal_acknowledged: abnormalAcknowledged,
                        alerts_reviewed: alerts.critical.length > 0 || abnormalOnly.length > 0,
                        ack_particular_ids: ackParticularIds,
                    });
                } catch (err) {
                    // user cancelled
                }
            });

            recalculate();
        });
    </script>
    @endpush
    @endif

    @if($test->report_format === 'Radiology' || $test->report_format === 'Cardiology')
@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .quill_editor {
            min-height: 200px;
            background-color: white;
        }
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            background-color: #f9fafb;
        }
        .ql-container.ql-snow {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing Quill editors...');
            
            var editors = document.querySelectorAll('.quill_editor');
            console.log('Found ' + editors.length + ' editors');

            editors.forEach(function(editorContainer) {
                var textarea = editorContainer.nextElementSibling;
                
                try {
                    var quill = new Quill(editorContainer, {
                        theme: 'snow',
                        readOnly: {{ $isReadOnly ? 'true' : 'false' }},
                        modules: {
                            toolbar: {{ $isReadOnly ? 'false' : "[
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'align': [] }],
                                ['link', 'image'],
                                ['clean']
                            ]" }}
                        }
                    });

                    if (textarea && textarea.value) {
                        quill.root.innerHTML = textarea.value;
                    }

                    @if(!$isReadOnly)
                        quill.on('text-change', function() {
                            textarea.value = quill.root.innerHTML;
                        });
                    @endif
                    
                    console.log('Quill initialized for:', editorContainer);
                } catch (e) {
                    console.error('Quill initialization failed:', e);
                }
            });
        });
    </script>
@endpush
    @endif
@endsection