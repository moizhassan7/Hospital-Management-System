@extends('layouts.app')

@section('title', 'Voucher categories')

@section('content')
    <x-ui.page-header title="Voucher categories" subtitle="Receipt (Credit) and Payment (Debit) counter-accounts used on the voucher desk.">
        <x-slot:actions>
            <a href="{{ route('finance.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Finance
            </a>
            <a href="{{ route('finance.vouchers.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" />Create voucher
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="New category" description="Pick Receipt (Credit) or Payment (Debit) and its counter-account. Cash, bank or cheque determines CR/CP/BR/BP." icon="plus" flush>
        <form method="POST" action="{{ route('finance.categories.store') }}">
            @csrf
            <div class="panel-body">
                <div class="form-grid">
                    <x-ui.field label="Category name" error="name" required class="md:col-span-4">
                        <input name="name" value="{{ old('name') }}" required placeholder="e.g. Donation received" class="input">
                    </x-ui.field>
                    <x-ui.field label="Code" error="code" class="md:col-span-4">
                        <input name="code" value="{{ old('code') }}" placeholder="Auto from name" class="input">
                    </x-ui.field>
                    <x-ui.field label="Posting nature" error="default_type" required class="md:col-span-4">
                        <select name="default_type" required class="select">
                            <option value="credit" @selected(old('default_type') === 'credit')>Receipt Voucher (Credit)</option>
                            <option value="debit" @selected(old('default_type','debit') === 'debit')>Payment Voucher (Debit)</option>
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Counter-account" error="account_id" required class="md:col-span-8">
                        <select name="account_id" required class="select">
                            <option value="">Select account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->code }} · {{ $account->name }} ({{ ucfirst($account->type) }})</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Description" error="description" class="md:col-span-4">
                        <input name="description" value="{{ old('description') }}" placeholder="When this is used" class="input">
                    </x-ui.field>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-primary" type="submit">Add category</button>
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel title="Available categories" class="mt-5" flush>
        <x-slot:actions>
            <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg">{{ $categories->count() }}</span> total</span>
        </x-slot:actions>
        <div class="divide-y divide-line">
            @foreach($categories as $category)
                <form method="POST" action="{{ route('finance.categories.update', $category) }}" class="px-5 py-4">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <x-ui.field label="Name" class="md:col-span-4">
                            <div class="flex items-center gap-2">
                                <input name="name" value="{{ $category->name }}" required class="input">
                                @if($category->is_system)
                                    <x-ui.badge tone="neutral">System</x-ui.badge>
                                @endif
                            </div>
                        </x-ui.field>
                        <x-ui.field label="Code" class="md:col-span-2">
                            <input name="code" value="{{ $category->code }}" required {{ $category->is_system ? 'readonly' : '' }} class="input {{ $category->is_system ? 'bg-subtle' : '' }}">
                        </x-ui.field>
                        <x-ui.field label="Posting nature" class="md:col-span-3">
                            <select name="default_type" {{ $category->is_system ? 'disabled' : '' }} class="select">
                                <option value="credit" @selected($category->default_type === 'credit')>Receipt (Credit)</option>
                                <option value="debit" @selected($category->default_type === 'debit')>Payment (Debit)</option>
                            </select>
                            @if($category->is_system)
                                <input type="hidden" name="default_type" value="{{ $category->default_type }}">
                            @endif
                        </x-ui.field>
                        <x-ui.field label="Counter-account" class="md:col-span-3">
                            <select name="account_id" {{ $category->is_system ? 'disabled' : '' }} class="select">
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" @selected($category->account_id === $account->id)>{{ $account->code }} · {{ $account->name }}</option>
                                @endforeach
                            </select>
                            @if($category->is_system)
                                <input type="hidden" name="account_id" value="{{ $category->account_id }}">
                            @endif
                        </x-ui.field>
                        <x-ui.field label="Description" class="md:col-span-9">
                            <input name="description" value="{{ $category->description }}" placeholder="Category description" class="input">
                        </x-ui.field>
                        <div class="md:col-span-3 flex items-end justify-between gap-3">
                            @if($category->is_system)
                                <input type="hidden" name="is_active" value="1">
                                <span class="text-[12.5px] font-semibold text-fg-muted">Always active</span>
                            @else
                                <label class="option-row">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="checkbox" @checked($category->is_active)>
                                    <span class="option-row-title">Active</span>
                                </label>
                            @endif
                            <button class="btn btn-primary" type="submit">Save</button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </x-ui.panel>
@endsection
