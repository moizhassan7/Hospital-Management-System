@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">{{ isset($user) ? 'Edit User' : 'User Manager' }}</h2>
        <a href="{{ route('dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Please fix the following errors:</span>
            <ul class="mt-3 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="user_manager_form" action="{{ isset($user) ? route('admin.user_manager.update', $user->id) : route('admin.user_manager.store') }}" method="POST">
            @csrf
            @isset($user)
                @method('PUT')
                <input type="hidden" name="user_id" value="{{ $user->id }}">
            @endisset

            <div class="flex items-center justify-between mb-4 border-b pb-2">
                <h3 class="text-2xl font-semibold text-gray-800">User Details</h3>
                <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Manage Roles
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">Select User (to Edit):</label>
                    <select id="user_id_select" onchange="window.location.href='/admin/user-manager/' + this.value + '/edit'" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- New User --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (isset($user) && $user->id == $u->id) ? 'selected' : '' }}>
                                {{ $u->username }} - {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name:</label>
                    <input type="text" id="name" name="name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Full Name" value="{{ old('name', $user->name ?? '') }}" required>
                </div>
                <div>
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                    <input type="text" id="username" name="username" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Username" value="{{ old('username', $user->username ?? '') }}" required>
                </div>
                <div>
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" id="email" name="email" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Email" value="{{ old('email', $user->email ?? '') }}">
                </div>
                <div>
                    <label for="branch" class="block text-gray-700 text-sm font-bold mb-2">Branch:</label>
                    <input type="text" id="branch" name="branch" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Branch" value="{{ old('branch', $user->branch ?? '') }}">
                </div>
                <div>
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                    <input type="password" id="password" name="password" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="{{ isset($user) ? 'Set New Password (optional)' : 'Password' }}" {{ isset($user) ? '' : 'required' }}>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Assign Roles</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @foreach ($roles as $role)
                    <label class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                            class="form-checkbox h-5 w-5 text-blue-600 rounded"
                            {{ (isset($user) && $user->roles->contains($role->id)) ? 'checked' : '' }}>
                        <span class="text-gray-800 font-medium">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Individual Permission Overrides</h3>
            <div id="permissions_list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                @foreach ($permissions as $groupName => $groupPermissions)
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <h4 class="text-lg font-bold text-blue-800 mb-3 border-b border-blue-200 pb-1">{{ $groupName }}</h4>
                        <div class="space-y-2">
                            @foreach ($groupPermissions as $permission)
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded"
                                        {{ (isset($user) && $user->permissions->contains($permission->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700 group-hover:text-blue-600 transition-colors">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ isset($user) ? 'Update User' : 'Save User' }}
                </button>
            </div>
        </form>
    </div>
@endsection