@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <x-ui.page-header title="Roles" subtitle="Named sets of permissions assigned to staff accounts.">
        <x-slot:actions>
            <a href="{{ route('admin.user_manager') }}" class="btn btn-ghost">
                <x-ui.icon name="user-cog" />Users
            </a>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" />Create new role
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel title="Role list" flush>
        <x-slot:actions>
            <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#roles-table">{{ count($roles) }}</span> total</span>
        </x-slot:actions>
        <div class="table-toolbar">
            <x-ui.search-input name="" class="toolbar-search" placeholder="Filter roles" data-table-filter="#roles-table" />
        </div>
        <div class="table-wrap">
            <table id="roles-table" class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th class="actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="cell-strong">{{ $role->name }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($role->permissions as $permission)
                                        <x-ui.badge tone="info">{{ $permission->name }}</x-ui.badge>
                                    @empty
                                        <span class="muted">No permissions</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                    @if($role->name !== 'Super Admin')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline"
                                            data-confirm="Staff with this role keep their accounts but lose these permissions until another role is assigned."
                                            data-confirm-title="Delete this role?" data-confirm-label="Delete role">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="3" class="p-0">
                                <x-ui.empty-state icon="shield-check" title="No roles yet" description="Create a role and assign permissions to it." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
@endsection
