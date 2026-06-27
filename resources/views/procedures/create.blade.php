@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">
            @isset($procedure)
                Edit Procedure: {{ $procedure->name }}
            @else
                Add New Procedure
            @endisset
        </h2></div>
        <a href="{{ route('procedures.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Procedures
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
        <form action="{{ isset($procedure) ? route('procedures.update', $procedure->id) : route('procedures.store') }}" method="POST">
            @csrf
            @isset($procedure)
                @method('PUT')
            @endisset

            <div class="hms-form-grid mb-6">
                <div>
                    <label for="type" class="hms-label">Procedure Type:</label>
                    <select id="type" name="type" class="hms-select" required>
                        <option value="">Select Type</option>
                        <option value="Major" {{ old('type', $procedure->type ?? '') == 'Major' ? 'selected' : '' }}>Major</option>
                        <option value="Minor" {{ old('type', $procedure->type ?? '') == 'Minor' ? 'selected' : '' }}>Minor</option>
                    </select>
                </div>
                <div>
                    <label for="department_id" class="hms-label">Department:</label>
                    <select id="department_id" name="department_id" class="hms-select" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $procedure->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="speciality_id" class="hms-label">Speciality:</label>
                    <select id="speciality_id" name="speciality_id" class="hms-select">
                        <option value="">Select Speciality</option>
                        @foreach($specialities as $spec)
                            <option value="{{ $spec->id }}" {{ old('speciality_id', $procedure->speciality_id ?? '') == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="room_location" class="hms-label">Room Location:</label>
                    <input type="text" id="room_location" name="room_location" class="hms-input" placeholder="e.g., OT-1" value="{{ old('room_location', $procedure->room_location ?? '') }}">
                </div>
                <div>
                    <label for="name" class="hms-label">Procedure Name:</label>
                    <input type="text" id="name" name="name" class="hms-input" placeholder="e.g., Angioplasty" value="{{ old('name', $procedure->name ?? '') }}" required>
                </div>
                <div>
                    <label for="performed_for" class="hms-label">Performed For:</label>
                    <select id="performed_for" name="performed_for" class="hms-select" required>
                        <option value="">Select Target</option>
                        <option value="General" {{ old('performed_for', $procedure->performed_for ?? '') == 'General' ? 'selected' : '' }}>General</option>
                        <option value="Male" {{ old('performed_for', $procedure->performed_for ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('performed_for', $procedure->performed_for ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
            </div>

            <h3 class="hms-section-title mt-8">Fee Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="general_normal_fee" class="hms-label">General Normal Fee ($):</label>
                    <input type="number" id="general_normal_fee" name="general_normal_fee" class="hms-input" value="{{ old('general_normal_fee', $procedure->general_normal_fee ?? '0.00') }}" required min="0" step="0.01">
                </div>
                <div>
                    <label for="general_emergency_fee" class="hms-label">General Emergency Fee ($):</label>
                    <input type="number" id="general_emergency_fee" name="general_emergency_fee" class="hms-input" value="{{ old('general_emergency_fee', $procedure->general_emergency_fee ?? '0.00') }}" required min="0" step="0.01">
                </div>
                <div>
                    <label for="welfare_normal_fee" class="hms-label">Welfare Normal Fee ($):</label>
                    <input type="number" id="welfare_normal_fee" name="welfare_normal_fee" class="hms-input" value="{{ old('welfare_normal_fee', $procedure->welfare_normal_fee ?? '0.00') }}" required min="0" step="0.01">
                </div>
                <div>
                    <label for="welfare_emergency_fee" class="hms-label">Welfare Emergency Fee ($):</label>
                    <input type="number" id="welfare_emergency_fee" name="welfare_emergency_fee" class="hms-input" value="{{ old('welfare_emergency_fee', $procedure->welfare_emergency_fee ?? '0.00') }}" required min="0" step="0.01">
                </div>
            </div>

            <h3 class="hms-section-title mt-8">Doctor's Percentage</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="general_normal_doctor_percentage" class="hms-label">General Normal (%):</label>
                    <input type="number" id="general_normal_doctor_percentage" name="general_normal_doctor_percentage" class="hms-input" value="{{ old('general_normal_doctor_percentage', $procedure->general_normal_doctor_percentage ?? '0') }}" required min="0" max="100" step="0.01">
                </div>
                <div>
                    <label for="general_emergency_doctor_percentage" class="hms-label">General Emergency (%):</label>
                    <input type="number" id="general_emergency_doctor_percentage" name="general_emergency_doctor_percentage" class="hms-input" value="{{ old('general_emergency_doctor_percentage', $procedure->general_emergency_doctor_percentage ?? '0') }}" required min="0" max="100" step="0.01">
                </div>
                <div>
                    <label for="welfare_normal_doctor_percentage" class="hms-label">Welfare Normal (%):</label>
                    <input type="number" id="welfare_normal_doctor_percentage" name="welfare_normal_doctor_percentage" class="hms-input" value="{{ old('welfare_normal_doctor_percentage', $procedure->welfare_normal_doctor_percentage ?? '0') }}" required min="0" max="100" step="0.01">
                </div>
                <div>
                    <label for="welfare_emergency_doctor_percentage" class="hms-label">Welfare Emergency (%):</label>
                    <input type="number" id="welfare_emergency_doctor_percentage" name="welfare_emergency_doctor_percentage" class="hms-input" value="{{ old('welfare_emergency_doctor_percentage', $procedure->welfare_emergency_doctor_percentage ?? '0') }}" required min="0" max="100" step="0.01">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="hms-btn hms-btn-primary">
                    @isset($procedure)
                        Update Procedure
                    @else
                        Add Procedure
                    @endisset
                </button>
            </div>
        </form>
    </div>
@endsection