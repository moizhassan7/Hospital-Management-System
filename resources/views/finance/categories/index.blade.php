@extends('layouts.app')

@section('content')
<div class="finance-shell mx-auto max-w-6xl space-y-5 text-slate-800">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><a href="{{ route('finance.index') }}" class="finance-mono text-xs font-semibold uppercase tracking-wider text-teal-700">← Finance</a><h2 class="mt-2 text-3xl font-extrabold tracking-tight">Voucher categories</h2><p class="mt-1 text-sm text-slate-500">Configure Receipt (Credit) and Payment (Debit) counter-accounts without adding another voucher module.</p></div><a href="{{ route('finance.vouchers.create') }}" class="finance-focus rounded-xl bg-[#0f766e] px-4 py-2.5 text-sm font-extrabold text-white">Create voucher</a></div>

    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="rounded-2xl bg-[#0f2f2c] p-5 text-white shadow-xl">
        <div class="grid gap-5 lg:grid-cols-[15rem_1fr]">
            <div><p class="finance-mono text-[11px] font-semibold uppercase tracking-wider text-teal-200">New category</p><h3 class="mt-2 text-xl font-extrabold">Extend the voucher desk.</h3><p class="mt-2 text-xs leading-5 text-teal-50/75">Pick Receipt (Credit) or Payment (Debit) and its counter-account. Cash, bank or cheque determines CR/CP/BR/BP.</p></div>
            <form method="POST" action="{{ route('finance.categories.store') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@csrf
                <div><label class="mb-1 block text-xs font-bold text-teal-100">Category name *</label><input name="name" value="{{ old('name') }}" required placeholder="e.g. Donation received" class="finance-focus w-full rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-teal-100/40"></div>
                <div><label class="mb-1 block text-xs font-bold text-teal-100">Code</label><input name="code" value="{{ old('code') }}" placeholder="Auto from name" class="finance-focus w-full rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-teal-100/40"></div>
                <div><label class="mb-1 block text-xs font-bold text-teal-100">Posting nature *</label><select name="default_type" required class="finance-focus w-full rounded-xl border border-white/15 bg-[#173f3b] px-3 py-2.5 text-sm text-white"><option value="credit" @selected(old('default_type') === 'credit')>Receipt Voucher (Credit)</option><option value="debit" @selected(old('default_type','debit') === 'debit')>Payment Voucher (Debit)</option></select></div>
                <div class="sm:col-span-2"><label class="mb-1 block text-xs font-bold text-teal-100">Counter-account *</label><select name="account_id" required class="finance-focus w-full rounded-xl border border-white/15 bg-[#173f3b] px-3 py-2.5 text-sm text-white"><option value="">Select account</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->code }} · {{ $account->name }} ({{ ucfirst($account->type) }})</option>@endforeach</select></div>
                <div><label class="mb-1 block text-xs font-bold text-teal-100">Description</label><input name="description" value="{{ old('description') }}" placeholder="When this is used" class="finance-focus w-full rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-teal-100/40"></div>
                <div class="sm:col-span-2 lg:col-span-3"><button class="finance-focus rounded-xl bg-[#d7ff66] px-5 py-2.5 text-sm font-extrabold text-[#17312d] hover:bg-[#e2ff8f]">Add category</button></div>
            </form>
        </div>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between"><h3 class="text-lg font-extrabold">Available categories</h3><span class="finance-mono text-xs text-slate-400">{{ $categories->count() }} total</span></div>
        @foreach($categories as $category)
        <form method="POST" action="{{ route('finance.categories.update', $category) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">@csrf @method('PUT')
            <div class="grid items-end gap-3 md:grid-cols-[1.15fr_.8fr_.75fr_1.3fr_auto]">
                <div><div class="mb-1 flex items-center gap-2"><label class="text-xs font-bold text-slate-500">Name</label>@if($category->is_system)<span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">System</span>@endif</div><input name="name" value="{{ $category->name }}" required class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-500">Code</label><input name="code" value="{{ $category->code }}" required {{ $category->is_system ? 'readonly' : '' }} class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 finance-mono text-xs {{ $category->is_system ? 'bg-slate-50 text-slate-400' : '' }}"></div>
                <div><label class="mb-1 block text-xs font-bold text-slate-500">Posting nature</label><select name="default_type" {{ $category->is_system ? 'disabled' : '' }} class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><option value="credit" @selected($category->default_type === 'credit')>Receipt (Credit)</option><option value="debit" @selected($category->default_type === 'debit')>Payment (Debit)</option></select>@if($category->is_system)<input type="hidden" name="default_type" value="{{ $category->default_type }}">@endif</div>
                <div><label class="mb-1 block text-xs font-bold text-slate-500">Counter-account</label><select name="account_id" {{ $category->is_system ? 'disabled' : '' }} class="finance-focus w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">@foreach($accounts as $account)<option value="{{ $account->id }}" @selected($category->account_id === $account->id)>{{ $account->code }} · {{ $account->name }}</option>@endforeach</select>@if($category->is_system)<input type="hidden" name="account_id" value="{{ $category->account_id }}">@endif</div>
                <button class="finance-focus rounded-xl border border-teal-200 bg-teal-50 px-4 py-2.5 text-sm font-extrabold text-teal-800 hover:bg-teal-100">Save</button>
            </div>
            <div class="mt-3 flex flex-col gap-3 border-t border-slate-100 pt-3 sm:flex-row sm:items-center"><input name="description" value="{{ $category->description }}" placeholder="Category description" class="finance-focus flex-1 rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600">@if($category->is_system)<input type="hidden" name="is_active" value="1"><span class="text-xs font-semibold text-emerald-700">Required system category · always active</span>@else<label class="inline-flex items-center gap-2 text-xs font-bold text-slate-600"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="rounded border-slate-300 text-teal-700">Active for new vouchers</label>@endif</div>
        </form>
        @endforeach
    </section>
</div>
@endsection
