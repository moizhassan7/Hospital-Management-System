@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">{{ $isReadOnly ? 'Report for' : 'Enter Results for' }} {{ $test->name }}</h2>
        <div class="flex items-center space-x-3">
            @if($isReadOnly)
                <a href="{{ route('laboratory.print_report', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-md transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4"></path></svg>
                    Print Report
                </a>
            @endif
            <a href="{{ route('laboratory.result_entry.search') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
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

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient: {{ $labPatient->patient_name }} (MR: {{ $labPatient->mr_no }})</h3>

        <form action="{{ route('laboratory.result_entry.save', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @if($test->report_format === 'Quantitative' || !$test->report_format)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    @foreach($test->testParticulars as $particular)
                        <div>
                            <label for="result_{{ $particular->id }}" class="block text-gray-700 text-sm font-bold mb-2">
                                {{ $particular->name }}
                                @if($particular->normal_range_min || $particular->normal_range_max)
                                    <span class="text-xs text-gray-500">({{ $particular->normal_range_min }} - {{ $particular->normal_range_max }} {{ $particular->unit }})</span>
                                @else
                                    <span class="text-xs text-gray-500">({{ $particular->reference_text }})</span>
                                @endif
                            </label>
                            <input type="text" id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" 
                                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @if($isReadOnly) bg-gray-100 @endif" 
                                placeholder="Enter result" value="{{ $existingResults[$particular->id] ?? '' }}" @if($isReadOnly) readonly @endif required>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Descriptive Format (Radiology / Cardiology) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    @foreach($test->testParticulars as $particular)
                        @if(strtolower($particular->name) === 'findings')
                            <div class="md:col-span-2 mb-4">
                                <label for="result_{{ $particular->id }}" class="block text-gray-700 text-sm font-bold mb-2">Findings:</label>
                                <div class="quill_editor bg-white border rounded-lg" style="height: 300px;"></div>
                                <textarea id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" class="quill-hidden" style="display: none;">{{ $existingResults[$particular->id] ?? $test->template }}</textarea>
                            </div>
                        @else
                            <div>
                                <label for="result_{{ $particular->id }}" class="block text-gray-700 text-sm font-bold mb-2">{{ $particular->name }}:</label>
                                <input type="text" id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" 
                                    class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @if($isReadOnly) bg-gray-100 @endif" 
                                    placeholder="Enter {{ $particular->name }}" value="{{ $existingResults[$particular->id] ?? '' }}" @if($isReadOnly) readonly @endif>
                            </div>
                        @endif
                    @endforeach
                </div>
                
                @if($test->report_format === 'Radiology')
                    <div class="mb-6">
                        @if(!$isReadOnly)
                            <label for="test_images" class="block text-gray-700 text-sm font-bold mb-2">Upload Images/DICOM Screenshots (Optional):</label>
                            <input type="file" id="test_images" name="test_images[]" multiple accept="image/*" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Save Results
                    </button>
                </div>
            @endif
        </form>
    </div>

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