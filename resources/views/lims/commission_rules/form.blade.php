@extends('layouts.app')

@section('page_title', $rule->exists ? 'Edit Commission Rule' : 'Add Commission Rule')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => $rule->exists ? 'Edit commission rule' : 'Add commission rule',
        'subtitle' => 'Edits only affect future bookings — past snapshots stay frozen',
        'backUrl' => route('pathology.commission_rules.index'),
        'backLabel' => 'Back to rules',
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
            action="{{ $rule->exists ? route('pathology.commission_rules.update', $rule) : route('pathology.commission_rules.store') }}"
            id="commission-rule-form">
            @csrf
            @if($rule->exists)
                @method('PUT')
            @endif

            <div class="hms-form-grid mb-6">
                <x-form.field label="Doctor" for="doctor_id" hint="Leave blank for any doctor">
                    <select id="doctor_id" name="doctor_id" class="hms-select">
                        <option value="">Any doctor</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ (string) old('doctor_id', $rule->doctor_id) === (string) $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </x-form.field>

                <x-form.field label="Test category" for="test_category_id" hint="Leave blank for any category">
                    <select id="test_category_id" name="test_category_id" class="hms-select">
                        <option value="">Any category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('test_category_id', $rule->test_category_id) === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->code }} — {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </x-form.field>

                <x-form.field label="Collection center" for="collection_center_id" hint="Optional CC-specific override">
                    <select id="collection_center_id" name="collection_center_id" class="hms-select">
                        <option value="">Any center</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}" {{ (string) old('collection_center_id', $rule->collection_center_id) === (string) $center->id ? 'selected' : '' }}>
                                {{ $center->code }} — {{ $center->name }}
                            </option>
                        @endforeach
                    </select>
                </x-form.field>

                <x-form.field label="Basis" for="basis" :required="true">
                    <select id="basis" name="basis" class="hms-select" required>
                        <option value="percent" {{ old('basis', $rule->basis) === 'percent' ? 'selected' : '' }}>Percent</option>
                        <option value="fixed" {{ old('basis', $rule->basis) === 'fixed' ? 'selected' : '' }}>Fixed amount</option>
                    </select>
                </x-form.field>

                <x-form.field label="Percent (%)" for="percent" id="percent-field-wrap">
                    <input type="number" id="percent" name="percent" class="hms-input" step="0.0001" min="0" max="100"
                        value="{{ old('percent', $rule->percent) }}">
                </x-form.field>

                <x-form.field label="Fixed amount" for="amount" id="amount-field-wrap">
                    <input type="number" id="amount" name="amount" class="hms-input" step="0.01" min="0"
                        value="{{ old('amount', $rule->amount) }}">
                </x-form.field>

                <x-form.field label="Priority" for="priority" hint="Lower number = higher priority">
                    <input type="number" id="priority" name="priority" class="hms-input" min="0"
                        value="{{ old('priority', $rule->priority ?? 100) }}">
                </x-form.field>

                <x-form.field label="Effective from" for="effective_from" :required="true">
                    <input type="date" id="effective_from" name="effective_from" class="hms-input" required
                        value="{{ old('effective_from', optional($rule->effective_from)->format('Y-m-d') ?? now('Asia/Karachi')->toDateString()) }}">
                </x-form.field>

                <x-form.field label="Effective to" for="effective_to" hint="Leave blank for open-ended">
                    <input type="date" id="effective_to" name="effective_to" class="hms-input"
                        value="{{ old('effective_to', optional($rule->effective_to)->format('Y-m-d')) }}">
                </x-form.field>
            </div>

            <label class="hms-checkbox-row mb-6">
                <input type="checkbox" name="is_active" value="1" class="hms-checkbox" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
                <span class="hms-checkbox-label">Active</span>
            </label>

            <div class="hms-form-actions">
                <button type="submit" class="hms-btn hms-btn-primary">{{ $rule->exists ? 'Save rule' : 'Create rule' }}</button>
                <a href="{{ route('pathology.commission_rules.index') }}" class="hms-btn hms-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const basis = document.getElementById('basis');
    const percentWrap = document.getElementById('percent-field-wrap');
    const amountWrap = document.getElementById('amount-field-wrap');
    const percent = document.getElementById('percent');
    const amount = document.getElementById('amount');

    function sync() {
        const isFixed = basis.value === 'fixed';
        if (percentWrap) percentWrap.style.display = isFixed ? 'none' : '';
        if (amountWrap) amountWrap.style.display = isFixed ? '' : 'none';
        if (percent) percent.required = !isFixed;
        if (amount) amount.required = isFixed;
    }

    basis?.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
@endsection
