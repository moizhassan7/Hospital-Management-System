@extends('layouts.app')

@section('title', isset($test) ? 'Edit test' : 'Manage ' . ($category ?? 'Laboratory') . ' tests')

@section('content')
    @php
        $label = $category ?? 'Laboratory';
        $hubRoute = $category == 'Pathology' ? route('pathology.index') : ($category == 'Radiology' ? route('radiology.index') : route('laboratory.index'));
        $listRoute = $category == 'Pathology' ? route('pathology.manage_test') : ($category == 'Radiology' ? route('radiology.manage_test') : route('laboratory.manage_test'));
        $currency = get_setting('currency_symbol', 'Rs');
        $priorityTone = fn ($priority) => match ($priority) {
            'STAT' => 'danger',
            'Urgent' => 'warning',
            default => 'neutral',
        };
    @endphp

    <x-ui.page-header :title="isset($test) ? 'Edit test' : 'Manage ' . $label . ' tests'" :subtitle="$category == 'Radiology' ? 'Scans, report format and the template used when entering findings.' : 'Test codes, fees, heads and the format used on result reports.'">
        <x-slot:actions>
            <a href="{{ $hubRoute }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />{{ $label }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <x-ui.panel :title="isset($test) ? 'Edit ' . $test->name : 'Add ' . $label . ' test'" :icon="isset($test) ? 'pencil' : 'plus'" flush>
            @if(isset($test))
                <form action="{{ route('laboratory.manage_test.update', $test->id) }}" method="POST">
                    @csrf
                    @method('PUT')
            @else
                <form action="{{ route('laboratory.manage_test.store') }}" method="POST">
                    @csrf
            @endif
                    <input type="hidden" name="category" value="{{ $category ?? 'Pathology' }}">
                    <div class="panel-body">
                        <div class="form-grid">
                            <x-ui.field label="Test ID" for="test_id" error="test_id" required class="md:col-span-4">
                                <input type="text" id="test_id" name="test_id" @class(['input input-mono', 'is-invalid' => $errors->has('test_id')]) placeholder="e.g. T001" value="{{ old('test_id', $test->test_id ?? '') }}" autocomplete="off" required>
                            </x-ui.field>
                            <x-ui.field label="Test name" for="test_name" error="test_name" required class="md:col-span-8">
                                <input type="text" id="test_name" name="test_name" @class(['input', 'is-invalid' => $errors->has('test_name')]) placeholder="e.g. Complete Blood Count" value="{{ old('test_name', $test->name ?? '') }}" autocomplete="off" required>
                            </x-ui.field>
                            <x-ui.field :label="'Price (' . $currency . ')'" for="test_price" error="test_price" required class="md:col-span-4">
                                <input type="number" id="test_price" name="test_price" @class(['input input-num', 'is-invalid' => $errors->has('test_price')]) placeholder="e.g. 50.00" min="0" step="0.01" value="{{ old('test_price', $test->price ?? '') }}" required>
                            </x-ui.field>
                            <x-ui.field label="Test type" for="test_type" error="test_type" required class="md:col-span-4">
                                <select id="test_type" name="test_type" @class(['select', 'is-invalid' => $errors->has('test_type')]) required>
                                    <option value="">Select type</option>
                                    <option value="Blood" @selected(old('test_type', $test->type ?? '') == 'Blood')>Blood</option>
                                    <option value="Urine" @selected(old('test_type', $test->type ?? '') == 'Urine')>Urine</option>
                                    <option value="Imaging" @selected(old('test_type', $test->type ?? '') == 'Imaging')>Imaging</option>
                                    <option value="Other" @selected(old('test_type', $test->type ?? '') == 'Other')>Other</option>
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Test head" for="test_head_id" error="test_head_id" required class="md:col-span-4">
                                <select id="test_head_id" name="test_head_id" @class(['select', 'is-invalid' => $errors->has('test_head_id')]) required>
                                    <option value="">Select test head</option>
                                    @foreach($testHeads as $head)
                                        <option value="{{ $head->id }}" @selected(old('test_head_id', $test->test_head_id ?? '') == $head->id)>{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Report format" for="report_format" error="report_format" required class="md:col-span-4">
                                <select id="report_format" name="report_format" @class(['select', 'is-invalid' => $errors->has('report_format')]) required onchange="toggleTemplateEditor()">
                                    <option value="Quantitative" @selected(old('report_format', $test->report_format ?? '') == 'Quantitative')>Quantitative (Pathology)</option>
                                    <option value="Radiology" @selected(old('report_format', $test->report_format ?? '') == 'Radiology')>Radiology (Descriptive)</option>
                                    <option value="Cardiology" @selected(old('report_format', $test->report_format ?? '') == 'Cardiology')>Cardiology (Descriptive)</option>
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Priority" for="priority" error="priority" required class="md:col-span-4">
                                <select id="priority" name="priority" @class(['select', 'is-invalid' => $errors->has('priority')]) required>
                                    <option value="">Select priority</option>
                                    <option value="Routine" @selected(old('priority', $test->priority ?? '') == 'Routine')>Routine</option>
                                    <option value="Urgent" @selected(old('priority', $test->priority ?? '') == 'Urgent')>Urgent</option>
                                    <option value="STAT" @selected(old('priority', $test->priority ?? '') == 'STAT')>STAT (Critical)</option>
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Report time (hours)" for="report_time" error="report_time" required class="md:col-span-4">
                                <input type="number" id="report_time" name="report_time" @class(['input input-num', 'is-invalid' => $errors->has('report_time')]) placeholder="e.g. 24" min="0" value="{{ old('report_time', $test->report_time ?? '') }}" required>
                            </x-ui.field>
                            <div class="md:col-span-12" id="template_container" style="display: none;">
                                <x-ui.field label="Report template (for Radiology / Cardiology findings)" for="template" error="template">
                                    <div id="quill_editor" class="bg-white rounded-lg border border-line" style="height: 250px;"></div>
                                    <textarea id="template" name="template" class="hidden">{{ old('template', $test->template ?? '') }}</textarea>
                                </x-ui.field>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        @isset($test)
                            <a href="{{ $listRoute }}" class="btn btn-ghost">Cancel</a>
                        @endisset
                        <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                            <x-ui.icon name="{{ isset($test) ? 'save' : 'plus' }}" />{{ isset($test) ? 'Update test' : 'Add test' }}
                        </button>
                    </div>
                </form>
        </x-ui.panel>

        <x-ui.panel title="Existing tests" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#tests-table">{{ count($tests) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter tests" data-table-filter="#tests-table" />
            </div>
            <div class="table-wrap">
                <table id="tests-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Test ID</th>
                            <th>Name</th>
                            <th class="num">Price</th>
                            <th>Type</th>
                            <th>Test head</th>
                            <th>Priority</th>
                            <th class="num">Report (h)</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tests as $index => $row)
                            <tr @class(['bg-brand-50/50' => isset($test) && $test->id === $row->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $row->test_id }}</span></td>
                                <td class="cell-strong">{{ $row->name }}</td>
                                <td class="num"><x-ui.money :amount="$row->price" /></td>
                                <td>{{ $row->type }}</td>
                                <td>{{ $row->testHead->name ?? '—' }}</td>
                                <td><x-ui.badge :tone="$priorityTone($row->priority)">{{ $row->priority }}</x-ui.badge></td>
                                <td class="num">{{ $row->report_time }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('laboratory.manage_test.edit', $row->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <form action="{{ route('laboratory.manage_test.destroy', $row->id) }}" method="POST" class="inline"
                                            data-confirm="“{{ $row->name }}” will no longer be offered on new registrations. Saved results stay on record."
                                            data-confirm-title="Delete this test?" data-confirm-label="Delete test">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="9" class="p-0">
                                    <x-ui.empty-state icon="flask" title="No tests yet" description="Add a test head first, then define the tests offered under it." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        var quill = new Quill('#quill_editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        var templateTextarea = document.getElementById('template');

        if (templateTextarea.value) {
            quill.root.innerHTML = templateTextarea.value;
        }

        quill.on('text-change', function() {
            templateTextarea.value = quill.root.innerHTML;
        });

        function toggleTemplateEditor() {
            var format = document.getElementById('report_format').value;
            var container = document.getElementById('template_container');
            if (format === 'Radiology' || format === 'Cardiology') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleTemplateEditor();
        });
    </script>
@endsection
