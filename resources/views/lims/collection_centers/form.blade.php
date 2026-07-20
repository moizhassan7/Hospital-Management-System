@extends('layouts.app')

@section('page_title', $center->exists ? 'Edit Collection Center' : 'Add Collection Center')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => $center->exists ? 'Edit collection center' : 'Add collection center',
        'subtitle' => 'Code and lab-number prefix must be unique within the organization',
        'backUrl' => route('pathology.collection_centers.index'),
        'backLabel' => 'Back to list',
    ])

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="hms-panel hms-panel-padded max-w-3xl">
        <form method="POST"
            action="{{ $center->exists ? route('pathology.collection_centers.update', $center) : route('pathology.collection_centers.store') }}">
            @csrf
            @if($center->exists)
                @method('PUT')
            @endif

            <div class="hms-form-grid mb-6">
                @if($organizations->count() > 1)
                    <x-form.field label="Organization" for="organization_id" :required="true">
                        <select id="organization_id" name="organization_id" class="hms-select" required>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ (string) old('organization_id', $center->organization_id) === (string) $org->id ? 'selected' : '' }}>
                                    {{ $org->code }} — {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-form.field>
                @elseif($organizations->isNotEmpty())
                    <input type="hidden" name="organization_id" value="{{ old('organization_id', $center->organization_id ?: $organizations->first()->id) }}">
                @endif

                <x-form.field label="Code" for="code" :required="true" hint="e.g. CC1, MAIN">
                    <input type="text" id="code" name="code" class="hms-input" value="{{ old('code', $center->code) }}" required maxlength="32">
                </x-form.field>

                <x-form.field label="Name" for="name" :required="true">
                    <input type="text" id="name" name="name" class="hms-input" value="{{ old('name', $center->name) }}" required maxlength="255">
                </x-form.field>

                <x-form.field label="Kind" for="kind" :required="true">
                    <select id="kind" name="kind" class="hms-select" required @if($center->exists && $center->isMainLab()) disabled @endif>
                        <option value="collection_center" {{ old('kind', $center->kind) === 'collection_center' ? 'selected' : '' }}>Collection Center</option>
                        <option value="main_lab" {{ old('kind', $center->kind) === 'main_lab' ? 'selected' : '' }}>Main Lab</option>
                    </select>
                    @if($center->exists && $center->isMainLab())
                        <input type="hidden" name="kind" value="main_lab">
                    @endif
                </x-form.field>

                <x-form.field label="Lab number prefix" for="lab_number_prefix" :required="true" hint="Used in lab numbers e.g. CC1-202607-0001">
                    <input type="text" id="lab_number_prefix" name="lab_number_prefix" class="hms-input" value="{{ old('lab_number_prefix', $center->lab_number_prefix) }}" required maxlength="16">
                </x-form.field>

                <x-form.field label="Phone" for="phone">
                    <input type="text" id="phone" name="phone" class="hms-input" value="{{ old('phone', $center->phone) }}" maxlength="50">
                </x-form.field>

                <x-form.field label="Address" for="address" class="md:col-span-2 lg:col-span-3">
                    <textarea id="address" name="address" rows="2" class="hms-textarea">{{ old('address', $center->address) }}</textarea>
                </x-form.field>
            </div>

            <label class="hms-checkbox-row mb-6">
                <input type="checkbox" name="is_active" value="1" class="hms-checkbox" {{ old('is_active', $center->is_active ?? true) ? 'checked' : '' }}>
                <span class="hms-checkbox-label">Active (available for booking)</span>
            </label>

            <div class="hms-form-actions">
                <button type="submit" class="hms-btn hms-btn-primary">{{ $center->exists ? 'Save changes' : 'Create center' }}</button>
                <a href="{{ route('pathology.collection_centers.index') }}" class="hms-btn hms-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
