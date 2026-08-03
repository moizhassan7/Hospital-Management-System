@extends('layouts.app')

@section('page_title', 'Lab Settings')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'Lab settings',
        'subtitle' => 'Branding and report doctors',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back to Pathology',
    ])

    <div class="hms-tabs">
        <a href="{{ route('pathology.settings.index', ['tab' => 'branding']) }}"
            class="hms-tab {{ $tab === 'branding' ? 'is-active' : '' }}">Lab branding</a>
        <a href="{{ route('pathology.settings.index', ['tab' => 'doctors']) }}"
            class="hms-tab {{ $tab === 'doctors' ? 'is-active' : '' }}">Report doctors</a>
    </div>

    @if($tab === 'branding')
        <div class="hms-panel mb-5">
            <div class="hms-panel-header">
                <div>
                    <h3 class="hms-panel-title">Logo, name & address</h3>
                    <p class="hms-panel-subtitle">Updates apply across the website, login page, and all lab reports.</p>
                </div>
            </div>
            <div class="hms-panel-body">
                <form action="{{ route('pathology.settings.branding.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col md:flex-row gap-6 mb-6">
                        <div class="shrink-0 flex flex-col gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="flex flex-col items-center gap-3 mb-2">
                                <img src="{{ app(\App\Services\HospitalBrandingService::class)->logoUrl() }}" alt="Current logo" class="h-16 w-16 object-contain rounded-lg bg-white p-1 border">
                                <x-form.field label="Upload new logo" for="logo" hint="PNG/JPG up to 10MB">
                                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp" class="hms-input text-sm">
                                    @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </x-form.field>
                            </div>
                            
                            <div class="flex flex-col items-center gap-3 mb-2 border-t pt-3">
                                @if(app(\App\Services\HospitalBrandingService::class)->get('header_image'))
                                    <img src="{{ asset(app(\App\Services\HospitalBrandingService::class)->get('header_image')) }}" alt="Current header" class="h-12 w-full object-contain bg-white p-1 border">
                                @endif
                                <x-form.field label="PDF Header Image" for="header_image" hint="Printed at top of PDFs">
                                    <input type="file" id="header_image" name="header_image" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp" class="hms-input text-sm">
                                    @error('header_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </x-form.field>
                            </div>

                            <div class="flex flex-col items-center gap-3 border-t pt-3">
                                @if(app(\App\Services\HospitalBrandingService::class)->get('footer_image'))
                                    <img src="{{ asset(app(\App\Services\HospitalBrandingService::class)->get('footer_image')) }}" alt="Current footer" class="h-12 w-full object-contain bg-white p-1 border">
                                @endif
                                <x-form.field label="PDF Footer Image" for="footer_image" hint="Printed at bottom of PDFs">
                                    <input type="file" id="footer_image" name="footer_image" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp" class="hms-input text-sm">
                                    @error('footer_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </x-form.field>
                            </div>
                        </div>
                        <div class="flex-1 hms-form-grid">
                            <x-form.field label="Full lab name" for="name" :required="true">
                                <input type="text" id="name" name="name" class="hms-input" value="{{ old('name', $settings['name']) }}" required>
                            </x-form.field>
                            <x-form.field label="Short name" for="short_name" :required="true">
                                <input type="text" id="short_name" name="short_name" class="hms-input" value="{{ old('short_name', $settings['short_name']) }}" required>
                            </x-form.field>
                            <x-form.field label="Tagline" for="tagline">
                                <input type="text" id="tagline" name="tagline" class="hms-input" value="{{ old('tagline', $settings['tagline']) }}">
                            </x-form.field>
                            <x-form.field label="City" for="city">
                                <input type="text" id="city" name="city" class="hms-input" value="{{ old('city', $settings['city']) }}">
                            </x-form.field>
                            <x-form.field label="Phone" for="phone" class="hms-field-span-2">
                                <input type="text" id="phone" name="phone" class="hms-input" value="{{ old('phone', $settings['phone'] ?? '') }}">
                            </x-form.field>
                            <x-form.field label="Email" for="email">
                                <input type="email" id="email" name="email" class="hms-input" value="{{ old('email', $settings['email'] ?? '') }}">
                            </x-form.field>
                            <x-form.field label="Full address" for="address" class="md:col-span-2 lg:col-span-3">
                                <textarea id="address" name="address" rows="3" class="hms-textarea">{{ old('address', $settings['address'] ?? '') }}</textarea>
                            </x-form.field>
                        </div>
                    </div>

                    <div class="hms-form-actions">
                        <button type="submit" class="hms-btn hms-btn-primary">Save branding</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="hms-panel">
                <div class="hms-panel-header">
                    <h3 class="hms-panel-title">Add report doctor</h3>
                </div>
                <div class="hms-panel-body">
                    <form action="{{ route('pathology.settings.doctors.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <x-form.field label="Doctor name" for="name" :required="true">
                            <input type="text" id="name" name="name" class="hms-input" value="{{ old('name') }}" required>
                        </x-form.field>
                        <x-form.field label="Designation" for="designation" hint="e.g. Consultant Pathologist">
                            <input type="text" id="designation" name="designation" class="hms-input" value="{{ old('designation') }}">
                        </x-form.field>
                        <x-form.field label="Qualifications" for="qualifications" hint="e.g. MBBS, FCPS">
                            <input type="text" id="qualifications" name="qualifications" class="hms-input" value="{{ old('qualifications') }}">
                        </x-form.field>
                        <x-form.field label="Phone" for="phone">
                            <input type="text" id="phone" name="phone" class="hms-input" value="{{ old('phone') }}">
                        </x-form.field>
                        <x-form.field label="Display order" for="sort_order">
                            <input type="number" id="sort_order" name="sort_order" class="hms-input" value="{{ old('sort_order', 0) }}" min="0">
                        </x-form.field>
                        <label class="hms-checkbox-row">
                            <input type="checkbox" name="is_active" value="1" class="hms-checkbox" checked>
                            <span class="hms-checkbox-label">Show on lab reports</span>
                        </label>
                        <button type="submit" class="hms-btn hms-btn-primary w-full">Add doctor</button>
                    </form>
                </div>
            </div>

            <div class="hms-panel hms-panel-flush">
                <div class="hms-panel-header">
                    <h3 class="hms-panel-title">Doctors on reports ({{ $doctors->count() }})</h3>
                </div>
                @if($doctors->isEmpty())
                    <div class="hms-re-empty">
                        <p class="hms-empty-title">No doctors added yet</p>
                        <p class="hms-empty-desc">Added doctors appear at the bottom of every pathology lab report.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($doctors as $doctor)
                            <div class="p-5">
                                <form action="{{ route('pathology.settings.doctors.update', $doctor) }}" method="POST" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="hms-form-grid-2">
                                        <x-form.field label="Name" :for="'doctor_name_'.$doctor->id">
                                            <input type="text" name="name" id="doctor_name_{{ $doctor->id }}" class="hms-input" value="{{ $doctor->name }}" required>
                                        </x-form.field>
                                        <x-form.field label="Designation" :for="'doctor_des_'.$doctor->id">
                                            <input type="text" name="designation" id="doctor_des_{{ $doctor->id }}" class="hms-input" value="{{ $doctor->designation }}">
                                        </x-form.field>
                                        <x-form.field label="Qualifications" :for="'doctor_qual_'.$doctor->id">
                                            <input type="text" name="qualifications" id="doctor_qual_{{ $doctor->id }}" class="hms-input" value="{{ $doctor->qualifications }}">
                                        </x-form.field>
                                        <x-form.field label="Phone" :for="'doctor_phone_'.$doctor->id">
                                            <input type="text" name="phone" id="doctor_phone_{{ $doctor->id }}" class="hms-input" value="{{ $doctor->phone }}">
                                        </x-form.field>
                                        <x-form.field label="Order" :for="'doctor_sort_'.$doctor->id">
                                            <input type="number" name="sort_order" id="doctor_sort_{{ $doctor->id }}" class="hms-input" value="{{ $doctor->sort_order }}" min="0">
                                        </x-form.field>
                                        <div class="flex items-end">
                                            <label class="hms-checkbox-row pb-2">
                                                <input type="checkbox" name="is_active" value="1" class="hms-checkbox" {{ $doctor->is_active ? 'checked' : '' }}>
                                                <span class="hms-checkbox-label">Active</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        <button type="submit" class="hms-btn hms-btn-primary hms-btn-sm">Save</button>
                                    </div>
                                </form>
                                <form action="{{ route('pathology.settings.doctors.destroy', $doctor) }}" method="POST" class="mt-2"
                                    onsubmit="return confirm('Remove this doctor from lab reports?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="hms-btn hms-btn-danger hms-btn-sm">Delete</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
