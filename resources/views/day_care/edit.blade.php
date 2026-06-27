@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Edit Day Care Procedure</h2></div>
        <a href="{{ route('daycare.index') }}" class="hms-back-btn">
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
        <form id="daycare_form" action="{{ route('daycare.update', $dayCareProcedure->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <h3 class="hms-section-title">Patient Details</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="mr_number" class="hms-label">MR Number:</label>
                    <input type="text" id="mr_number" name="mr_number" value="{{ $dayCareProcedure->mr_number }}" class="hms-input focus:ring-2 focus:ring-blue-500" placeholder="Search or press Enter" required>
                </div>
                <div>
                    <label for="procedure_id" class="hms-label">Procedure ID:</label>
                    <input type="text" id="procedure_id" name="procedure_id" value="{{ $dayCareProcedure->procedure_id }}" class="hms-input hms-input-readonly" placeholder="Auto-generated on save" readonly>
                </div>
                <div>
                    <label for="patient_name" class="hms-label">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" value="{{ $dayCareProcedure->patient_name }}" class="hms-input hms-input-readonly" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="age" class="hms-label">Age:</label>
                    <input type="number" id="age" name="age" value="{{ $dayCareProcedure->age }}" class="hms-input hms-input-readonly" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="gender" class="hms-label">Gender:</label>
                    <input type="text" id="gender" name="gender" value="{{ $dayCareProcedure->gender }}" class="hms-input hms-input-readonly" placeholder="Auto-populated" readonly>
                </div>
            </div>

            <h3 class="hms-section-title mt-8">Procedure Details</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="procedure_type" class="hms-label">Procedure Type:</label>
                    <select id="procedure_type" name="procedure_type" class="hms-input focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Type</option>
                        <option value="MAJOR" @if($dayCareProcedure->procedure_type == 'MAJOR') selected @endif>Major</option>
                        <option value="MINOR" @if($dayCareProcedure->procedure_type == 'MINOR') selected @endif>Minor</option>
                    </select>
                </div>
                <div>
                    <label for="fee" class="hms-label">Fee ($):</label>
                    <input type="number" id="fee" name="fee" value="{{ $dayCareProcedure->fee }}" class="hms-input focus:ring-2 focus:ring-blue-500" placeholder="e.g., 500" min="0" step="0.01" required>
                </div>
                <div>
                    <label for="duration_in_hours" class="hms-label">Duration (Hours):</label>
                    <input type="number" id="duration_in_hours" name="duration_in_hours" value="{{ $dayCareProcedure->duration_in_hours }}" class="hms-input focus:ring-2 focus:ring-blue-500" placeholder="e.g., 1.5" min="0" step="0.5">
                </div>
                <div class="flex items-center mt-6">
                    <label class="hms-checkbox-row">
                        <input type="checkbox" name="is_operation" id="is_operation" value="1" class="hms-checkbox" @if($dayCareProcedure->is_operation) checked @endif>
                        <span class="ml-2 text-gray-700 text-sm font-bold">Is Operation?</span>
                    </label>
                </div>
                <div class="flex items-center mt-6">
                    <label class="hms-checkbox-row">
                        <input type="checkbox" name="is_anesthesia" id="is_anesthesia" value="1" class="hms-checkbox" @if($dayCareProcedure->anesthesia_type) checked @endif>
                        <span class="ml-2 text-gray-700 text-sm font-bold">Requires Anesthesia?</span>
                    </label>
                </div>
                <div id="anesthesia_section" class="hidden">
                    <label for="anesthesia_type" class="hms-label">Anesthesia Type:</label>
                    <select id="anesthesia_type" name="anesthesia_type" class="hms-input focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        <option value="Type1" @if($dayCareProcedure->anesthesia_type == 'Type1') selected @endif>Type 1</option>
                        <option value="Type2" @if($dayCareProcedure->anesthesia_type == 'Type2') selected @endif>Type 2</option>
                        <option value="Type3" @if($dayCareProcedure->anesthesia_type == 'Type3') selected @endif>Type 3</option>
                        <option value="Type4" @if($dayCareProcedure->anesthesia_type == 'Type4') selected @endif>Type 4</option>
                    </select>
                </div>
                <div>
                    <label for="department_id" class="hms-label">Department:</label>
                    <select id="department_id" name="department_id" class="hms-input focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @if($dayCareProcedure->department_name == $department->name) selected @endif>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="consultant_input" class="hms-label">Consultant (Optional):</label>
                    <input type="text" id="consultant_input" value="{{ $dayCareProcedure->department_consultant_name }}" class="hms-input focus:ring-2 focus:ring-blue-500" placeholder="Search or press Enter">
                    <input type="hidden" name="consultant_id" id="consultant_id">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="hms-btn hms-btn-primary">
                    Update Procedure
                </button>
            </div>
        </form>
    </div>
    
  @include('day_care_procedure.modals')
    @include('day_care_procedure.scripts')
@endsection

