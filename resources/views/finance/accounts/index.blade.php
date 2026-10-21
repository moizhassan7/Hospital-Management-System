@extends('layouts.app')

@section('title', 'Chart of accounts')

@section('content')
    <x-ui.page-header title="Chart of accounts" subtitle="Hospital account heads, control groups and postable accounts.">
        <x-slot:actions>
            <a href="{{ route('finance.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Finance
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="stack">
            @foreach($roots as $root)
                <x-ui.panel :title="$root->name" flush>
                    <x-slot:actions>
                        <span class="id">{{ $root->code }}</span>
                        <x-ui.badge tone="neutral">{{ $root->type }}</x-ui.badge>
                    </x-slot:actions>
                    <div class="divide-y divide-line">
                        @foreach($root->children as $child)
                            @if($child->is_postable)
                                <div class="flex items-center justify-between px-5 py-3">
                                    <span>
                                        <span class="id">{{ $child->code }}</span>
                                        <strong class="text-sm">{{ $child->name }}</strong>
                                    </span>
                                    <x-ui.badge tone="success">Posting account</x-ui.badge>
                                </div>
                            @else
                                <div class="bg-subtle px-5 py-2 text-[11px] font-semibold uppercase tracking-wider text-fg-muted">{{ $child->code }} · {{ $child->name }}</div>
                                @foreach($child->children as $account)
                                    <div class="flex items-center justify-between px-7 py-3">
                                        <span>
                                            <span class="id">{{ $account->code }}</span>
                                            <strong class="text-sm">{{ $account->name }}</strong>
                                        </span>
                                        <x-ui.badge tone="success">Posting account</x-ui.badge>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </x-ui.panel>
            @endforeach
        </div>

        <x-ui.panel title="Account mappings" description="System roles point to accounts, so posting logic never depends on database IDs." icon="sliders" class="lg:sticky lg:top-[84px]" flush>
            <div class="divide-y divide-line">
                @foreach($mappings as $mapping)
                    @if($canManageMappings)
                        <form method="POST" action="{{ route('finance.account_mappings.update', $mapping) }}" class="px-5 py-3">
                            @csrf
                            @method('PUT')
                            <label for="mapping-{{ $mapping->id }}" class="field-label">{{ $mapping->name }}</label>
                            <div class="mt-1 flex gap-2">
                                <select id="mapping-{{ $mapping->id }}" name="account_id" class="select min-w-0 flex-1">
                                    @foreach($postableAccounts as $account)
                                        <option value="{{ $account->id }}" @selected($mapping->account_id === $account->id)>{{ $account->code }} · {{ $account->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary btn-sm" type="submit">Save</button>
                            </div>
                        </form>
                    @else
                        <div class="px-5 py-3">
                            <p class="text-sm font-semibold">{{ $mapping->name }}</p>
                            <p class="mt-1 id">{{ $mapping->account->code }} · {{ $mapping->account->name }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-ui.panel>
    </div>
@endsection
