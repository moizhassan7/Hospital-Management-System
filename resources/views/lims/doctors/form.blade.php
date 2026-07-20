@extends('layouts.app')

@section('page_title', $doctor->exists ? 'Edit Referring Doctor' : 'Add Referring Doctor')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => $doctor->exists ? 'Edit referring doctor' : 'Add referring doctor',
        'subtitle' => 'Used when booking with a doctor referral and for commission matching',
        'backUrl' => route('pathology.lims_doctors.index'),
        'backLabel' => 'Back to doctors',
    ])

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="hms-panel hms-panel-padded max-w-xl">
        <form method="POST"
            action="{{ $doctor->exists ? route('pathology.lims_doctors.update', $doctor) : route('pathology.lims_doctors.store') }}">
            @csrf
            @if($doctor->exists)
                @method('PUT')
            @endif

            <div class="space-y-4 mb-6">
                <x-form.field label="Code" for="code" hint="Optional short code">
                    <input type="text" id="code" name="code" class="hms-input" value="{{ old('code', $doctor->code) }}" maxlength="32">
                </x-form.field>
                <x-form.field label="Doctor name" for="name" :required="true">
                    <input type="text" id="name" name="name" class="hms-input" value="{{ old('name', $doctor->name) }}" required maxlength="255">
                </x-form.field>
                <x-form.field label="Phone" for="phone">
                    <input type="text" id="phone" name="phone" class="hms-input" value="{{ old('phone', $doctor->phone) }}" maxlength="50">
                </x-form.field>
                <label class="hms-checkbox-row">
                    <input type="checkbox" name="is_active" value="1" class="hms-checkbox" {{ old('is_active', $doctor->is_active ?? true) ? 'checked' : '' }}>
                    <span class="hms-checkbox-label">Active</span>
                </label>
            </div>

            <div class="hms-form-actions">
                <button type="submit" class="hms-btn hms-btn-primary">{{ $doctor->exists ? 'Save changes' : 'Create doctor' }}</button>
                <a href="{{ route('pathology.lims_doctors.index') }}" class="hms-btn hms-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
