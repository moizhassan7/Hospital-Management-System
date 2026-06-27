@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Patient Registration</h2></div>
        <a href="{{ route('patients.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
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
        <form id="patient_registration_form" action="{{ route('patients.store') }}" method="POST">
            @csrf

            <h3 class="hms-section-title">Patient Information</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="mr_number" class="hms-label">MR Number:</label>
                    <input type="text" id="mr_number" name="mr_number" class="hms-input hms-input-readonly @error('mr_number') border-red-500 @enderror" value="{{ old('mr_number', $formattedMrNumber ?? '') }}" readonly>
                    @error('mr_number')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="registration_date" class="hms-label">Registration Date:</label>
                    <input type="date" id="registration_date" name="registration_date" class="hms-input @error('registration_date') border-red-500 @enderror" value="{{ old('registration_date', date('Y-m-d')) }}" required>
                    @error('registration_date')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="name" class="hms-label">Patient Name:</label>
                    <input type="text" id="name" name="name" class="hms-input @error('name') border-red-500 @enderror" placeholder="e.g., John Doe" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="marital_status" class="hms-label">Marital Status:</label>
                    <select id="marital_status" name="marital_status" class="hms-input @error('marital_status') border-red-500 @enderror" required>
                        <option value="">Select Status</option>
                        <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Divorced" {{ old('marital_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="Widowed" {{ old('marital_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                    @error('marital_status')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_of_birth" class="hms-label">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="hms-input @error('date_of_birth') border-red-500 @enderror" value="{{ old('date_of_birth') }}" required>
                    @error('date_of_birth')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="is_welfare" class="hms-label">Patient Type:</label>
                    <select id="is_welfare" name="is_welfare" class="hms-input @error('is_welfare') border-red-500 @enderror" required>
                        <option value="0" {{ old('is_welfare') == '0' ? 'selected' : '' }}>Normal</option>
                        <option value="1" {{ old('is_welfare') == '1' ? 'selected' : '' }}>Welfare</option>
                    </select>
                    @error('is_welfare')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h3 class="hms-section-title mt-8">Guardian/Relative Information</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="relation_type" class="hms-label">Relation Type:</label>
                    <select id="relation_type" name="relation_type" class="hms-input @error('relation_type') border-red-500 @enderror">
                        <option value="">Select Relation</option>
                        <option value="Father" {{ old('relation_type') == 'Father' ? 'selected' : '' }}>Father</option>
                        <option value="Mother" {{ old('relation_type') == 'Mother' ? 'selected' : '' }}>Mother</option>
                        <option value="Spouse" {{ old('relation_type') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                        <option value="Son" {{ old('relation_type') == 'Son' ? 'selected' : '' }}>Son</option>
                        <option value="Daughter" {{ old('relation_type') == 'Daughter' ? 'selected' : '' }}>Daughter</option>
                        <option value="Other" {{ old('relation_type') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('relation_type')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="guardian_name" class="hms-label">Name (Guardian/Relative):</label>
                    <input type="text" id="guardian_name" name="guardian_name" class="hms-input @error('guardian_name') border-red-500 @enderror" placeholder="e.g., Jane's Father" value="{{ old('guardian_name') }}">
                    @error('guardian_name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="guardian_cnic" class="hms-label">Guardian CNIC:</label>
                    <input type="text" id="guardian_cnic" name="guardian_cnic" class="hms-input @error('guardian_cnic') border-red-500 @enderror" placeholder="e.g., 12345-6789012-3" value="{{ old('guardian_cnic') }}">
                    @error('guardian_cnic')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h3 class="hms-section-title mt-8">Patient Contact & Demographics</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="age" class="hms-label">Age:</label>
                    <input type="number" id="age" name="age" class="hms-input hms-input-readonly" placeholder="e.g., 30" min="0" value="{{ old('age') }}" readonly>
                </div>
                <div>
                    <label for="weight" class="hms-label">Weight (kg):</label>
                    <input type="number" id="weight" name="weight" class="hms-input @error('weight') border-red-500 @enderror" placeholder="e.g., 70" min="0" step="0.1" value="{{ old('weight') }}">
                    @error('weight')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="gender" class="hms-label">Gender:</label>
                    <select id="gender" name="gender" class="hms-input @error('gender') border-red-500 @enderror" required>
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="mobile_number" class="hms-label">Mobile Number:</label>
                    <input type="text" id="mobile_number" name="mobile_number" class="hms-input @error('mobile_number') border-red-500 @enderror" placeholder="e.g., 03xx-xxxxxxx" value="{{ old('mobile_number') }}">
                    @error('mobile_number')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="hms-label">Email:</label>
                    <input type="email" id="email" name="email" class="hms-input @error('email') border-red-500 @enderror" placeholder="e.g., patient@example.com" value="{{ old('email') }}">
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="cnic" class="hms-label">CNIC:</label>
                    <input type="text" id="cnic" name="cnic" class="hms-input @error('cnic') border-red-500 @enderror" placeholder="e.g., 12345-6789012-3" value="{{ old('cnic') }}">
                    @error('cnic')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="address" class="hms-label">Address:</label>
                    <textarea id="address" name="address" rows="3" class="hms-input @error('address') border-red-500 @enderror" placeholder="Patient's full address">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="hms-btn hms-btn-primary">
                    Register Patient
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('date_of_birth').addEventListener('change', function() {
            var dob = new Date(this.value);
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            document.getElementById('age').value = age;
        });
    </script>
@endsection