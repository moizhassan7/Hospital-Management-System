@extends('layouts.app')

@section('content')
<div class="finance-shell mx-auto max-w-6xl space-y-5 text-slate-800">
    <div>
        <a href="{{ route('finance.index') }}" class="finance-mono text-xs font-semibold uppercase tracking-wider text-teal-700">← Finance</a>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Chart of accounts</h2>
        <p class="mt-1 text-sm text-slate-500">Hospital account heads, control groups and postable accounts.</p>
    </div>
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="space-y-3">
            @foreach($roots as $root)
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between bg-[#0f2f2c] px-5 py-3 text-white">
                        <div>
                            <span class="finance-mono text-xs text-teal-200">{{ $root->code }}</span>
                            <h3 class="font-extrabold">{{ $root->name }}</h3>
                        </div>
                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase">{{ $root->type }}</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($root->children as $child)
                            @if($child->is_postable)
                                <div class="flex items-center justify-between px-5 py-3">
                                    <span><span class="finance-mono mr-2 text-xs text-slate-400">{{ $child->code }}</span><strong class="text-sm">{{ $child->name }}</strong></span>
                                    <span class="text-[10px] font-bold uppercase text-emerald-700">Posting account</span>
                                </div>
                            @else
                                <div class="bg-slate-50 px-5 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ $child->code }} · {{ $child->name }}</div>
                                @foreach($child->children as $account)
                                    <div class="flex items-center justify-between px-7 py-3">
                                        <span><span class="finance-mono mr-2 text-xs text-slate-400">{{ $account->code }}</span><strong class="text-sm">{{ $account->name }}</strong></span>
                                        <span class="text-[10px] font-bold uppercase text-emerald-700">Posting account</span>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </article>
            @endforeach
        </section>

        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-5">
            <p class="finance-mono text-[11px] font-semibold uppercase tracking-wider text-teal-700">Account mappings</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">System roles point to accounts, so posting logic never depends on database IDs.</p>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($mappings as $mapping)
                    @if($canManageMappings)
                        <form method="POST" action="{{ route('finance.account_mappings.update', $mapping) }}" class="py-3">
                            @csrf @method('PUT')
                            <label for="mapping-{{ $mapping->id }}" class="text-xs font-bold text-slate-700">{{ $mapping->name }}</label>
                            <div class="mt-1 flex gap-2">
                                <select id="mapping-{{ $mapping->id }}" name="account_id" class="finance-focus min-w-0 flex-1 rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                                    @foreach($postableAccounts as $account)<option value="{{ $account->id }}" @selected($mapping->account_id === $account->id)>{{ $account->code }} · {{ $account->name }}</option>@endforeach
                                </select>
                                <button class="finance-focus rounded-lg bg-teal-700 px-2.5 text-[10px] font-extrabold text-white">Save</button>
                            </div>
                        </form>
                    @else
                        <div class="py-3">
                            <p class="text-xs font-bold text-slate-700">{{ $mapping->name }}</p>
                            <p class="mt-1 finance-mono text-[10px] text-slate-400">{{ $mapping->account->code }} · {{ $mapping->account->name }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </aside>
    </div>
</div>
@endsection
