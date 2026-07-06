<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('hospital.name') }} - Login</title>
    <link rel="icon" type="image/png" href="{{ asset(config('hospital.logo')) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans">
    <div class="hms-login-shell">
        <div class="hms-login-brand">
            @include('partials.hospital-brand', ['variant' => 'login-hero'])
            <p class="mt-6 text-blue-100 text-center max-w-sm text-lg">{{ config('hospital.tagline', 'Hospital Management System') }}</p>
        </div>

        <div class="hms-login-form-side">
            <div class="hms-login-card">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Sign in</h2>
                <p class="text-gray-500 text-sm mb-8">Enter your credentials to access the system.</p>

                @if ($errors->any())
                    <div class="hms-alert hms-alert-error" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="ml-1">{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="login" class="block text-gray-700 text-sm font-semibold mb-2">Username or email</label>
                        <input type="text" id="login" name="login" class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="admin or user@example.com" value="{{ old('login') }}" required autofocus>
                    </div>
                    <div>
                        <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                        <input type="password" id="password" name="password" class="shadow-sm border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="********" required>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-colors w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
