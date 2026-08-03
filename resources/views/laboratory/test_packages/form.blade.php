@extends('layouts.app')

@section('page_title', isset($testPackage) ? 'Edit Test Package' : 'Create Test Package')

@section('content')
    <div class="hms-page-toolbar flex items-center justify-between mb-6">
        <div>
            <h2 class="hms-page-heading">{{ isset($testPackage) ? 'Edit Test Package' : 'Create Test Package' }}</h2>
            <p class="hms-page-subheading">{{ isset($testPackage) ? 'Modify an existing package' : 'Add a new package of tests' }}</p>
        </div>
        <a href="{{ route('pathology.test_packages.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Packages
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

    <div class="hms-panel hms-panel-padded mb-5">
        <form action="{{ isset($testPackage) ? route('pathology.test_packages.update', $testPackage->id) : route('pathology.test_packages.store') }}" method="POST">
            @csrf
            @if(isset($testPackage))
                @method('PUT')
            @endif

            <div class="hms-form-grid mb-6">
                <div>
                    <label for="name" class="hms-label">Package Name <span class="hms-required">*</span>:</label>
                    <input type="text" id="name" name="name" class="hms-input" placeholder="e.g., Full Body Profile" value="{{ old('name', $testPackage->name ?? '') }}" required>
                </div>
                
                <div>
                    <label for="price" class="hms-label">Package Price (PKR) <span class="hms-required">*</span>:</label>
                    <input type="number" id="price" name="price" class="hms-input" placeholder="e.g., 5000" min="0" step="1" value="{{ old('price', isset($testPackage) ? round($testPackage->price) : '') }}" required>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label for="description" class="hms-label">Description:</label>
                    <textarea id="description" name="description" class="hms-input" rows="2" placeholder="Optional details about this package">{{ old('description', $testPackage->description ?? '') }}</textarea>
                </div>

                <div>
                    <label for="is_active" class="hms-label">Status:</label>
                    <select id="is_active" name="is_active" class="hms-select">
                        <option value="1" {{ old('is_active', $testPackage->is_active ?? true) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !old('is_active', $testPackage->is_active ?? true) ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Included Tests</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6 max-h-96 overflow-y-auto p-4 border border-gray-200 rounded-lg bg-gray-50">
                @php
                    $selectedTestIds = isset($testPackage) ? $testPackage->tests->pluck('id')->toArray() : [];
                    if (old('test_ids')) {
                        $selectedTestIds = old('test_ids');
                    }
                @endphp
                
                @foreach($tests as $test)
                    <label class="inline-flex items-center p-3 bg-white rounded shadow-sm border border-gray-100 cursor-pointer hover:bg-blue-50 transition-colors">
                        <input type="checkbox" name="test_ids[]" value="{{ $test->id }}" class="hms-checkbox" {{ in_array($test->id, $selectedTestIds) ? 'checked' : '' }}>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-800">{{ $test->name }}</span>
                            <span class="block text-xs text-gray-500">PKR {{ number_format($test->price, 0) }}</span>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('pathology.test_packages.index') }}" class="hms-btn hms-btn-secondary">Cancel</a>
                <button type="submit" class="hms-btn hms-btn-primary">
                    {{ isset($testPackage) ? 'Update Package' : 'Create Package' }}
                </button>
            </div>
        </form>
    </div>
@endsection
