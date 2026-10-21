@extends('layouts.app')

@section('title', 'Birth certificates')

@section('content')
    <x-ui.page-header title="Birth certificates" subtitle="Issue birth certificates for babies delivered at the hospital.">
        <x-slot:meta>
            <x-ui.badge tone="warning" dot>Preview</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="warning" title="Birth certificates are not connected yet" class="mb-5">
        Submitting the form will not issue a certificate, and the list below shows sample rows only.
    </x-ui.alert>

    <div class="stack">
        <x-ui.panel title="Issue new birth certificate" icon="award" flush>
            <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
                @csrf
                <x-ui.form-section title="Baby" class="pt-4">
                    <div class="form-grid">
                        <x-ui.field label="Baby's name" for="baby_name" required class="md:col-span-6 xl:col-span-4">
                            <input type="text" id="baby_name" name="baby_name" class="input" placeholder="e.g. Baby Smith" required>
                        </x-ui.field>
                        <x-ui.field label="Date of birth" for="dob" required class="md:col-span-3 xl:col-span-2">
                            <input type="date" id="dob" name="dob" class="input" value="{{ date('Y-m-d') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Time of birth" for="time_of_birth" required class="md:col-span-3 xl:col-span-2">
                            <input type="time" id="time_of_birth" name="time_of_birth" class="input" value="{{ date('H:i') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Gender" for="gender" required class="md:col-span-4 xl:col-span-2">
                            <select id="gender" name="gender" class="select" required>
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Place of birth" for="place_of_birth" required class="md:col-span-8 xl:col-span-6">
                            <input type="text" id="place_of_birth" name="place_of_birth" class="input" value="Hospital Name" required>
                        </x-ui.field>
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Parents">
                    <div class="form-grid">
                        <x-ui.field label="Mother's name" for="mother_name" required class="md:col-span-6">
                            <input type="text" id="mother_name" name="mother_name" class="input" placeholder="e.g. Jane Doe" required>
                        </x-ui.field>
                        <x-ui.field label="Mother's CNIC" for="mother_cnic" class="md:col-span-6 xl:col-span-4">
                            <input type="text" id="mother_cnic" name="mother_cnic" class="input tabular-nums" placeholder="12345-6789012-3">
                        </x-ui.field>
                        <x-ui.field label="Father's name" for="father_name" class="md:col-span-6 md:col-start-1">
                            <input type="text" id="father_name" name="father_name" class="input" placeholder="e.g. John Doe">
                        </x-ui.field>
                        <x-ui.field label="Father's CNIC" for="father_cnic" class="md:col-span-6 xl:col-span-4">
                            <input type="text" id="father_cnic" name="father_cnic" class="input tabular-nums" placeholder="12345-6789012-3">
                        </x-ui.field>
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Certificate">
                    <div class="form-grid">
                        <x-ui.field label="Delivered by" for="doctor_delivered" required class="md:col-span-6 xl:col-span-4">
                            <select id="doctor_delivered" name="doctor_delivered" class="select" required>
                                <option value="">Select doctor</option>
                                {{-- Static Doctors for dropdown --}}
                                <option value="DOC001">Dr. Alice Smith</option>
                                <option value="DOC002">Dr. Bob Johnson</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Registration no." for="registration_number" required class="md:col-span-3 xl:col-span-3">
                            <input type="text" id="registration_number" name="registration_number" class="input input-mono" placeholder="BC-2025-001" required>
                        </x-ui.field>
                        <x-ui.field label="Date of issue" for="date_of_issue" required class="md:col-span-3 xl:col-span-2">
                            <input type="date" id="date_of_issue" name="date_of_issue" class="input" value="{{ date('Y-m-d') }}" required>
                        </x-ui.field>
                    </div>
                </x-ui.form-section>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary"><x-ui.icon name="file-check" />Issue certificate</button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Issued birth certificates" flush>
            <x-slot:actions>
                <x-ui.badge tone="warning">Sample rows</x-ui.badge>
            </x-slot:actions>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Reg. no.</th>
                            <th>Baby's name</th>
                            <th>Date of birth</th>
                            <th>Gender</th>
                            <th>Mother's name</th>
                            <th>Father's name</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Static Birth Certificate Data --}}
                        @php
                            $birthCertificates = [
                                ['reg_no' => 'BC-2025-001', 'baby_name' => 'Baby A', 'dob' => '2025-07-10', 'gender' => 'Female', 'mother_name' => 'Jane Doe', 'father_name' => 'John Doe'],
                                ['reg_no' => 'BC-2025-002', 'baby_name' => 'Baby B', 'dob' => '2025-07-12', 'gender' => 'Male', 'mother_name' => 'Alice Smith', 'father_name' => 'Bob Smith'],
                                ['reg_no' => 'BC-2025-003', 'baby_name' => 'Baby C', 'dob' => '2025-07-15', 'gender' => 'Female', 'mother_name' => 'Sarah Johnson', 'father_name' => 'David Johnson'],
                            ];
                        @endphp
                        @foreach ($birthCertificates as $index => $cert)
                            <tr>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $cert['reg_no'] }}</span></td>
                                <td class="cell-strong">{{ $cert['baby_name'] }}</td>
                                <td class="nowrap">{{ $cert['dob'] }}</td>
                                <td>{{ $cert['gender'] }}</td>
                                <td>{{ $cert['mother_name'] }}</td>
                                <td>{{ $cert['father_name'] }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="#" class="btn btn-ghost btn-sm">View</a>
                                        <a href="#" class="btn btn-danger-ghost btn-sm">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
@endsection
