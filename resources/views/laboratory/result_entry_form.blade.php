@extends('layouts.app')

@section('title', ($isReadOnly ? 'Report: ' : 'Enter results: ') . $test->name)

@section('content')
    @php $isQuantitative = $test->report_format === 'Quantitative' || ! $test->report_format; @endphp

    <x-ui.page-header :title="$test->name" :subtitle="$isReadOnly ? 'Saved report — values are read-only.' : 'Enter a result for every parameter, then save. Enter and arrow keys move between rows.'">
        <x-slot:meta>
            @if ($isReadOnly)
                <x-ui.badge tone="success" dot>Report saved</x-ui.badge>
            @else
                <x-ui.badge tone="warning" dot>Awaiting results</x-ui.badge>
            @endif
            <x-ui.badge>{{ $test->report_format ?: 'Quantitative' }}</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('laboratory.result_entry.search') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patient search
            </a>
            @if ($isReadOnly)
                <a href="{{ route('laboratory.print_report', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" target="_blank" class="btn btn-primary">
                    <x-ui.icon name="printer" />Print report
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.patient-band class="mb-5" :mr="$labPatient->mr_no" :name="$labPatient->patient_name" :age="$labPatient->age" :gender="$labPatient->gender">
        <div class="band-cell">
            <span class="band-label">Test</span>
            <span class="band-value">{{ $test->name }}</span>
        </div>
    </x-ui.patient-band>

    <form action="{{ route('laboratory.result_entry.save', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        @if ($isQuantitative)
            <x-ui.panel title="Results" :description="$test->testParticulars->count() . ' parameters'" icon="test-tube" flush>
                <div class="table-wrap">
                    <table class="table" data-result-grid>
                        <thead>
                            <tr>
                                <th class="num w-12">#</th>
                                <th>Parameter</th>
                                <th class="w-56">Result</th>
                                <th>Unit</th>
                                <th>Reference range</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($test->testParticulars as $particular)
                                <tr>
                                    <td class="num muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <label for="result_{{ $particular->id }}" class="cell-strong">{{ $particular->name }}</label>
                                    </td>
                                    <td>
                                        <input type="text" id="result_{{ $particular->id }}" name="result_{{ $particular->id }}"
                                            class="input input-sm tabular-nums" placeholder="Enter result" autocomplete="off"
                                            value="{{ $existingResults[$particular->id] ?? '' }}" @if ($isReadOnly) readonly @endif required>
                                    </td>
                                    <td class="muted nowrap">{{ $particular->unit ?: '—' }}</td>
                                    <td class="nowrap tabular-nums">
                                        @if ($particular->normal_range_min || $particular->normal_range_max)
                                            {{ $particular->normal_range_min }} – {{ $particular->normal_range_max }}
                                        @else
                                            <span class="muted">{{ $particular->reference_text ?: '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (! $isReadOnly)
                    <x-slot:footer class="is-sticky">
                        <span class="footer-note">Every parameter needs a result before saving.</span>
                        <a href="{{ route('laboratory.result_entry.search') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary" data-busy-label="Saving results…">
                            <x-ui.icon name="save" />Save results
                        </button>
                    </x-slot:footer>
                @endif
            </x-ui.panel>
        @else
            {{-- Descriptive format (Radiology / Cardiology) --}}
            <x-ui.panel title="Findings and report" icon="file-text">
                <div class="form-grid">
                    @foreach ($test->testParticulars as $particular)
                        @if (strtolower($particular->name) === 'findings')
                            <div class="md:col-span-12">
                                <label for="result_{{ $particular->id }}" class="field-label mb-1.5">Findings</label>
                                <div class="quill_editor" style="height: 300px;"></div>
                                <textarea id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" class="quill-hidden" style="display: none;">{{ $existingResults[$particular->id] ?? $test->template }}</textarea>
                            </div>
                        @else
                            <x-ui.field :label="$particular->name" :for="'result_' . $particular->id" class="md:col-span-6">
                                <input type="text" id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" class="input"
                                    placeholder="Enter {{ $particular->name }}" value="{{ $existingResults[$particular->id] ?? '' }}" @if ($isReadOnly) readonly @endif>
                            </x-ui.field>
                        @endif
                    @endforeach
                </div>

                @if ($test->report_format === 'Radiology')
                    <div class="mt-6 border-t border-line-soft pt-5">
                        @if (! $isReadOnly)
                            <x-ui.field label="Images or DICOM screenshots" for="test_images" hint="Optional. You can select several images at once.">
                                <input type="file" id="test_images" name="test_images[]" multiple accept="image/*" class="input">
                            </x-ui.field>
                        @endif

                        @if (isset($testImages) && $testImages->count() > 0)
                            <div class="mt-4">
                                <h4 class="section-heading">Attached images</h4>
                                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                    @foreach ($testImages as $image)
                                        <a href="{{ asset('storage/' . $image->image_path) }}" target="_blank" class="group relative block overflow-hidden rounded-lg border border-line">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Attached image {{ $loop->iteration }}" class="h-32 w-full object-cover">
                                            <span class="absolute inset-0 grid place-items-center bg-ink-900/0 text-white opacity-0 transition group-hover:bg-ink-900/35 group-hover:opacity-100">
                                                <x-ui.icon name="eye" class="size-6" />
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if (! $isReadOnly)
                    <x-slot:footer class="is-sticky">
                        <a href="{{ route('laboratory.result_entry.search') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary" data-busy-label="Saving results…">
                            <x-ui.icon name="save" />Save results
                        </button>
                    </x-slot:footer>
                @endif
            </x-ui.panel>
        @endif
    </form>

    @if ($isQuantitative && ! $isReadOnly)
        @push('scripts')
            <script>
                // Spreadsheet-style movement between result cells; Enter on the last cell submits as before.
                document.addEventListener('DOMContentLoaded', function () {
                    const cells = [...document.querySelectorAll('[data-result-grid] input[name^="result_"]')];
                    cells.forEach((cell, i) => {
                        cell.addEventListener('keydown', (event) => {
                            const next = cells[i + 1];
                            const prev = cells[i - 1];
                            if ((event.key === 'Enter' || event.key === 'ArrowDown') && next) {
                                event.preventDefault();
                                next.focus();
                                next.select();
                            } else if (event.key === 'ArrowUp' && prev) {
                                event.preventDefault();
                                prev.focus();
                                prev.select();
                            }
                        });
                    });
                });
            </script>
        @endpush
    @endif

    @if ($test->report_format === 'Radiology' || $test->report_format === 'Cardiology')
        @push('styles')
            <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
            <style>
                .quill_editor {
                    min-height: 200px;
                    background-color: #fff;
                    font-family: var(--font-sans);
                    font-size: 14px;
                }

                .ql-toolbar.ql-snow {
                    border-color: var(--color-line-strong);
                    border-top-left-radius: 6px;
                    border-top-right-radius: 6px;
                    background-color: var(--color-subtle);
                }

                .ql-container.ql-snow {
                    border-color: var(--color-line-strong);
                    border-bottom-left-radius: 6px;
                    border-bottom-right-radius: 6px;
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

                            @if (! $isReadOnly)
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
