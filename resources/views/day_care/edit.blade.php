@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Edit Day Care Procedure</h2>
        <a href="{{ route('daycare.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Procedures
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

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

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="daycare_form" action="{{ route('daycare.update', $dayCareProcedure->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_number" class="block text-gray-700 text-sm font-bold mb-2">MR Number:</label>
                    <input type="text" id="mr_number" name="mr_number" value="{{ $dayCareProcedure->mr_number }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search or press Enter" required>
                </div>
                <div>
                    <label for="procedure_id" class="block text-gray-700 text-sm font-bold mb-2">Procedure ID:</label>
                    <input type="text" id="procedure_id" name="procedure_id" value="{{ $dayCareProcedure->procedure_id }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-generated on save" readonly>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" value="{{ $dayCareProcedure->patient_name }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" value="{{ $dayCareProcedure->age }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
                    <input type="text" id="gender" name="gender" value="{{ $dayCareProcedure->gender }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Procedure Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="procedure_type" class="block text-gray-700 text-sm font-bold mb-2">Procedure Type:</label>
                    <select id="procedure_type" name="procedure_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Type</option>
                        <option value="MAJOR" @if($dayCareProcedure->procedure_type == 'MAJOR') selected @endif>Major</option>
                        <option value="MINOR" @if($dayCareProcedure->procedure_type == 'MINOR') selected @endif>Minor</option>
                    </select>
                </div>
                <div>
                    <label for="fee" class="block text-gray-700 text-sm font-bold mb-2">Fee ($):</label>
                    <input type="number" id="fee" name="fee" value="{{ $dayCareProcedure->fee }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 500" min="0" step="0.01" required>
                </div>
                <div>
                    <label for="duration_in_hours" class="block text-gray-700 text-sm font-bold mb-2">Duration (Hours):</label>
                    <input type="number" id="duration_in_hours" name="duration_in_hours" value="{{ $dayCareProcedure->duration_in_hours }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 1.5" min="0" step="0.5">
                </div>
                <div class="flex items-center mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_operation" id="is_operation" value="1" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" @if($dayCareProcedure->is_operation) checked @endif>
                        <span class="ml-2 text-gray-700 text-sm font-bold">Is Operation?</span>
                    </label>
                </div>
                <div class="flex items-center mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_anesthesia" id="is_anesthesia" value="1" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" @if($dayCareProcedure->anesthesia_type) checked @endif>
                        <span class="ml-2 text-gray-700 text-sm font-bold">Requires Anesthesia?</span>
                    </label>
                </div>
                <div id="anesthesia_section" class="hidden">
                    <label for="anesthesia_type" class="block text-gray-700 text-sm font-bold mb-2">Anesthesia Type:</label>
                    <select id="anesthesia_type" name="anesthesia_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        <option value="Type1" @if($dayCareProcedure->anesthesia_type == 'Type1') selected @endif>Type 1</option>
                        <option value="Type2" @if($dayCareProcedure->anesthesia_type == 'Type2') selected @endif>Type 2</option>
                        <option value="Type3" @if($dayCareProcedure->anesthesia_type == 'Type3') selected @endif>Type 3</option>
                        <option value="Type4" @if($dayCareProcedure->anesthesia_type == 'Type4') selected @endif>Type 4</option>
                    </select>
                </div>
                <div>
                    <label for="department_id" class="block text-gray-700 text-sm font-bold mb-2">Department:</label>
                    <select id="department_id" name="department_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @if($dayCareProcedure->department_name == $department->name) selected @endif>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="consultant_input" class="block text-gray-700 text-sm font-bold mb-2">Consultant (Optional):</label>
                    <input type="text" id="consultant_input" value="{{ $dayCareProcedure->department_consultant_name }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search or press Enter">
                    <input type="hidden" name="consultant_id" id="consultant_id">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Update Procedure
                </button>
            </div>
        </form>
    </div>
    
  @include('day_care_procedure.modals')
    @include('day_care_procedure.scripts')
@endsection

