@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">
            @isset($doctor)
                Edit Doctor: {{ $doctor->name }}
            @else
                Add New Doctor
            @endisset
        </h2>
        <a href="{{ route('doctors.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Doctors Management
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
        <form action="{{ isset($doctor) ? route('doctors.update', $doctor->id) : route('doctors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @isset($doctor)
                @method('PUT')
            @endisset

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="doctor_picture" class="block text-gray-700 text-sm font-bold mb-2">Doctor Picture:</label>
                    <input type="file" id="doctor_picture" name="doctor_picture" accept="image/*"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="registration_date" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
                    <input type="date" id="registration_date" name="registration_date"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        value="{{ old('registration_date', $doctor->registration_date ?? date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label for="doctor_type" class="block text-gray-700 text-sm font-bold mb-2">Doctor Type:</label>
                    <select id="doctor_type" name="doctor_type"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                        <option value="">Select Doctor Type</option>
                        @foreach ($doctorTypes as $type)
                            <option value="{{ $type->name }}" {{ old('doctor_type', $doctor->type ?? '') == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="doctor_code" class="block text-gray-700 text-sm font-bold mb-2">Doctor Code:</label>
                    <input type="text" id="doctor_code" name="doctor_code"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., DR001" value="{{ old('doctor_code', $doctor->code ?? '') }}" required>
                </div>
                <div>
                    <label for="department_id" class="block text-gray-700 text-sm font-bold mb-2">Department:</label>
                    <select id="department_id" name="department_id"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $doctor->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="speciality_id" class="block text-gray-700 text-sm font-bold mb-2">Speciality:</label>
                    <select id="speciality_id" name="speciality_id"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Speciality</option>
                        @foreach ($specialities as $spec)
                            <option value="{{ $spec->id }}" {{ old('speciality_id', $doctor->speciality_id ?? '') == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="room_location" class="block text-gray-700 text-sm font-bold mb-2">Room Location:</label>
                    <input type="text" id="room_location" name="room_location"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Room 203, OPD Block" value="{{ old('room_location', $doctor->room_location ?? '') }}">
                </div>
                <div>
                    <label for="employee_group" class="block text-gray-700 text-sm font-bold mb-2">Employee Group:</label>
                    <select id="employee_group" name="employee_group"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Group</option>
                        <option value="Medical Staff" {{ old('employee_group', $doctor->employee_group ?? '') == 'Medical Staff' ? 'selected' : '' }}>Medical Staff</option>
                        <option value="Surgical Staff" {{ old('employee_group', $doctor->employee_group ?? '') == 'Surgical Staff' ? 'selected' : '' }}>Surgical Staff</option>
                        <option value="Support Staff" {{ old('employee_group', $doctor->employee_group ?? '') == 'Support Staff' ? 'selected' : '' }}>Support Staff</option>
                    </select>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Working Days</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
                @php
                    $workingDays = old('working_days', $doctor->working_days ?? []);
                @endphp
                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="working_days[]" value="{{ $day }}"
                            class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                            {{ in_array($day, $workingDays) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700 text-sm font-bold">{{ $day }}</span>
                    </label>
                @endforeach
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="doctor_name" class="block text-gray-700 text-sm font-bold mb-2">Doctor Name:</label>
                    <input type="text" id="doctor_name" name="doctor_name"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Dr. Full Name" value="{{ old('doctor_name', $doctor->name ?? '') }}" required>
                </div>
                <div>
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                    <textarea id="address" name="address" rows="1"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Doctor's address">{{ old('address', $doctor->address ?? '') }}</textarea>
                </div>
                <div>
                    <label for="mobile_number" class="block text-gray-700 text-sm font-bold mb-2">Mobile Number:</label>
                    <input type="tel" id="mobile_number" name="mobile_number"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., +923xx-xxxxxxx" value="{{ old('mobile_number', $doctor->mobile_number ?? '') }}">
                </div>
                <div>
                    <label for="office_phone" class="block text-gray-700 text-sm font-bold mb-2">Office Phone:</label>
                    <input type="tel" id="office_phone" name="office_phone"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 048-xxxxxxx" value="{{ old('office_phone', $doctor->office_phone ?? '') }}">
                </div>
                <div>
                    <label for="reception_phone" class="block text-gray-700 text-sm font-bold mb-2">Reception Phone:</label>
                    <input type="tel" id="reception_phone" name="reception_phone"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 048-xxxxxxx" value="{{ old('reception_phone', $doctor->reception_phone ?? '') }}">
                </div>
                <div>
                    <label for="accounts_of" class="block text-gray-700 text-sm font-bold mb-2">Accounts Of:</label>
                    <input type="text" id="accounts_of" name="accounts_of"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Bank Account Number" value="{{ old('accounts_of', $doctor->accounts_of ?? '') }}">
                </div>
                <div>
                    <label for="fee" class="block text-gray-700 text-sm font-bold mb-2">Doctor's Default Fee ($):</label>
                    <input type="number" id="fee" name="fee"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 500" value="{{ old('fee', $doctor->fee ?? '0.00') }}" required step="0.01">
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Fee & Percentage Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="general_normal_fee" class="block text-gray-700 text-sm font-bold mb-2">General Normal Fee ($):</label>
                    <input type="number" id="general_normal_fee" name="general_normal_fee"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 400" value="{{ old('general_normal_fee', $doctor->general_normal_fee ?? '0.00') }}" required step="0.01">
                </div>
                <div>
                    <label for="general_emergency_fee" class="block text-gray-700 text-sm font-bold mb-2">General Emergency Fee ($):</label>
                    <input type="number" id="general_emergency_fee" name="general_emergency_fee"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 600" value="{{ old('general_emergency_fee', $doctor->general_emergency_fee ?? '0.00') }}" required step="0.01">
                </div>
                <div>
                    <label for="welfare_normal_fee" class="block text-gray-700 text-sm font-bold mb-2">Welfare Normal Fee ($):</label>
                    <input type="number" id="welfare_normal_fee" name="welfare_normal_fee"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 200" value="{{ old('welfare_normal_fee', $doctor->welfare_normal_fee ?? '0.00') }}" required step="0.01">
                </div>
                <div>
                    <label for="welfare_emergency_fee" class="block text-gray-700 text-sm font-bold mb-2">Welfare Emergency Fee ($):</label>
                    <input type="number" id="welfare_emergency_fee" name="welfare_emergency_fee"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 300" value="{{ old('welfare_emergency_fee', $doctor->welfare_emergency_fee ?? '0.00') }}" required step="0.01">
                </div>

                <div>
                    <label for="general_normal_percentage" class="block text-gray-700 text-sm font-bold mb-2">General Normal Percentage (%):</label>
                    <input type="number" id="general_normal_percentage" name="general_normal_percentage"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 80" value="{{ old('general_normal_percentage', $doctor->general_normal_percentage ?? '0.00') }}" required min="0" max="100" step="0.01">
                </div>
                <div>
                    <label for="general_emergency_percentage" class="block text-gray-700 text-sm font-bold mb-2">General Emergency Percentage (%):</label>
                    <input type="number" id="general_emergency_percentage" name="general_emergency_percentage"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 75" value="{{ old('general_emergency_percentage', $doctor->general_emergency_percentage ?? '0.00') }}" required min="0" max="100" step="0.01">
                </div>
                <div>
                    <label for="welfare_normal_percentage" class="block text-gray-700 text-sm font-bold mb-2">Welfare Normal Percentage (%):</label>
                    <input type="number" id="welfare_normal_percentage" name="welfare_normal_percentage"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 50" value="{{ old('welfare_normal_percentage', $doctor->welfare_normal_percentage ?? '0.00') }}" required min="0" max="100" step="0.01">
                </div>
                <div>
                    <label for="welfare_emergency_percentage" class="block text-gray-700 text-sm font-bold mb-2">Welfare Emergency Percentage (%):</label>
                    <input type="number" id="welfare_emergency_percentage" name="welfare_emergency_percentage"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 40" value="{{ old('welfare_emergency_percentage', $doctor->welfare_emergency_percentage ?? '0.00') }}" required min="0" max="100" step="0.01">
                </div>
            </div>

            <div class="mb-6 mt-8">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                        {{ old('is_active', $doctor->is_active ?? true) ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-700 text-sm font-bold">Doctor is Active</span>
                </label>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    @isset($doctor)
                        Update Doctor
                    @else
                        Add Doctor
                    @endisset
                </button>
            </div>
        </form>
    </div>
@endsection