@extends('layouts.app')

@section('title', isset($patient) && $patient->exists ? 'Edit patient' : 'Register patient')

@section('content')
    @php
        $editing = isset($patient) && $patient->exists;
        $optionalFields = ['weight', 'cnic', 'email', 'address', 'relation_type', 'guardian_name', 'guardian_cnic'];
        $optionalOpen = $errors->hasAny($optionalFields)
            || collect($optionalFields)->contains(fn ($field) => filled(old($field)))
            || ($editing && collect($optionalFields)->contains(fn ($field) => filled($patient->{$field})));
        $gender = old('gender', $patient->gender ?? '');
        $welfare = (string) old('is_welfare', $editing ? ($patient->is_welfare ? '1' : '0') : '0');
        $marital = old('marital_status', $patient->marital_status ?? '');
        $relation = old('relation_type', $patient->relation_type ?? '');
    @endphp

    @if ($editing)
        <x-ui.page-header title="Edit patient" :subtitle="$patient->name . ' · MR ' . $patient->mr_number">
            <x-slot:actions>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-ghost">
                    <x-ui.icon name="arrow-left" />Patient profile
                </a>
            </x-slot:actions>
        </x-ui.page-header>
    @else
        <x-ui.page-header title="Register patient" subtitle="Create a medical record. Only the patient details are required; guardian details can be added now or later.">
            <x-slot:actions>
                <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                    <x-ui.icon name="arrow-left" />Patients
                </a>
                <a href="{{ route('patients.all') }}" class="btn btn-secondary">
                    <x-ui.icon name="list" />All patients
                </a>
            </x-slot:actions>
        </x-ui.page-header>
    @endif

    <form id="patient_registration_form" action="{{ $editing ? route('patients.update', $patient) : route('patients.store') }}" method="POST" class="max-w-[1120px]">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <x-ui.panel flush>
            <x-ui.form-section title="Patient details" :description="$editing ? 'Changes apply to future visits and documents.' : 'Required for registration.'" icon="user">
                <x-slot:aside>
                    <span class="text-xs text-fg-muted"><span class="field-required">*</span> Required</span>
                </x-slot:aside>

                <div class="form-grid">
                    <x-ui.field label="MR number" for="mr_number" :tag="$editing ? 'Fixed' : 'Auto'" error="mr_number" class="md:col-span-4 xl:col-span-3">
                        <input type="text" id="mr_number" name="mr_number" class="input input-mono" value="{{ $editing ? $patient->mr_number : old('mr_number', $formattedMrNumber ?? '') }}" readonly>
                    </x-ui.field>
                    <x-ui.field label="Registration date" for="registration_date" error="registration_date" required class="md:col-span-4 xl:col-span-3">
                        <input type="date" id="registration_date" name="registration_date" @class(['input', 'is-invalid' => $errors->has('registration_date')]) value="{{ old('registration_date', $editing ? optional($patient->registration_date)->format('Y-m-d') : date('Y-m-d')) }}" required>
                    </x-ui.field>

                    <x-ui.field label="Patient name" for="name" error="name" required class="md:col-span-8 xl:col-span-6 md:col-start-1 xl:col-start-1">
                        <input type="text" id="name" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="Full name as on CNIC" value="{{ old('name', $patient->name ?? '') }}" required @unless ($editing) autofocus @endunless autocomplete="off">
                    </x-ui.field>
                    <x-ui.field label="Mobile number" for="mobile_number" error="mobile_number" class="md:col-span-4 xl:col-span-3">
                        <input type="tel" id="mobile_number" name="mobile_number" @class(['input tabular-nums', 'is-invalid' => $errors->has('mobile_number')]) placeholder="03xx-xxxxxxx" value="{{ old('mobile_number', $patient->mobile_number ?? '') }}" autocomplete="off">
                    </x-ui.field>

                    <x-ui.field label="Date of birth" for="date_of_birth" error="date_of_birth" required class="md:col-span-4 xl:col-span-3 md:col-start-1 xl:col-start-1">
                        <input type="date" id="date_of_birth" name="date_of_birth" @class(['input', 'is-invalid' => $errors->has('date_of_birth')]) value="{{ old('date_of_birth', $editing ? optional($patient->date_of_birth)->format('Y-m-d') : '') }}" required>
                    </x-ui.field>
                    <x-ui.field label="Age" for="age" tag="Calculated" class="md:col-span-2 xl:col-span-2">
                        <div class="input-group has-end">
                            <input type="number" id="age" name="age" class="input input-num" placeholder="—" min="0" value="{{ old('age', $patient->age ?? '') }}" readonly>
                            <span class="input-affix is-end">yrs</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Gender" error="gender" required class="md:col-span-6 xl:col-span-4">
                        <div class="segmented" role="radiogroup" aria-label="Gender">
                            @foreach (['Male', 'Female', 'Other'] as $option)
                                <label class="segmented-option">
                                    <input type="radio" name="gender" id="gender_{{ strtolower($option) }}" value="{{ $option }}" {{ $gender == $option ? 'checked' : '' }} required>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Patient type" error="is_welfare" required class="md:col-span-6 xl:col-span-4 md:col-start-1 xl:col-start-1">
                        <div class="segmented" role="radiogroup" aria-label="Patient type">
                            <label class="segmented-option">
                                <input type="radio" name="is_welfare" id="is_welfare_normal" value="0" {{ $welfare === '0' ? 'checked' : '' }} required>
                                <span>Normal</span>
                            </label>
                            <label class="segmented-option">
                                <input type="radio" name="is_welfare" id="is_welfare_welfare" value="1" {{ $welfare === '1' ? 'checked' : '' }} required>
                                <span>Welfare</span>
                            </label>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Marital status" for="marital_status" error="marital_status" required class="md:col-span-6 xl:col-span-3">
                        <select id="marital_status" name="marital_status" @class(['select', 'is-invalid' => $errors->has('marital_status')]) required>
                            @foreach (['Single', 'Married', 'Divorced', 'Widowed'] as $option)
                                <option value="{{ $option }}" {{ $marital == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <section class="form-section">
                <button type="button" onclick="toggleOptionalInfo()" class="flex w-full items-center justify-between gap-3 text-left" aria-expanded="{{ $optionalOpen ? 'true' : 'false' }}" aria-controls="optional-fields" id="optional-toggle">
                    <span>
                        <span class="form-section-title"><x-ui.icon name="users" />Additional &amp; guardian information</span>
                        <span class="form-section-desc block">Optional — weight, CNIC, contact details and guardian.</span>
                    </span>
                    <span class="btn btn-ghost btn-sm">
                        <span data-optional-label>{{ $optionalOpen ? 'Hide' : 'Show' }}</span>
                        <x-ui.icon name="chevron-down" id="optional-icon" class="transition-transform {{ $optionalOpen ? 'rotate-180' : '' }}" />
                    </span>
                </button>

                <div id="optional-fields" @class(['mt-5', 'hidden' => ! $optionalOpen])>
                    <div class="form-grid">
                        <x-ui.field label="Weight" for="weight" error="weight" class="md:col-span-3 xl:col-span-2">
                            <div class="input-group has-end">
                                <input type="number" id="weight" name="weight" @class(['input input-num', 'is-invalid' => $errors->has('weight')]) placeholder="70" min="0" step="0.1" value="{{ old('weight', $patient->weight ?? '') }}">
                                <span class="input-affix is-end">kg</span>
                            </div>
                        </x-ui.field>
                        <x-ui.field label="Patient CNIC" for="cnic" error="cnic" class="md:col-span-4 xl:col-span-4">
                            <input type="text" id="cnic" name="cnic" @class(['input tabular-nums', 'is-invalid' => $errors->has('cnic')]) placeholder="12345-6789012-3" value="{{ old('cnic', $patient->cnic ?? '') }}" autocomplete="off">
                        </x-ui.field>
                        <x-ui.field label="Email" for="email" error="email" class="md:col-span-5 xl:col-span-6">
                            <input type="email" id="email" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) placeholder="patient@example.com" value="{{ old('email', $patient->email ?? '') }}" autocomplete="off">
                        </x-ui.field>
                        <x-ui.field label="Address" for="address" error="address" class="md:col-span-12">
                            <textarea id="address" name="address" rows="2" @class(['textarea min-h-0', 'is-invalid' => $errors->has('address')]) placeholder="House, street, area and city">{{ old('address', $patient->address ?? '') }}</textarea>
                        </x-ui.field>
                    </div>

                    <h4 class="section-heading mt-6 mb-3 border-t border-line-soft pt-5">Guardian or relative</h4>
                    <div class="form-grid">
                        <x-ui.field label="Relation" for="relation_type" error="relation_type" class="md:col-span-3">
                            <select id="relation_type" name="relation_type" class="select">
                                <option value="">Select relation</option>
                                @foreach (['Father', 'Mother', 'Spouse', 'Son', 'Daughter', 'Other'] as $option)
                                    <option value="{{ $option }}" {{ $relation == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Guardian name" for="guardian_name" error="guardian_name" class="md:col-span-5">
                            <input type="text" id="guardian_name" name="guardian_name" class="input" placeholder="Full name" value="{{ old('guardian_name', $patient->guardian_name ?? '') }}" autocomplete="off">
                        </x-ui.field>
                        <x-ui.field label="Guardian CNIC" for="guardian_cnic" error="guardian_cnic" class="md:col-span-4">
                            <input type="text" id="guardian_cnic" name="guardian_cnic" class="input tabular-nums" placeholder="12345-6789012-3" value="{{ old('guardian_cnic', $patient->guardian_cnic ?? '') }}" autocomplete="off">
                        </x-ui.field>
                    </div>
                </div>
            </section>

            <x-slot:footer class="is-sticky">
                @if ($editing)
                    <span class="footer-note">Age is recalculated from the date of birth when you save.</span>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="save" />Save changes
                    </button>
                @else
                    <span class="footer-note">The MR number is assigned when you save.</span>
                    <a href="{{ route('patients.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-busy-label="Registering…">
                        <x-ui.icon name="check" />Register patient
                    </button>
                @endif
            </x-slot:footer>
        </x-ui.panel>
    </form>

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
            const toggle = document.getElementById('optional-toggle');
            const label = toggle.querySelector('[data-optional-label]');

            if (optionalFields.classList.contains('hidden')) {
                optionalFields.classList.remove('hidden');
                icon.classList.add('rotate-180');
                toggle.setAttribute('aria-expanded', 'true');
                label.textContent = 'Hide';
            } else {
                optionalFields.classList.add('hidden');
                icon.classList.remove('rotate-180');
                toggle.setAttribute('aria-expanded', 'false');
                label.textContent = 'Show';
            }
        }
    </script>
@endsection
