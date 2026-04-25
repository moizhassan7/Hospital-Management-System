@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">{{ isset($role) ? 'Edit Role' : 'Create New Role' }}</h2>
        <a href="{{ route('admin.roles.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Roles
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            @if(isset($role))
                <input type="hidden" name="role_id" value="{{ $role->id }}">
            @endif

            <div class="mb-6">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Role Name:</label>
                <input type="text" id="name" name="name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Role Name" value="{{ old('name', $role->name ?? '') }}" required {{ (isset($role) && $role->name === 'Super Admin') ? 'readonly' : '' }}>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Assign Permissions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-6">
                @foreach ($permissions as $groupName => $groupPermissions)
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <h4 class="text-lg font-bold text-blue-800 mb-3 border-b border-blue-200 pb-1">{{ $groupName }}</h4>
                        <div class="space-y-2">
                            @foreach ($groupPermissions as $permission)
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition duration-150 ease-in-out"
                                        {{ (isset($role) && $role->permissions->contains($permission->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700 group-hover:text-blue-600 transition-colors">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-8">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ isset($role) ? 'Update Role' : 'Create Role' }}
                </button>
            </div>
        </form>
    </div>
@endsection
