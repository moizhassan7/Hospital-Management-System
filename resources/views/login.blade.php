<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ get_setting('hospital_name', config('app.name', 'Hospital')) }} - Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center font-sans">
    <div class="flex w-full min-h-screen bg-white">
        <!-- Left Side: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 sm:p-12 lg:p-16 xl:p-24 bg-white relative">
            
            <div class="w-full max-w-md mx-auto">
                <!-- Logo area -->
                <div class="mb-12 flex justify-center lg:justify-start">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-blue-700 flex items-center gap-3">
                        @if(get_setting('logo'))
                            <img src="{{ asset('storage/' . get_setting('logo')) }}" alt="Logo" class="h-10 sm:h-12 w-auto object-contain">
                        @else
                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        @endif
                        <span class="tracking-tight uppercase">{{ get_setting('hospital_name', config('app.name', 'Hospital')) }}</span>
                    </h1>
                </div>

                <!-- Headings -->
                <div class="mb-8 text-center lg:text-left">
                    <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4 tracking-tight">Get Started Now</h2>
                    <p class="text-gray-500 text-lg leading-relaxed">Please enter your information to access your account.</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Form Card -->
                <form action="{{ route('login') }}" method="POST" class="bg-white p-8 sm:p-10 rounded-3xl shadow-[0_0_50px_rgba(0,0,0,0.05)] border border-gray-100">
                    @csrf
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                        <input type="email" id="email" name="email" class="w-full px-5 py-4 rounded-xl bg-white border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700" placeholder="user@example.com" value="{{ old('email') }}" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" id="password" name="password" class="w-full px-5 py-4 rounded-xl bg-white border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700" placeholder="••••••••" required>
                    </div>

                    <div class="flex items-center justify-end mb-8">
                        <a href="#" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">
                            Forgot Password?
                        </a>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg shadow-blue-600/30 transition-all duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-blue-500/50 active:scale-[0.98] text-lg">
                        Sign In
                    </button>
                    
                    <div class="mt-8 text-center text-sm text-gray-500 font-medium">
                        Don't have an account? <a href="#" class="font-bold text-blue-600 hover:text-blue-800 transition-colors">Register</a>
                    </div>
                </form>

                <!-- Footer area -->
                <div class="text-center text-sm text-gray-500 mt-12 pt-8 border-t border-gray-100">
                    <p class="font-bold text-gray-700 mb-1">Powered by Switch2itech</p>
                    <p>For inquiries and information, kindly email at:<br>
                    <a href="mailto:support@switch2itech.com" class="text-blue-600 hover:underline transition-colors">support@switch2itech.com</a></p>
                </div>
            </div>
        </div>

        <!-- Right Side: Image and Text -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-50 to-blue-100 flex-col items-center justify-center p-12 relative overflow-hidden">
            <!-- Background Decorations -->
            <div class="absolute top-0 right-0 -mt-32 -mr-32 w-96 h-96 bg-white rounded-full blur-3xl opacity-60"></div>
            <div class="absolute bottom-0 left-0 -mb-32 -ml-32 w-96 h-96 bg-blue-200 rounded-full blur-3xl opacity-40"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full h-full bg-blue-600/5 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 max-w-xl w-full">
                <div class="mb-10 text-left">
                    <h2 class="text-4xl font-extrabold text-blue-900 mb-4 tracking-tight leading-tight">New to {{ get_setting('hospital_name', config('app.name', 'Hospital')) }}? <br/><span class="text-blue-600">One Window All Services</span></h2>
                    <p class="text-blue-800/80 mb-8 text-lg font-medium">
                        Create your account to experience a seamless healthcare management system. Manage patients, appointments, and hospital operations efficiently.
                    </p>
                    
                    <div class="flex items-center gap-6 justify-start mb-12">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg mb-2 shadow-lg shadow-blue-600/30">1</div>
                            <span class="text-xs font-bold text-blue-900 text-center uppercase tracking-wider">Register<br/>Account</span>
                        </div>
                        <div class="w-8 h-0.5 bg-blue-200"></div>
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg mb-2 border-2 border-blue-200">2</div>
                            <span class="text-xs font-bold text-blue-900 text-center uppercase tracking-wider">Secure<br/>Sign-In</span>
                        </div>
                        <div class="w-8 h-0.5 bg-blue-200"></div>
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg mb-2 border-2 border-blue-200">3</div>
                            <span class="text-xs font-bold text-blue-900 text-center uppercase tracking-wider">Access<br/>Services</span>
                        </div>
                    </div>
                </div>

                <!-- Generated Image -->
                <div class="relative rounded-3xl overflow-hidden shadow-2xl shadow-blue-900/20 transform hover:-translate-y-2 transition-transform duration-500 border-4 border-white/50 backdrop-blur-sm">
                    <img src="{{ asset('images/login_illustration.jpg') }}" alt="Hospital Management Dashboard" class="w-full h-auto object-cover aspect-[4/3]">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-900/40 to-transparent"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>