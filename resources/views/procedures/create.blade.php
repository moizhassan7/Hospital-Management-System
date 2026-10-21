@extends('layouts.app')

@section('title', isset($procedure) ? 'Edit procedure' : 'Add procedure')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $types = ['Major', 'Minor'];
        $performedFor = ['General', 'Male', 'Female'];
        $currentType = old('type', $procedure->type ?? '');
        $currentFor = old('performed_for', $procedure->performed_for ?? '');
        $feeRows = [
            ['label' => 'General', 'sub' => 'Normal', 'fee' => 'general_normal_fee', 'share' => 'general_normal_doctor_percentage'],
            ['label' => 'General', 'sub' => 'Emergency', 'fee' => 'general_emergency_fee', 'share' => 'general_emergency_doctor_percentage'],
            ['label' => 'Welfare', 'sub' => 'Normal', 'fee' => 'welfare_normal_fee', 'share' => 'welfare_normal_doctor_percentage'],
            ['label' => 'Welfare', 'sub' => 'Emergency', 'fee' => 'welfare_emergency_fee', 'share' => 'welfare_emergency_doctor_percentage'],
        ];
    @endphp

    <x-ui.page-header :title="isset($procedure) ? 'Edit procedure' : 'Add procedure'" :subtitle="isset($procedure) ? $procedure->name : 'Name, department, fees and doctor shares.'">
        <x-slot:actions>
            <a href="{{ route('procedures.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Procedures
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ isset($procedure) ? route('procedures.update', $procedure) : route('procedures.store') }}" method="POST" class="max-w-[1080px]">
        @csrf
        @isset($procedure)
            @method('PUT')
        @endisset

        <x-ui.panel flush>
            <x-ui.form-section title="Procedure" icon="scissors">
                <div class="form-grid">
                    <x-ui.field label="Procedure name" for="name" error="name" required class="md:col-span-8">
                        <input type="text" id="name" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="e.g. Appendectomy" value="{{ old('name', $procedure->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Room location" for="room_location" error="room_location" class="md:col-span-4">
                        <input type="text" id="room_location" name="room_location" class="input" placeholder="e.g. OT 2" value="{{ old('room_location', $procedure->room_location ?? '') }}">
                    </x-ui.field>

                    <x-ui.field label="Type" error="type" required class="md:col-span-4"
                        :hint="$currentType && ! in_array($currentType, $types) ? 'Saved as “' . $currentType . '”. Choose Major or Minor to save changes.' : null">
                        <div class="segmented" role="radiogroup" aria-label="Type">
                            @foreach ($types as $type)
                                <label class="segmented-option">
                                    <input type="radio" name="type" id="type_{{ strtolower($type) }}" value="{{ $type }}" @checked($currentType === $type) required>
                                    <span>{{ $type }}</span>
                                </label>
                            @endforeach
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Performed for" error="performed_for" required class="md:col-span-8"
                        :hint="$currentFor && ! in_array($currentFor, $performedFor) ? 'Saved as “' . $currentFor . '”. Choose General, Male or Female to save changes.' : null">
                        <div class="segmented" role="radiogroup" aria-label="Performed for">
                            @foreach ($performedFor as $option)
                                <label class="segmented-option">
                                    <input type="radio" name="performed_for" id="performed_for_{{ strtolower($option) }}" value="{{ $option }}" @checked($currentFor === $option) required>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Department" for="department_id" error="department_id" required class="md:col-span-6">
                        <select id="department_id" name="department_id" @class(['select', 'is-invalid' => $errors->has('department_id')]) required>
                            <option value="">Select department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id', $procedure->department_id ?? '') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Speciality" for="speciality_id" error="speciality_id" class="md:col-span-6">
                        <select id="speciality_id" name="speciality_id" class="select">
                            <option value="">No speciality</option>
                            @foreach ($specialities as $spec)
                                <option value="{{ $spec->id }}" @selected(old('speciality_id', $procedure->speciality_id ?? '') == $spec->id)>{{ $spec->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Fees & doctor shares" description="Fee charged to the patient and the doctor's share of it." icon="banknote">
                <div class="table-wrap rounded-lg border border-line">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient type</th>
                                <th>Case</th>
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
                                            <input type="number" id="{{ $row['fee'] }}" name="{{ $row['fee'] }}" @class(['input input-sm input-money', 'is-invalid' => $errors->has($row['fee'])]) value="{{ old($row['fee'], $procedure->{$row['fee']} ?? '0') }}" min="0" step="0.01" required>
                                        </div>
                                    </td>
                                    <td>
                                        <label for="{{ $row['share'] }}" class="sr-only">{{ $row['label'] }} {{ strtolower($row['sub']) }} doctor share</label>
                                        <div class="input-group has-end">
                                            <input type="number" id="{{ $row['share'] }}" name="{{ $row['share'] }}" @class(['input input-sm input-num', 'is-invalid' => $errors->has($row['share'])]) value="{{ old($row['share'], $procedure->{$row['share']} ?? '0') }}" min="0" max="100" step="0.01" required>
                                            <span class="input-affix is-end">%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.form-section>

            <x-slot:footer class="is-sticky">
                <a href="{{ route('procedures.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                    <x-ui.icon name="{{ isset($procedure) ? 'save' : 'plus' }}" />{{ isset($procedure) ? 'Update procedure' : 'Add procedure' }}
                </button>
            </x-slot:footer>
        </x-ui.panel>
    </form>
@endsection
