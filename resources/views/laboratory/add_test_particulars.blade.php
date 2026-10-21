@extends('layouts.app')

@section('title', 'Add ' . ($category ?? 'Laboratory') . ' test particulars')

@section('content')
    @php
        $label = $category ?? 'Laboratory';
        $hubRoute = $category == 'Pathology' ? route('pathology.index') : ($category == 'Radiology' ? route('radiology.index') : route('laboratory.index'));
    @endphp

    <x-ui.page-header :title="'Add ' . $label . ' test particulars'" subtitle="Parameters, units and normal ranges printed on each test report.">
        <x-slot:actions>
            <a href="{{ route('laboratory.test_particular_details') }}" class="btn btn-secondary">
                <x-ui.icon name="list" />All particulars
            </a>
            <a href="{{ $hubRoute }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />{{ $label }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <x-ui.panel title="Define new test particular" icon="plus" flush>
            <form action="{{ route('laboratory.add_test_particulars.store') }}" method="POST">
                @csrf
                <div class="panel-body">
                    <div class="form-grid">
                        <x-ui.field label="Test head" for="test_head" error="test_head_id" required class="md:col-span-4">
                            <select id="test_head" name="test_head_id" @class(['select', 'is-invalid' => $errors->has('test_head_id')]) required>
                                <option value="">Select test head</option>
                                @foreach($testHeads as $head)
                                    <option value="{{ $head->id }}" @selected(old('test_head_id') == $head->id)>{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Test name" for="test_name" error="test_id" required class="md:col-span-4">
                            <select id="test_name" name="test_id" @class(['select', 'is-invalid' => $errors->has('test_id')]) required disabled>
                                <option value="">Select test head first</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Particular name" for="particular_name" error="particular_name" required class="md:col-span-4">
                            <input type="text" id="particular_name" name="particular_name" @class(['input', 'is-invalid' => $errors->has('particular_name')]) placeholder="e.g. Haemoglobin" value="{{ old('particular_name') }}" autocomplete="off" required>
                        </x-ui.field>
                        <x-ui.field label="Unit" for="unit" error="unit" class="md:col-span-4">
                            <input type="text" id="unit" name="unit" class="input" placeholder="e.g. g/dL" value="{{ old('unit') }}" autocomplete="off">
                        </x-ui.field>
                        <x-ui.field label="Normal range (min)" for="normal_range_min" error="normal_range_min" class="md:col-span-4">
                            <input type="number" id="normal_range_min" name="normal_range_min" class="input input-num" placeholder="e.g. 12.0" step="0.01" value="{{ old('normal_range_min') }}">
                        </x-ui.field>
                        <x-ui.field label="Normal range (max)" for="normal_range_max" error="normal_range_max" class="md:col-span-4">
                            <input type="number" id="normal_range_max" name="normal_range_max" class="input input-num" placeholder="e.g. 16.0" step="0.01" value="{{ old('normal_range_max') }}">
                        </x-ui.field>
                        <x-ui.field label="Reference text / notes" for="reference_text" error="reference_text" class="md:col-span-12">
                            <textarea id="reference_text" name="reference_text" rows="2" class="input" placeholder="e.g. Varies by age and gender">{{ old('reference_text') }}</textarea>
                        </x-ui.field>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="plus" />Add particular
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing test particulars" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#particulars-table">{{ count($testParticulars) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter particulars" data-table-filter="#particulars-table" />
            </div>
            <div class="table-wrap">
                <table id="particulars-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Particular</th>
                            <th>Test</th>
                            <th>Test head</th>
                            <th>Unit</th>
                            <th>Normal range</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($testParticulars as $index => $particular)
                            <tr>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td class="cell-strong">{{ $particular->name }}</td>
                                <td>{{ $particular->test->name ?? '—' }}</td>
                                <td>{{ $particular->test->testHead->name ?? '—' }}</td>
                                <td>{{ $particular->unit ?: '—' }}</td>
                                <td class="tabular-nums">
                                    @if ($particular->normal_range_min !== null || $particular->normal_range_max !== null)
                                        {{ $particular->normal_range_min ?? '—' }} <span class="muted">—</span> {{ $particular->normal_range_max ?? '—' }}
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="#" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <a href="#" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="7" class="p-0">
                                    <x-ui.empty-state icon="list" title="No particulars yet" description="Add the first parameter for a test using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
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

            testHeadSelect.addEventListener('change', (e) => {
                populateTestNames(e.target.value);
            });

            const initialTestHeadId = testHeadSelect.value;
            if (initialTestHeadId) {
                populateTestNames(initialTestHeadId);
            }
        });
    </script>
@endsection
