@extends('layouts.app')

@section('page_title', 'Roles')

@section('content')
    @include('partials.page-shell-start', ['title' => 'Role Management', 'subtitle' => 'Permissions and access control'])

    <div class="hms-page-toolbar !mt-0 !mb-0">
        <div></div>
        <a href="{{ route('admin.roles.create') }}" class="hms-btn hms-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create role
        </a>
    </div>

    <div class="hms-panel hms-panel-flush">
        <div class="hms-table-wrap">
            <table class="hms-table">
                <thead>
                    <tr>
                        <th>Role name</th>
                        <th>Permissions</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td><span class="font-semibold text-gray-800">{{ $role->name }}</span></td>
                        <td>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($role->permissions as $permission)
                                    <span class="hms-badge hms-badge-blue">{{ $permission->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div class="hms-table-actions">
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="hms-btn hms-btn-icon hms-btn-icon-edit" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M18.364 5.636a2 2 0 112.828 2.828l-7.071 7.071-4.242 1.414 1.414-4.242 7.071-7.071z"/></svg>
                                </a>
                                @if($role->name !== 'Super Admin')
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="hms-btn hms-btn-icon hms-btn-icon-delete" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 border-t border-gray-100">
            {{ $roles->onEachSide(1)->links() }}
        </div>
    </div>
@endsection
