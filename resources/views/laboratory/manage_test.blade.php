@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Manage Pathology Test</h2></div>
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
        @if(isset($test))
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Edit Pathology Test</h3>
            <form action="{{ route('laboratory.manage_test.update', $test->id) }}" method="POST">
                @csrf
                @method('PUT')
        @else
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Add New Pathology Test</h3>
            <form action="{{ route('laboratory.manage_test.store') }}" method="POST">
                @csrf
        @endif
            <input type="hidden" name="category" value="Pathology">
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="test_name" class="hms-label">Test Name:</label>
                    <input type="text" id="test_name" name="test_name" class="hms-input" placeholder="e.g., Complete Blood Count" value="{{ old('test_name', $test->name ?? '') }}" required>
                </div>
                <div>
                    <label for="test_type" class="hms-label">Test Type:</label>
                    <select id="test_type" name="test_type" class="hms-select" required>
                        <option value="Routine" {{ (old('test_type', $test->type ?? '') == 'Routine') ? 'selected' : '' }}>Routine</option>
                        <option value="Blood" {{ (old('test_type', $test->type ?? '') == 'Blood') ? 'selected' : '' }}>Blood</option>
                        <option value="Urine" {{ (old('test_type', $test->type ?? '') == 'Urine') ? 'selected' : '' }}>Urine</option>
                        <option value="Stool" {{ (old('test_type', $test->type ?? '') == 'Stool') ? 'selected' : '' }}>Stool</option>
                        <option value="Tissue" {{ (old('test_type', $test->type ?? '') == 'Tissue') ? 'selected' : '' }}>Tissue</option>
                        <option value="Fluid" {{ (old('test_type', $test->type ?? '') == 'Fluid') ? 'selected' : '' }}>Fluid</option>
                        <option value="Other" {{ (old('test_type', $test->type ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label for="test_head_id" class="hms-label">Test Head:</label>
                    <select id="test_head_id" name="test_head_id" class="hms-select" required>
                        <option value="">Select Test Head</option>
                        @foreach($testHeads as $head)
                            <option value="{{ $head->id }}" {{ (old('test_head_id', $test->test_head_id ?? '') == $head->id) ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="priority" class="hms-label">Priority:</label>
                    <select id="priority" name="priority" class="hms-select" required>
                        <option value="">Select Priority</option>
                        <option value="Routine" {{ (old('priority', $test->priority ?? '') == 'Routine') ? 'selected' : '' }}>Routine</option>
                        <option value="Urgent" {{ (old('priority', $test->priority ?? '') == 'Urgent') ? 'selected' : '' }}>Urgent</option>
                        <option value="STAT" {{ (old('priority', $test->priority ?? '') == 'STAT') ? 'selected' : '' }}>STAT (Critical)</option>
                    </select>
                </div>
                <div>
                    <label for="report_time" class="hms-label">Report Time (Hours):</label>
                    <input type="number" id="report_time" name="report_time" class="hms-input" placeholder="e.g., 24" min="0" value="{{ old('report_time', $test->report_time ?? '') }}" required>
                </div>
                <div class="md:col-span-2 lg:col-span-3 border-t border-gray-200 pt-4 mt-2">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Sample Collection Settings</h4>
                </div>
                <div>
                    <label for="sample_expiry_hours" class="hms-label">Sample Expiry Time (Hours):</label>
                    <input type="number" id="sample_expiry_hours" name="sample_expiry_hours" class="hms-input" placeholder="e.g., 24" min="1" value="{{ old('sample_expiry_hours', $test->sample_expiry_hours ?? 24) }}">
                    <p class="text-xs text-gray-500 mt-1">Hours after collection before sample expires</p>
                </div>
                <div>
                    <label for="sample_vial" class="hms-label">Sample Vial Type:</label>
                    <input type="text" id="sample_vial" name="sample_vial" class="hms-input" list="sample_vial_options"
                        placeholder="e.g., EDTA (Purple)"
                        value="{{ old('sample_vial', $test->sample_vial ?? '') }}">
                    <datalist id="sample_vial_options">
                        @foreach(['EDTA (Purple)', 'Plain (Red)', 'Fluoride (Gray)', 'Citrate (Blue)', 'Heparin (Green)', 'Urine Container', 'Stool Container', 'Serum Separator', 'General'] as $vialType)
                            <option value="{{ $vialType }}"></option>
                        @endforeach
                    </datalist>
                    <p class="text-xs text-gray-500 mt-1">For different tube types in one test, separate with commas (e.g. <em>EDTA (Purple), Serum Separator, Citrate (Blue)</em>)</p>
                </div>
                <div>
                    <label for="vial_volume" class="hms-label">Vial Volume:</label>
                    <input type="text" id="vial_volume" name="vial_volume" class="hms-input" placeholder="e.g., 3-5 ml" value="{{ old('vial_volume', $test->vial_volume ?? '') }}">
                    <p class="text-xs text-gray-500 mt-1">Volume to collect for this sample</p>
                </div>
                <div>
                    <label for="vials_required" class="hms-label">Vials Required:</label>
                    <input type="number" id="vials_required" name="vials_required" class="hms-input" placeholder="e.g., 1" min="1" max="10" value="{{ old('vials_required', $test->vials_required ?? 1) }}">
                    <p class="text-xs text-gray-500 mt-1">Same tube type repeated (e.g. 4× EDTA). Ignored when multiple types are comma-separated above.</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="hms-btn hms-btn-primary">
                    {{ isset($test) ? 'Update Test' : 'Add Test' }}
                </button>
            </div>
        </form>
    </div>

    @php $currentSearch = $search ?? ''; @endphp
    <div class="hms-panel hms-panel-padded" id="existing-tests-panel">
        <div class="flex flex-col gap-4 mb-4 sm:flex-row sm:items-start sm:justify-between">
            <h3 class="text-2xl font-semibold text-gray-800">Existing Tests</h3>
            <div class="w-full sm:max-w-md shrink-0">
                <div class="hms-search-bar w-full flex items-center gap-2" id="existing-tests-search-form">
                    <input type="text" id="existing-tests-search" class="hms-input flex-1" placeholder="Search by test name, head, type, priority…" autocomplete="off" value="{{ $currentSearch }}">
                    <button type="button" id="existing-tests-clear" class="hms-btn hms-btn-ghost{{ $currentSearch === '' ? ' hidden' : '' }}">Clear</button>
                </div>
            </div>
        </div>
        <div id="existing-tests-results">
            @include('laboratory.partials.existing_tests_list')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const results = document.getElementById('existing-tests-results');
            const searchInput = document.getElementById('existing-tests-search');
            const clearBtn = document.getElementById('existing-tests-clear');
            let searchTimer = null;

            function testsListUrl(query, page) {
                const url = new URL(window.location.pathname, window.location.origin);
                const q = String(query || '').trim();
                if (q !== '') {
                    url.searchParams.set('q', q);
                }
                if (page && page > 1) {
                    url.searchParams.set('page', String(page));
                }
                return url.toString();
            }

            function toggleClearButton(query) {
                if (!clearBtn) {
                    return;
                }
                clearBtn.classList.toggle('hidden', String(query || '').trim() === '');
            }

            function loadTestsList(url) {
                if (!results) {
                    return;
                }
                results.classList.add('opacity-50', 'pointer-events-none');

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Search failed');
                        }
                        return response.text();
                    })
                    .then(function (html) {
                        results.innerHTML = html;
                        window.history.replaceState({}, '', url);
                    })
                    .catch(function (error) {
                        console.error('Tests search error:', error);
                    })
                    .finally(function () {
                        results.classList.remove('opacity-50', 'pointer-events-none');
                    });
            }

            if (searchInput && results) {
                searchInput.addEventListener('input', function () {
                    const query = searchInput.value;
                    toggleClearButton(query);
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function () {
                        loadTestsList(testsListUrl(query, 1));
                    }, 350);
                });

                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(searchTimer);
                        loadTestsList(testsListUrl(searchInput.value, 1));
                    }
                });
            }

            if (clearBtn && searchInput) {
                clearBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    toggleClearButton('');
                    loadTestsList(testsListUrl('', 1));
                    searchInput.focus();
                });
            }

            if (results) {
                results.addEventListener('click', function (e) {
                    const pageLink = e.target.closest('.existing-tests-pagination a');
                    if (!pageLink || !pageLink.href) {
                        return;
                    }
                    e.preventDefault();
                    loadTestsList(pageLink.href);
                });
            }
        });
    </script>
@endsection