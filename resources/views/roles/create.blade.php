@extends('layouts.app')

@section('title', isset($role) ? 'Edit role' : 'Create new role')

@section('content')
    <x-ui.page-header :title="isset($role) ? 'Edit role' : 'Create new role'" :subtitle="isset($role) ? $role->name : 'Name the role and choose the permissions it grants.'">
        <x-slot:actions>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Roles
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        @if(isset($role))
            <input type="hidden" name="role_id" value="{{ $role->id }}">
        @endif

        <div class="stack">
            <x-ui.panel title="Role" icon="shield-check" flush>
                <div class="panel-body">
                    <x-ui.field label="Role name" for="name" error="name" required>
                        <input type="text" id="name" name="name" @class(['input max-w-md', 'is-invalid' => $errors->has('name')]) placeholder="Role name" value="{{ old('name', $role->name ?? '') }}" required {{ (isset($role) && $role->name === 'Super Admin') ? 'readonly' : '' }}>
                    </x-ui.field>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Assign permissions" icon="lock" flush>
                <div class="panel-body">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($permissions as $groupName => $groupPermissions)
                            <div class="rounded-lg border border-line bg-subtle p-4">
                                <h4 class="mb-3 border-b border-line pb-2 text-[13px] font-semibold text-fg">{{ $groupName }}</h4>
                                <div class="space-y-2">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="option-row">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="checkbox" @checked(isset($role) && $role->permissions->contains($permission->id))>
                                            <span class="option-row-title">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="panel-footer">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($role) ? 'save' : 'plus' }}" />{{ isset($role) ? 'Update role' : 'Create role' }}
                    </button>
                </div>
            </x-ui.panel>
        </div>
    </form>
@endsection
