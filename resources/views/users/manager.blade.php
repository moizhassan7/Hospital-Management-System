@extends('layouts.app')

@section('page_title', isset($user) ? 'Edit User' : 'User Management')

@section('content')
    @include('partials.page-shell-start', [
        'title' => isset($user) ? 'Edit user' : 'User management',
        'subtitle' => 'Create logins and control module access per user',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back',
    ])

    @if($errors->any())
        <div class="hms-alert hms-alert-error mb-4" role="alert">
            <strong class="font-bold">Please fix these errors:</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="hms-panel hms-panel-flush mb-5">
        <div class="hms-panel-header">
            <h3 class="hms-panel-title">All users ({{ $users->count() }})</h3>
            <a href="{{ route('admin.user_manager') }}" class="hms-btn hms-btn-primary hms-btn-sm">+ New user</a>
        </div>
        <div class="hms-table-wrap">
            <table class="hms-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Roles</th>
                        <th>Module access</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        @php $access = $u->allPermissionNames(); @endphp
                        <tr>
                            <td class="font-medium text-gray-900">{{ $u->name }}</td>
                            <td>{{ $u->username }}</td>
                            <td>{{ $u->roles->pluck('name')->join(', ') ?: '—' }}</td>
                            <td>
                                <span class="text-xs text-gray-600">{{ count($access) }} permission(s)</span>
                            </td>
                            <td class="hms-table-actions">
                                <a href="{{ route('admin.user_manager.edit', $u) }}" class="hms-btn hms-btn-secondary hms-btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="hms-table-empty"><td colspan="5">No users yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="hms-panel mb-5">
        <form action="{{ isset($user) ? route('admin.user_manager.update', $user->id) : route('admin.user_manager.store') }}" method="POST">
            @csrf
            @isset($user)
                @method('PUT')
                <input type="hidden" name="user_id" value="{{ $user->id }}">
            @endisset

            <div class="hms-panel-header border-b border-gray-100 mb-0">
                <div>
                    <h3 class="hms-panel-title">{{ isset($user) ? 'Edit user' : 'Create new login' }}</h3>
                    <p class="hms-panel-subtitle">Username + password for sign in. Email is optional.</p>
                </div>
                <a href="{{ route('admin.roles.index') }}" class="hms-btn hms-btn-secondary hms-btn-sm">Manage roles</a>
            </div>

            <div class="hms-panel-body space-y-6">
                <div class="hms-form-grid">
                    <x-form.field label="Select user to edit" for="user_id_select">
                        <select id="user_id_select" onchange="if(this.value){window.location.href='/admin/user-manager/'+this.value+'/edit'}" class="hms-select">
                            <option value="">— Create new user —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (isset($user) && $user->id == $u->id) ? 'selected' : '' }}>
                                    {{ $u->username }} — {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-form.field>
                    <x-form.field label="Full name" for="name" :required="true">
                        <input type="text" id="name" name="name" class="hms-input" value="{{ old('name', $user->name ?? '') }}" required>
                    </x-form.field>
                    <x-form.field label="Username (for login)" for="username" :required="true">
                        <input type="text" id="username" name="username" class="hms-input" value="{{ old('username', $user->username ?? '') }}" required>
                    </x-form.field>
                    <x-form.field label="Email (optional)" for="email">
                        <input type="email" id="email" name="email" class="hms-input" value="{{ old('email', $user->email ?? '') }}">
                    </x-form.field>
                    <x-form.field label="Branch" for="branch">
                        <input type="text" id="branch" name="branch" class="hms-input" value="{{ old('branch', $user->branch ?? '') }}">
                    </x-form.field>
                    <x-form.field label="Password" for="password" :hint="isset($user) ? 'Leave blank to keep current password' : 'Minimum 6 characters'">
                        <input type="password" id="password" name="password" class="hms-input" {{ isset($user) ? '' : 'required' }}>
                    </x-form.field>
                </div>

                <div>
                    <h4 class="hms-section-title">Assign roles</h4>
                    <p class="text-sm text-gray-500 mb-3">Roles bundle module permissions. Pick one or more roles for this user.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($roles as $role)
                            <label class="hms-checkbox-row bg-gray-50 border border-gray-200 rounded-xl p-3">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="hms-checkbox"
                                    {{ (isset($user) && $user->roles->contains($role->id)) || in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                <span>
                                    <span class="hms-checkbox-label block">{{ $role->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $role->permissions->count() }} permissions</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if(isset($user) && !empty($effectivePermissions))
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Effective module access (from roles + overrides)</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($effectivePermissions as $perm)
                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-white border border-blue-200 text-xs text-blue-800">{{ $perm }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <h4 class="hms-section-title">Lab feature access</h4>
                    <p class="text-sm text-gray-500 mb-3">Tick which lab screens this user can open. Super Admin has full access automatically via role.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($permissions as $groupName => $groupPermissions)
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                                <h5 class="font-bold text-blue-800 mb-3 pb-2 border-b border-blue-100">{{ $groupName }}</h5>
                                <div class="space-y-2">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="hms-checkbox-row">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="hms-checkbox"
                                                {{ (isset($user) && $user->permissions->contains($permission->id)) || in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                            <span class="hms-checkbox-label">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="hms-form-actions">
                    <button type="submit" class="hms-btn hms-btn-primary">
                        {{ isset($user) ? 'Update user' : 'Create user' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
