<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hospital</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-100 min-h-screen flex">

    <aside class="w-64 bg-gradient-to-br from-blue-700 to-blue-900 text-white shadow-lg flex flex-col rounded-r-xl">
        <div class="p-6 border-b border-blue-800 flex items-center justify-center">
            <h1 class="text-2xl font-bold tracking-wide">KHAZIR HOSPITAL</h1>
        </div>
        <nav class="flex-grow p-4 overflow-y-auto">
            <ul>
                @if(Auth::user()->hasPermission('View Dashboard'))
                <li class="mb-2">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Dashboard</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Departments'))
                <li class="mb-2">
                    <a href="{{ route('departments.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Departments</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Patients'))
                <li class="mb-2">
                    <a href="{{ route('patients.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M12 20.052v-8.3M15 7.052h2.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H12m-3-10V4.5a1.5 1.5 0 011.5-1.5h3.5a1.5 1.5 0 011.5 1.5V7">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Patients</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Emergency'))
                 <li class="mb-2">
                    <a href="{{ route('emergency.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Emergency</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Doctors'))
                <li class="mb-2">
                    <a href="{{ route('doctors.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Doctors</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Doctor Portal'))
                <li class="mb-2">
                    <a href="{{ route('doctors.dashboard') }}"
                        class="flex items-center p-3 rounded-lg bg-blue-800 hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                        <span class="text-lg font-bold text-emerald-400">Doctor Portal</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Procedures'))
                <li class="mb-2">
                    <a href="{{ route('day-care.create') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <span class="text-lg font-medium">Day Care</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="{{ route('procedures.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Procedures</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Laboratory'))
                <li class="mb-2">
                    <a href="{{ route('laboratory.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Laboratory</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Store'))
                <li class="mb-2">
                    <a href="{{ route('store.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Store</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Reports'))
                <li class="mb-2">
                    <a href="{{ route('index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m32 2v-2a4 4 0 00-4-4h-5a4 4 0 00-4 4v2m-24-5a4 4 0 11-8 0 4 4 0 018 0zm24 0a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Reports</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('View Users'))
                <li class="mb-2">
                    <a href="{{ route('admin.user_manager') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Users</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->hasPermission('Manage Roles'))
                <li class="mb-2">
                    <a href="{{ route('admin.roles.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                            </path>
                        </svg>
                        <span class="text-lg font-medium">Roles</span>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        <div class="p-4 border-t border-blue-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out w-full text-left">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    <span class="text-lg font-medium">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow-sm py-4 px-6 flex items-center justify-between rounded-bl-xl">
            <h2 class="text-2xl font-semibold text-gray-800">Dashboard Overview</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, {{ Auth::user()->name }}!</span>
                <div
                    class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center text-blue-800 font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>

</html>