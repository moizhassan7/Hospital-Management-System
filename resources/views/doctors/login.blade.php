@extends('layouts.app')

@section('content')
    <div class="hms-login-form-side min-h-[calc(100vh-4rem)]">
        <div class="hms-login-card">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Doctor sign in</h2>
            <p class="text-gray-500 text-sm mb-8">Access the doctor portal dashboard.</p>

            @include('partials.flash-alerts')

            <form action="{{ route('doctors.login') }}" method="POST" class="space-y-5">
                @csrf
                <x-form.field label="Email address" for="email" :required="true">
                    <input type="email" id="email" name="email" class="hms-input" placeholder="doctor@example.com" value="{{ old('email') }}" required>
                </x-form.field>
                <x-form.field label="Password" for="password" :required="true">
                    <input type="password" id="password" name="password" class="hms-input" placeholder="********" required>
                </x-form.field>
                <button type="submit" class="hms-btn hms-btn-primary w-full hms-btn-lg">Login</button>
            </form>
        </div>
    </div>
@endsection
