@extends('layouts.app')

@section('title', isset($doctor) ? 'Edit doctor' : 'Add doctor')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $workingDays = old('working_days', $doctor->working_days ?? []);
        $feeRows = [
            ['label' => 'General', 'sub' => 'Normal visit', 'fee' => 'general_normal_fee', 'share' => 'general_normal_percentage'],
            ['label' => 'General', 'sub' => 'Emergency', 'fee' => 'general_emergency_fee', 'share' => 'general_emergency_percentage'],
            ['label' => 'Welfare', 'sub' => 'Normal visit', 'fee' => 'welfare_normal_fee', 'share' => 'welfare_normal_percentage'],
            ['label' => 'Welfare', 'sub' => 'Emergency', 'fee' => 'welfare_emergency_fee', 'share' => 'welfare_emergency_percentage'],
        ];
    @endphp

    <x-ui.page-header :title="isset($doctor) ? 'Edit doctor' : 'Add doctor'" :subtitle="isset($doctor) ? $doctor->name . ' · ' . $doctor->code : 'Profile, working days, fees and revenue shares.'">
        <x-slot:actions>
            <a href="{{ route('doctors.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Doctors
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ isset($doctor) ? route('doctors.update', $doctor->id) : route('doctors.store') }}" method="POST" enctype="multipart/form-data" class="max-w-[1180px]">
        @csrf
        @isset($doctor)
            @method('PUT')
        @endisset

        <x-ui.panel flush>
            <x-ui.form-section title="Profile" icon="user">
                <div class="form-grid">
                    <x-ui.field label="Doctor name" for="doctor_name" error="doctor_name" required class="md:col-span-8 xl:col-span-6">
                        <input type="text" id="doctor_name" name="doctor_name" @class(['input', 'is-invalid' => $errors->has('doctor_name')]) placeholder="e.g. Dr. Full Name" value="{{ old('doctor_name', $doctor->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Doctor code" for="doctor_code" error="doctor_code" required class="md:col-span-4 xl:col-span-2">
                        <input type="text" id="doctor_code" name="doctor_code" @class(['input input-mono', 'is-invalid' => $errors->has('doctor_code')]) placeholder="DR001" value="{{ old('doctor_code', $doctor->code ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Mobile number" for="mobile_number" error="mobile_number" class="md:col-span-6 xl:col-span-4">
                        <input type="tel" id="mobile_number" name="mobile_number" class="input tabular-nums" placeholder="+923xx-xxxxxxx" value="{{ old('mobile_number', $doctor->mobile_number ?? '') }}">
                    </x-ui.field>

                    <x-ui.field label="Department" for="department_id" error="department_id" required class="md:col-span-6 xl:col-span-4 xl:col-start-1">
                        <select id="department_id" name="department_id" @class(['select', 'is-invalid' => $errors->has('department_id')]) required>
                            <option value="">Select department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $doctor->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Speciality" for="speciality_id" error="speciality_id" class="md:col-span-6 xl:col-span-4">
                        <select id="speciality_id" name="speciality_id" class="select">
                            <option value="">Select speciality</option>
                            @foreach ($specialities as $spec)
                                <option value="{{ $spec->id }}" {{ old('speciality_id', $doctor->speciality_id ?? '') == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Doctor type" for="doctor_type" error="doctor_type" required class="md:col-span-6 xl:col-span-4">
                        <select id="doctor_type" name="doctor_type" @class(['select', 'is-invalid' => $errors->has('doctor_type')]) required>
                            <option value="">Select doctor type</option>
                            @foreach ($doctorTypes as $type)
                                <option value="{{ $type->name }}" {{ old('doctor_type', $doctor->type ?? '') == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field label="Employee group" for="employee_group" error="employee_group" hint="Doctor and Procedure groups unlock revenue-share options below." class="md:col-span-6 xl:col-span-4">
                        <select id="employee_group" name="employee_group" class="select">
                            <option value="">Select group</option>
                            <option value="Doctor" {{ old('employee_group', $doctor->employee_group ?? '') == 'Doctor' ? 'selected' : '' }}>Doctor</option>
                            <option value="Procedure" {{ old('employee_group', $doctor->employee_group ?? '') == 'Procedure' ? 'selected' : '' }}>Procedure</option>
                            <option value="Service" {{ old('employee_group', $doctor->employee_group ?? '') == 'Service' ? 'selected' : '' }}>Service</option>
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Picture" for="doctor_picture" error="doctor_picture" hint="JPG, PNG or GIF up to 2 MB." class="md:col-span-6 xl:col-span-8">
                        <input type="file" id="doctor_picture" name="doctor_picture" accept="image/*" class="input">
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Practice details" icon="building">
                <div class="form-grid">
                    <x-ui.field label="Registration date" for="registration_date" error="registration_date" required class="md:col-span-4 xl:col-span-3">
                        <input type="date" id="registration_date" name="registration_date" class="input" value="{{ old('registration_date', $doctor->registration_date ?? date('Y-m-d')) }}" required>
                    </x-ui.field>
                    <x-ui.field label="Room location" for="room_location" error="room_location" class="md:col-span-8 xl:col-span-5">
                        <input type="text" id="room_location" name="room_location" class="input" placeholder="e.g. Room 203, OPD block" value="{{ old('room_location', $doctor->room_location ?? '') }}">
                    </x-ui.field>
                    <x-ui.field label="Accounts of" for="accounts_of" error="accounts_of" class="md:col-span-6 xl:col-span-4">
                        <input type="text" id="accounts_of" name="accounts_of" class="input" placeholder="e.g. bank account number" value="{{ old('accounts_of', $doctor->accounts_of ?? '') }}">
                    </x-ui.field>
                    <x-ui.field label="Office phone" for="office_phone" error="office_phone" class="md:col-span-3 xl:col-span-3 xl:col-start-1">
                        <input type="tel" id="office_phone" name="office_phone" class="input tabular-nums" placeholder="048-xxxxxxx" value="{{ old('office_phone', $doctor->office_phone ?? '') }}">
                    </x-ui.field>
                    <x-ui.field label="Reception phone" for="reception_phone" error="reception_phone" class="md:col-span-3 xl:col-span-3">
                        <input type="tel" id="reception_phone" name="reception_phone" class="input tabular-nums" placeholder="048-xxxxxxx" value="{{ old('reception_phone', $doctor->reception_phone ?? '') }}">
                    </x-ui.field>
                    <x-ui.field label="Address" for="address" error="address" class="md:col-span-12 xl:col-span-6">
                        <textarea id="address" name="address" rows="1" class="textarea min-h-0" placeholder="Doctor's address">{{ old('address', $doctor->address ?? '') }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Doctor portal" description="Optional sign-in for /doctor-portal. Leave the password blank when editing to keep the current one." icon="lock">
                <div class="form-grid">
                    <x-ui.field label="Portal email" for="email" error="email" class="md:col-span-6">
                        <input type="email" id="email" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) placeholder="doctor@hospital.com" value="{{ old('email', $doctor->email ?? '') }}" autocomplete="off">
                    </x-ui.field>
                    <x-ui.field label="Portal password" for="password" error="password" :hint="isset($doctor) ? 'Leave blank to keep the current password.' : 'At least 6 characters.'" class="md:col-span-6">
                        <input type="password" id="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) placeholder="{{ isset($doctor) && $doctor->password ? 'Unchanged' : 'Set a password' }}" autocomplete="new-password">
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Working days" icon="calendar">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-7">
                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                        <label class="check-card items-center">
                            <input type="checkbox" name="working_days[]" value="{{ $day }}" class="checkbox" {{ in_array($day, $workingDays) ? 'checked' : '' }}>
                            <span class="font-semibold">{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Fees & doctor shares" description="Fee charged to the patient and the doctor's share of it, by patient type and visit type." icon="banknote">
                <div class="table-wrap rounded-lg border border-line">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient type</th>
                                <th>Visit</th>
                                <th class="w-52">Fee ({{ $currency }})</th>
                                <th class="w-44">Doctor share (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($feeRows as $row)
                                <tr>
                                    <td><x-ui.badge :tone="$row['label'] === 'Welfare' ? 'info' : 'neutral'">{{ $row['label'] }}</x-ui.badge></td>
                                    <td class="cell-strong">{{ $row['sub'] }}</td>
                                    <td>
                                        <label for="{{ $row['fee'] }}" class="sr-only">{{ $row['label'] }} {{ strtolower($row['sub']) }} fee</label>
                                        <div class="input-group has-start">
                                            <span class="input-affix is-start">{{ $currency }}</span>
                                            <input type="number" id="{{ $row['fee'] }}" name="{{ $row['fee'] }}" @class(['input input-sm input-money', 'is-invalid' => $errors->has($row['fee'])]) value="{{ old($row['fee'], $doctor->{$row['fee']} ?? '0.00') }}" step="0.01">
                                        </div>
                                    </td>
                                    <td>
                                        <label for="{{ $row['share'] }}" class="sr-only">{{ $row['label'] }} {{ strtolower($row['sub']) }} doctor share</label>
                                        <div class="input-group has-end">
                                            <input type="number" id="{{ $row['share'] }}" name="{{ $row['share'] }}" @class(['input input-sm input-num', 'is-invalid' => $errors->has($row['share'])]) value="{{ old($row['share'], $doctor->{$row['share']} ?? '0.00') }}" min="0" max="100" step="0.01">
                                            <span class="input-affix is-end">%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Status & revenue sharing" icon="percent">
                <div class="space-y-3">
                    <label for="is_active" class="option-row">
                        <span>
                            <span class="option-row-title block">Doctor is active</span>
                            <span class="option-row-desc block">Turn off to mark this doctor as inactive.</span>
                        </span>
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="switch" {{ old('is_active', $doctor->is_active ?? true) ? 'checked' : '' }}>
                    </label>

                    <div id="is_shareable_container" style="display: none;">
                        <label for="is_shareable" class="option-row">
                            <span>
                                <span class="option-row-title block">Is shareable</span>
                                <span class="option-row-desc block">Eligible for doctor revenue sharing on this procedure.</span>
                            </span>
                            <input type="checkbox" name="is_shareable" id="is_shareable" value="1" class="switch" {{ old('is_shareable', $doctor->is_shareable ?? false) ? 'checked' : '' }}>
                        </label>
                    </div>

                    <div id="doctor_shares_container" style="display: none;">
                        <h4 class="section-heading mt-2">Doctor share percentages</h4>
                        @if (isset($shareableProcedures) && $shareableProcedures->count() > 0)
                            <div class="form-grid">
                                @foreach ($shareableProcedures as $proc)
                                    @php
                                        $existingShare = '';
                                        if (isset($doctor) && $doctor->shareableProcedures) {
                                            $pivot = $doctor->shareableProcedures->where('id', $proc->id)->first();
                                            if ($pivot) {
                                                $existingShare = $pivot->pivot->share_percentage;
                                            }
                                        }
                                    @endphp
                                    <x-ui.field :label="$proc->name" :for="'procedure_shares_' . $proc->id" class="md:col-span-6 xl:col-span-4">
                                        <div class="input-group has-end">
                                            <input type="number" id="procedure_shares_{{ $proc->id }}" name="procedure_shares[{{ $proc->id }}]" class="input input-num" placeholder="40" value="{{ old('procedure_shares.' . $proc->id, $existingShare) }}" min="0" max="100" step="0.01">
                                            <span class="input-affix is-end">%</span>
                                        </div>
                                    </x-ui.field>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[13px] text-fg-muted">No shareable procedures found.</p>
                        @endif
                    </div>
                </div>
            </x-ui.form-section>

            <x-slot:footer class="is-sticky">
                <a href="{{ route('doctors.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                    <x-ui.icon name="{{ isset($doctor) ? 'save' : 'user-plus' }}" />{{ isset($doctor) ? 'Update doctor' : 'Add doctor' }}
                </button>
            </x-slot:footer>
        </x-ui.panel>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const employeeGroupSelect = document.getElementById('employee_group');
            const isShareableContainer = document.getElementById('is_shareable_container');
            const doctorSharesContainer = document.getElementById('doctor_shares_container');

            function toggleVisibility() {
                const selected = employeeGroupSelect.value;
                if (selected === 'Procedure') {
                    isShareableContainer.style.display = 'block';
                    doctorSharesContainer.style.display = 'none';
                } else if (selected === 'Doctor') {
                    isShareableContainer.style.display = 'none';
                    doctorSharesContainer.style.display = 'block';
                } else {
                    isShareableContainer.style.display = 'none';
                    doctorSharesContainer.style.display = 'none';
                }
            }

            employeeGroupSelect.addEventListener('change', toggleVisibility);

            // Initial call to set correct state on page load (e.g. for edit mode or validation errors)
            toggleVisibility();
        });
    </script>
@endsection
