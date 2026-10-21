@extends('layouts.app')

@section('title', 'Hospital settings')

@section('content')
    <x-ui.page-header title="Hospital settings" subtitle="Name, address, currency and logo used on receipts and the application shell." />

    <x-ui.panel title="Organisation" icon="settings" flush>
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="Hospital name" for="hospital_name" error="hospital_name" required class="md:col-span-8">
                        <input type="text" id="hospital_name" name="hospital_name" @class(['input', 'is-invalid' => $errors->has('hospital_name')]) value="{{ old('hospital_name', $setting->hospital_name ?? '') }}" required>
                    </x-ui.field>
                    <x-ui.field label="Currency symbol" for="currency_symbol" error="currency_symbol" required class="md:col-span-4">
                        <input type="text" id="currency_symbol" name="currency_symbol" @class(['input', 'is-invalid' => $errors->has('currency_symbol')]) value="{{ old('currency_symbol', $setting->currency_symbol ?? 'Rs') }}" required placeholder="e.g. PKR, USD, Rs">
                    </x-ui.field>
                    <x-ui.field label="Hospital address" for="address" error="address" class="md:col-span-12">
                        <textarea id="address" name="address" rows="3" @class(['input', 'is-invalid' => $errors->has('address')])>{{ old('address', $setting->address ?? '') }}</textarea>
                    </x-ui.field>
                    <x-ui.field label="Hospital logo (optional)" for="logo" error="logo" class="md:col-span-12">
                        @if(isset($setting) && $setting->logo)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $setting->logo) }}" alt="Current logo" class="h-20 rounded-lg border border-line bg-subtle p-2">
                                <p class="field-hint">Current logo</p>
                            </div>
                        @endif
                        <input type="file" id="logo" name="logo" accept="image/*" @class(['input', 'is-invalid' => $errors->has('logo')])>
                    </x-ui.field>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                    <x-ui.icon name="save" />Save settings
                </button>
            </div>
        </form>
    </x-ui.panel>
@endsection
