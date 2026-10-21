@extends('layouts.app')

@section('title', isset($user) ? 'Edit user' : 'Users')

@section('content')
    <x-ui.page-header :title="isset($user) ? 'Edit user' : 'Users'" :subtitle="isset($user) ? $user->name . ' · ' . $user->username : 'Staff accounts, roles and individual permission overrides.'">
        <x-slot:actions>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <x-ui.icon name="shield-check" />Manage roles
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form id="user_manager_form" action="{{ isset($user) ? route('admin.user_manager.update', $user->id) : route('admin.user_manager.store') }}" method="POST">
        @csrf
        @isset($user)
            @method('PUT')
            <input type="hidden" name="user_id" value="{{ $user->id }}">
        @endisset

        <div class="stack">
            <x-ui.panel title="User details" icon="user" flush>
                <div class="panel-body">
                    <div class="form-grid">
                        <x-ui.field label="Select user (to edit)" for="user_id_select" class="md:col-span-4">
                            <select id="user_id_select" onchange="window.location.href='/admin/user-manager/' + this.value + '/edit'" class="select">
                                <option value="">— New user —</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected(isset($user) && $user->id == $u->id)>{{ $u->username }} — {{ $u->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Full name" for="name" error="name" required class="md:col-span-4">
                            <input type="text" id="name" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="Full name" value="{{ old('name', $user->name ?? '') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Username" for="username" error="username" required class="md:col-span-4">
                            <input type="text" id="username" name="username" @class(['input', 'is-invalid' => $errors->has('username')]) placeholder="Username" value="{{ old('username', $user->username ?? '') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Email" for="email" error="email" class="md:col-span-4">
                            <input type="email" id="email" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) placeholder="Email" value="{{ old('email', $user->email ?? '') }}">
                        </x-ui.field>
                        <x-ui.field label="Branch" for="branch" error="branch" class="md:col-span-4">
                            <input type="text" id="branch" name="branch" @class(['input', 'is-invalid' => $errors->has('branch')]) placeholder="Branch" value="{{ old('branch', $user->branch ?? '') }}">
                        </x-ui.field>
                        <x-ui.field label="Password" for="password" error="password" :required="! isset($user)" :hint="isset($user) ? 'Leave blank to keep the current password.' : null" class="md:col-span-4">
                            <input type="password" id="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) placeholder="{{ isset($user) ? 'Set new password (optional)' : 'Password' }}" {{ isset($user) ? '' : 'required' }}>
                        </x-ui.field>
                    </div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Assign roles" icon="shield-check" flush>
                <div class="panel-body">
                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-4">
                        @forelse ($roles as $role)
                            <label class="option-row">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="checkbox" @checked(isset($user) && $user->roles->contains($role->id))>
                                <span>
                                    <span class="option-row-title">{{ $role->name }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-[13px] text-fg-muted">No roles defined yet. Create one from Manage roles.</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Individual permission overrides" icon="lock" flush>
                <div class="panel-body" id="permissions_list">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($permissions as $groupName => $groupPermissions)
                            <div class="rounded-lg border border-line bg-subtle p-4">
                                <h4 class="mb-3 border-b border-line pb-2 text-[13px] font-semibold text-fg">{{ $groupName }}</h4>
                                <div class="space-y-2">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="option-row">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="checkbox" @checked(isset($user) && $user->permissions->contains($permission->id))>
                                            <span class="option-row-title">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="panel-footer">
                    @isset($user)
                        <a href="{{ route('admin.user_manager') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($user) ? 'save' : 'plus' }}" />{{ isset($user) ? 'Update user' : 'Save user' }}
                    </button>
                </div>
            </x-ui.panel>
        </div>
    </form>
@endsection
