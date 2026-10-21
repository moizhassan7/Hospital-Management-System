@extends('layouts.app')

@section('title', 'Admission form')

@section('content')
    <x-ui.page-header title="Admission form" subtitle="Record a patient's admission details and reason for admission.">
        <x-slot:meta>
            <x-ui.badge tone="warning" dot>Preview</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="warning" title="This form is not connected yet" class="mb-5 max-w-[1120px]">
        Submitting it will not save an admission. Use <a href="{{ route('patients.indoor_register') }}" class="link">Indoor patient registration</a> to admit patients.
    </x-ui.alert>

    <form action="#" method="POST" class="max-w-[1120px]"> {{-- Action will be updated later for actual submission --}}
        @csrf

        <x-ui.panel flush>
            <x-ui.form-section title="Patient information" icon="user">
                <div class="form-grid">
                    <x-ui.field label="MR number" for="mr_no" required class="md:col-span-4 xl:col-span-3">
                        <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001" required>
                    </x-ui.field>
                    <x-ui.field label="Patient name" for="patient_name" required class="md:col-span-8 xl:col-span-6">
                        <input type="text" id="patient_name" name="patient_name" class="input" placeholder="e.g. Jane Doe" required>
                    </x-ui.field>
                    <x-ui.field label="Age" for="age" required class="md:col-span-3 xl:col-span-3">
                        <input type="number" id="age" name="age" class="input input-num" placeholder="30" min="0" required>
                    </x-ui.field>
                    <x-ui.field label="Gender" for="gender" required class="md:col-span-4 xl:col-span-3">
                        <select id="gender" name="gender" class="select" required>
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Mobile number" for="mobile_number" class="md:col-span-5 xl:col-span-3">
                        <input type="tel" id="mobile_number" name="mobile_number" class="input" placeholder="e.g. +923xx-xxxxxxx">
                    </x-ui.field>
                    <x-ui.field label="Address" for="address" class="md:col-span-12 xl:col-span-6">
                        <textarea id="address" name="address" rows="1" class="textarea min-h-0" placeholder="Patient's full address"></textarea>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Admission details" icon="bed">
                <div class="form-grid">
                    <x-ui.field label="Admission date" for="admission_date" required class="md:col-span-4 xl:col-span-3">
                        <input type="date" id="admission_date" name="admission_date" class="input" value="{{ date('Y-m-d') }}" required>
                    </x-ui.field>
                    <x-ui.field label="Admission time" for="admission_time" required class="md:col-span-4 xl:col-span-2">
                        <input type="time" id="admission_time" name="admission_time" class="input" value="{{ date('H:i') }}" required>
                    </x-ui.field>
                    <x-ui.field label="Ward" for="ward_number" required class="md:col-span-4 xl:col-span-3">
                        <select id="ward_number" name="ward_number" class="select" required>
                            <option value="">Select ward</option>
                            {{-- Static Ward Data (example) --}}
                            <option value="Ward A">Ward A</option>
                            <option value="Ward B">Ward B</option>
                            <option value="ICU">ICU</option>
                            <option value="Private Room 1">Private Room 1</option>
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Bed no." for="bed_no" required class="md:col-span-4 xl:col-span-2">
                        <input type="text" id="bed_no" name="bed_no" class="input" placeholder="e.g. A-101" required>
                    </x-ui.field>
                    <x-ui.field label="Referring doctor" for="referring_doctor" class="md:col-span-8 xl:col-span-5">
                        <select id="referring_doctor" name="referring_doctor" class="select">
                            <option value="">Select doctor</option>
                            {{-- Static Doctors for dropdown --}}
                            <option value="DOC001">Dr. Alice Smith</option>
                            <option value="DOC002">Dr. Bob Johnson</option>
                            <option value="DOC003">Dr. Carol White</option>
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Estimated discharge date" for="estimated_discharge_date" class="md:col-span-4 xl:col-span-3">
                        <input type="date" id="estimated_discharge_date" name="estimated_discharge_date" class="input">
                    </x-ui.field>
                    <x-ui.field label="Reason for admission" for="admission_reason" required class="md:col-span-12">
                        <textarea id="admission_reason" name="admission_reason" rows="2" class="textarea min-h-0" placeholder="Reason for admission" required></textarea>
                    </x-ui.field>
                    <x-ui.field label="Initial diagnosis" for="initial_diagnosis" class="md:col-span-12">
                        <textarea id="initial_diagnosis" name="initial_diagnosis" rows="2" class="textarea min-h-0" placeholder="Initial medical diagnosis"></textarea>
                    </x-ui.field>
                    <x-ui.field label="Admission notes" for="admission_notes" class="md:col-span-12">
                        <textarea id="admission_notes" name="admission_notes" rows="3" class="textarea" placeholder="Any additional notes for admission"></textarea>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-slot:footer class="is-sticky">
                <a href="{{ route('patients.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><x-ui.icon name="check" />Admit patient</button>
            </x-slot:footer>
        </x-ui.panel>
    </form>
@endsection
