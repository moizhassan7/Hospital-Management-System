@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Quick Patient Registration</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
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
        <form id="patient_registration_form" action="{{ route('patients.store') }}" method="POST">
            @csrf

            <!-- Primary Information (Required for Fast Registration) -->
            <h3 class="text-2xl font-semibold text-blue-800 mb-4 border-b pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Primary Information (Fast Register)
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Row 1: MR, Date, Name, Mobile -->
                <div>
                    <label for="mr_number" class="block text-gray-700 text-sm font-bold mb-2">MR Number:</label>
                    <input type="text" id="mr_number" name="mr_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ old('mr_number', $formattedMrNumber ?? '') }}" readonly>
                </div>
                <div>
                    <label for="registration_date" class="block text-gray-700 text-sm font-bold mb-2">Registration Date:</label>
                    <input type="date" id="registration_date" name="registration_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('registration_date') border-red-500 @enderror" value="{{ old('registration_date', date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2 text-blue-600">Patient Name *</label>
                    <input type="text" id="name" name="name" class="shadow appearance-none border-2 border-blue-200 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Full Name" value="{{ old('name') }}" required autofocus>
                </div>
                <div>
                    <label for="mobile_number" class="block text-gray-700 text-sm font-bold mb-2 text-blue-600">Mobile Number</label>
                    <input type="text" id="mobile_number" name="mobile_number" class="shadow appearance-none border-2 border-blue-200 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 03xx-xxxxxxx" value="{{ old('mobile_number') }}">
                </div>

                <!-- Row 2: DOB, Age, Gender, Type -->
                <div>
                    <label for="date_of_birth" class="block text-gray-700 text-sm font-bold mb-2 text-blue-600">Date of Birth *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="shadow appearance-none border-2 border-blue-200 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('date_of_birth') }}" required>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age (Auto-calculated):</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Age" min="0" value="{{ old('age') }}" readonly>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2 text-blue-600">Gender *</label>
                    <select id="gender" name="gender" class="shadow appearance-none border-2 border-blue-200 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label for="is_welfare" class="block text-gray-700 text-sm font-bold mb-2 text-blue-600">Patient Type *</label>
                    <select id="is_welfare" name="is_welfare" class="shadow appearance-none border-2 border-blue-200 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="0" {{ old('is_welfare') == '0' ? 'selected' : '' }}>Normal</option>
                        <option value="1" {{ old('is_welfare') == '1' ? 'selected' : '' }}>Welfare</option>
                    </select>
                </div>

                <!-- Row 3: Marital Status -->
                <div>
                    <label for="marital_status" class="block text-gray-700 text-sm font-bold mb-2 text-blue-600">Marital Status *</label>
                    <select id="marital_status" name="marital_status" class="shadow appearance-none border-2 border-blue-200 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Divorced" {{ old('marital_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="Widowed" {{ old('marital_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button (Top position for fast checkout) -->
            <div class="flex justify-end border-b pb-8 mb-8">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition-colors duration-200 text-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Register Patient Now
                </button>
            </div>

            <!-- Additional Information (Optional) -->
            <div class="mt-8">
                <button type="button" onclick="toggleOptionalInfo()" class="flex items-center text-gray-600 hover:text-blue-600 focus:outline-none">
                    <h3 class="text-xl font-semibold mb-0 mr-2">Additional / Guardian Information (Optional)</h3>
                    <svg id="optional-icon" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <p class="text-sm text-gray-500 mb-4">Click to expand optional fields if needed.</p>

                <div id="optional-fields" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <div>
                        <label for="weight" class="block text-gray-700 text-sm font-bold mb-2">Weight (kg):</label>
                        <input type="number" id="weight" name="weight" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 70" min="0" step="0.1" value="{{ old('weight') }}">
                    </div>
                    <div>
                        <label for="cnic" class="block text-gray-700 text-sm font-bold mb-2">Patient CNIC:</label>
                        <input type="text" id="cnic" name="cnic" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 12345-6789012-3" value="{{ old('cnic') }}">
                    </div>
                    <div>
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                        <input type="email" id="email" name="email" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="patient@example.com" value="{{ old('email') }}">
                    </div>
                    
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                        <textarea id="address" name="address" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Patient's full address">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-span-1 md:col-span-2 lg:col-span-3 border-t pt-4 mt-2">
                        <h4 class="text-md font-bold text-gray-700 mb-4">Guardian / Relative Details</h4>
                    </div>

                    <div>
                        <label for="relation_type" class="block text-gray-700 text-sm font-bold mb-2">Relation Type:</label>
                        <select id="relation_type" name="relation_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Relation</option>
                            <option value="Father" {{ old('relation_type') == 'Father' ? 'selected' : '' }}>Father</option>
                            <option value="Mother" {{ old('relation_type') == 'Mother' ? 'selected' : '' }}>Mother</option>
                            <option value="Spouse" {{ old('relation_type') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                            <option value="Son" {{ old('relation_type') == 'Son' ? 'selected' : '' }}>Son</option>
                            <option value="Daughter" {{ old('relation_type') == 'Daughter' ? 'selected' : '' }}>Daughter</option>
                            <option value="Other" {{ old('relation_type') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="guardian_name" class="block text-gray-700 text-sm font-bold mb-2">Guardian Name:</label>
                        <input type="text" id="guardian_name" name="guardian_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Guardian Name" value="{{ old('guardian_name') }}">
                    </div>
                    <div>
                        <label for="guardian_cnic" class="block text-gray-700 text-sm font-bold mb-2">Guardian CNIC:</label>
                        <input type="text" id="guardian_cnic" name="guardian_cnic" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 12345-6789012-3" value="{{ old('guardian_cnic') }}">
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        // Auto-calculate Age based on Date of Birth
        document.getElementById('date_of_birth').addEventListener('change', function() {
            var dob = new Date(this.value);
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            document.getElementById('age').value = age > 0 ? age : 0;
        });

        // Toggle Optional Info block
        function toggleOptionalInfo() {
            const optionalFields = document.getElementById('optional-fields');
            const icon = document.getElementById('optional-icon');
            
            if (optionalFields.classList.contains('hidden')) {
                optionalFields.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                optionalFields.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
    </script>
@endsection