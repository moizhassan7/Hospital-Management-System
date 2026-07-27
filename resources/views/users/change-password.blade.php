@extends('layouts.app')

@section('page_title', 'Change Password')

@section('content')
    @include('partials.page-shell-start', [
        'title' => 'Change Password',
        'subtitle' => 'Update your account login password',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Dashboard',
    ])

    @if (session('success'))
        <div class="hms-alert hms-alert-success mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="hms-alert hms-alert-error mb-4" role="alert">
            <strong class="font-bold">Please fix the following errors:</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Account Info Summary Card -->
        <div class="hms-panel">
            <div class="hms-panel-header border-b border-gray-100 mb-0">
                <div>
                    <h3 class="hms-panel-title">Logged-in Account Details</h3>
                    <p class="hms-panel-subtitle">Your password will be updated for this account</p>
                </div>
            </div>
            <div class="hms-panel-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 block text-xs uppercase tracking-wider font-semibold mb-1">Full Name</span>
                        <span class="font-semibold text-gray-900 text-base">{{ $user->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase tracking-wider font-semibold mb-1">Username</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                            {{ $user->username }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase tracking-wider font-semibold mb-1">Scope / Role</span>
                        <span class="text-gray-800 font-medium">
                            {{ $user->isSuperAdmin() ? 'Super Administrator' : ($user->user_scope ? ucwords(str_replace('_', ' ', $user->user_scope)) : 'Lab Staff') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase tracking-wider font-semibold mb-1">Center / Branch</span>
                        <span class="text-gray-800 font-medium">
                            {{ $user->collectionCenter?->name ?? ($user->branch ?: 'Main Lab') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Update Form -->
        <div class="hms-panel">
            <form action="{{ route('user.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="hms-panel-header border-b border-gray-100 mb-0">
                    <div>
                        <h3 class="hms-panel-title">Update Password</h3>
                        <p class="hms-panel-subtitle">Enter your current password and choose a new password</p>
                    </div>
                </div>

                <div class="hms-panel-body space-y-5">
                    <x-form.field label="Current Password" for="old_password" :required="true">
                        <input type="password" id="old_password" name="old_password" class="hms-input" required autocomplete="current-password" placeholder="Enter your current password">
                    </x-form.field>

                    <x-form.field label="New Password" for="new_password" :required="true" hint="Minimum 6 characters">
                        <input type="password" id="new_password" name="new_password" class="hms-input" required minlength="6" autocomplete="new-password" placeholder="Enter new password">
                    </x-form.field>

                    <x-form.field label="Confirm New Password" for="new_password_confirmation" :required="true" hint="Re-enter your new password">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="hms-input" required minlength="6" autocomplete="new-password" placeholder="Re-enter new password">
                    </x-form.field>

                    <div class="hms-form-actions pt-2">
                        <button type="submit" class="hms-btn hms-btn-primary">
                            Update Password
                        </button>
                        <a href="{{ route('pathology.index') }}" class="hms-btn hms-btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
