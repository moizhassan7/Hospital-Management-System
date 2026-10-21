@extends('layouts.app')

@section('title', 'Death certificates')

@section('content')
    <x-ui.page-header title="Death certificates" subtitle="Issue death certificates for patients who died at the hospital.">
        <x-slot:meta>
            <x-ui.badge tone="warning" dot>Preview</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="warning" title="Death certificates are not connected yet" class="mb-5">
        Submitting the form will not issue a certificate, and the list below shows sample rows only.
    </x-ui.alert>

    <div class="stack">
        <x-ui.panel title="Issue new death certificate" icon="file-check" flush>
            <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
                @csrf
                <x-ui.form-section title="Deceased" class="pt-4">
                    <div class="form-grid">
                        <x-ui.field label="Deceased's name" for="deceased_name" required class="md:col-span-6 xl:col-span-5">
                            <input type="text" id="deceased_name" name="deceased_name" class="input" placeholder="e.g. John Doe" required>
                        </x-ui.field>
                        <x-ui.field label="Patient MR no." for="mr_no" class="md:col-span-6 xl:col-span-3">
                            <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001">
                        </x-ui.field>
                        <x-ui.field label="Date of death" for="date_of_death" required class="md:col-span-3 xl:col-span-2 md:col-start-1 xl:col-start-auto">
                            <input type="date" id="date_of_death" name="date_of_death" class="input" value="{{ date('Y-m-d') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Time of death" for="time_of_death" required class="md:col-span-3 xl:col-span-2">
                            <input type="time" id="time_of_death" name="time_of_death" class="input" value="{{ date('H:i') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Place of death" for="place_of_death" required class="md:col-span-6 xl:col-span-5 xl:col-start-1">
                            <input type="text" id="place_of_death" name="place_of_death" class="input" value="Hospital Name" required>
                        </x-ui.field>
                        <x-ui.field label="Cause of death" for="cause_of_death" required class="md:col-span-12 xl:col-span-7">
                            <textarea id="cause_of_death" name="cause_of_death" rows="1" class="textarea min-h-0" placeholder="e.g. Cardiac arrest" required></textarea>
                        </x-ui.field>
                        <x-ui.field label="Certified by" for="certifying_doctor" required class="md:col-span-6 xl:col-span-5">
                            <select id="certifying_doctor" name="certifying_doctor" class="select" required>
                                <option value="">Select doctor</option>
                                {{-- Static Doctors for dropdown --}}
                                <option value="DOC001">Dr. Alice Smith</option>
                                <option value="DOC002">Dr. Bob Johnson</option>
                            </select>
                        </x-ui.field>
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Next of kin and certificate">
                    <div class="form-grid">
                        <x-ui.field label="Next of kin name" for="next_of_kin_name" class="md:col-span-6 xl:col-span-4">
                            <input type="text" id="next_of_kin_name" name="next_of_kin_name" class="input" placeholder="e.g. Mary Doe">
                        </x-ui.field>
                        <x-ui.field label="Next of kin CNIC" for="next_of_kin_cnic" class="md:col-span-6 xl:col-span-3">
                            <input type="text" id="next_of_kin_cnic" name="next_of_kin_cnic" class="input tabular-nums" placeholder="12345-6789012-3">
                        </x-ui.field>
                        <x-ui.field label="Registration no." for="registration_number" required class="md:col-span-6 xl:col-span-3">
                            <input type="text" id="registration_number" name="registration_number" class="input input-mono" placeholder="DC-2025-001" required>
                        </x-ui.field>
                        <x-ui.field label="Date of issue" for="date_of_issue" required class="md:col-span-6 xl:col-span-2">
                            <input type="date" id="date_of_issue" name="date_of_issue" class="input" value="{{ date('Y-m-d') }}" required>
                        </x-ui.field>
                    </div>
                </x-ui.form-section>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary"><x-ui.icon name="file-check" />Issue certificate</button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Issued death certificates" flush>
            <x-slot:actions>
                <x-ui.badge tone="warning">Sample rows</x-ui.badge>
            </x-slot:actions>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Reg. no.</th>
                            <th>Deceased name</th>
                            <th>Date of death</th>
                            <th>Cause of death</th>
                            <th>Certified by</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Static Death Certificate Data --}}
                        @php
                            $deathCertificates = [
                                ['reg_no' => 'DC-2025-001', 'deceased_name' => 'Patient X', 'dod' => '2025-07-05', 'cause' => 'Heart Failure', 'doctor' => 'Dr. Alice Smith'],
                                ['reg_no' => 'DC-2025-002', 'deceased_name' => 'Patient Y', 'dod' => '2025-07-10', 'cause' => 'Respiratory Failure', 'doctor' => 'Dr. Bob Johnson'],
                            ];
                        @endphp
                        @foreach ($deathCertificates as $index => $cert)
                            <tr>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $cert['reg_no'] }}</span></td>
                                <td class="cell-strong">{{ $cert['deceased_name'] }}</td>
                                <td class="nowrap">{{ $cert['dod'] }}</td>
                                <td>{{ $cert['cause'] }}</td>
                                <td>{{ $cert['doctor'] }}</td>
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
