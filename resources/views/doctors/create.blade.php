@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Add New Doctor</h2>
        <a href="{{ route('doctors.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Doctors Management
        </a>
    </div>

    <!-- Add Doctor Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form action="#" method="POST" enctype="multipart/form-data"> {{-- enctype is important for file uploads --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- Basic Doctor Information -->
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
                        value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="doctor_type" class="block text-gray-700 text-sm font-bold mb-2">Doctor Type:</label>
                    <select id="doctor_type" name="doctor_type"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                        <option value="">Select Doctor Type</option>
                        {{-- Static Doctor Types (example) --}}
                        <option value="Consultant">Consultant</option>
                        <option value="Resident">Resident</option>
                        <option value="Intern">Intern</option>
                        <option value="Fellow">Fellow</option>
                    </select>
                </div>
                <div>
                    <label for="doctor_code" class="block text-gray-700 text-sm font-bold mb-2">Doctor Code:</label>
                    <input type="text" id="doctor_code" name="doctor_code"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., DR001" required>
                </div>
                <div>
                    <label for="department" class="block text-gray-700 text-sm font-bold mb-2">Department:</label>
                    <select id="department" name="department"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                        <option value="">Select Department</option>
                        {{-- Static Departments (example) --}}
                        <option value="Cardiology">Cardiology</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Neurology">Neurology</option>
                        <option value="Orthopedics">Orthopedics</option>
                        <option value="General Surgery">General Surgery</option>
                    </select>
                </div>
                <div>
                    <label for="speciality" class="block text-gray-700 text-sm font-bold mb-2">Speciality:</label>
                    <select id="speciality" name="speciality"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Speciality</option>
                        {{-- Static Specialities (example) --}}
                        <option value="Cardiac Surgery">Cardiac Surgery</option>
                        <option value="Pediatric Oncology">Pediatric Oncology</option>
                        <option value="Neurophysiology">Neurophysiology</option>
                        <option value="Joint Replacement">Joint Replacement</option>
                    </select>
                </div>
                <div>
                    <label for="room_location" class="block text-gray-700 text-sm font-bold mb-2">Room Location:</label>
                    <input type="text" id="room_location" name="room_location"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Room 203, OPD Block">
                </div>
                <div>
                    <label for="employee_group" class="block text-gray-700 text-sm font-bold mb-2">Employee Group:</label>
                    <select id="employee_group" name="employee_group"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Group</option>
                        <option value="Medical Staff">Medical Staff</option>
                        <option value="Surgical Staff">Surgical Staff</option>
                        <option value="Support Staff">Support Staff</option>
                    </select>
                </div>
            </div>

            <!-- Working Days -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Working Days</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="working_days[]" value="Monday"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm font-bold">Monday</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="working_days[]" value="Tuesday"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm font-bold">Tuesday</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="working_days[]" value="Wednesday"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm font-bold">Wednesday</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="working_days[]" value="Thursday"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm font-bold">Thursday</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="working_days[]" value="Friday"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm font-bold">Friday</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="working_days[]" value="Saturday"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm font-bold">Saturday</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="working_days[]" value="Sunday"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm font-bold">Sunday</span>
                </label>
            </div>

            <!-- Contact Information -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="doctor_name" class="block text-gray-700 text-sm font-bold mb-2">Doctor Name:</label>
                    <input type="text" id="doctor_name" name="doctor_name"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Dr. Full Name" required>
                </div>
                <div>
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                    <textarea id="address" name="address" rows="1"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Doctor's address"></textarea>
                </div>
                <div>
                    <label for="mobile_number" class="block text-gray-700 text-sm font-bold mb-2">Mobile Number:</label>
                    <input type="tel" id="mobile_number" name="mobile_number"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., +923xx-xxxxxxx">
                </div>
                <div>
                    <label for="office_phone" class="block text-gray-700 text-sm font-bold mb-2">Office Phone:</label>
                    <input type="tel" id="office_phone" name="office_phone"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 048-xxxxxxx">
                </div>
                <div>
                    <label for="reception_phone" class="block text-gray-700 text-sm font-bold mb-2">Reception
                        Phone:</label>
                    <input type="tel" id="reception_phone" name="reception_phone"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 048-xxxxxxx">
                </div>
                <div>
                    <label for="accounts_of" class="block text-gray-700 text-sm font-bold mb-2">Accounts Of:</label>
                    <input type="text" id="accounts_of" name="accounts_of"
                        class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Bank Account Number">
                </div>
            </div>

            <!-- Status -->
            <div class="mb-6 mt-8">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" id="is_active"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" checked>
                    <span class="ml-2 text-gray-700 text-sm font-bold">Doctor is Active</span>
                </label>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add Doctor
                </button>
            </div>
        </form>
    </div>
@endsection
