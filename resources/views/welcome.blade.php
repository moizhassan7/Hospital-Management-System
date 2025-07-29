<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-br from-blue-700 to-blue-900 text-white shadow-lg flex flex-col rounded-r-xl">
        <div class="p-6 border-b border-blue-800 flex items-center justify-center">
            <h1 class="text-3xl font-bold tracking-wide">HMS</h1>
        </div>
        <nav class="flex-grow p-4">
            <ul>
                <li class="mb-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="text-lg font-medium">Dashboard</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="{{ route('departments.index') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="text-lg font-medium">Departments</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M12 20.052v-8.3M15 7.052h2.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H12m-3-10V4.5a1.5 1.5 0 011.5-1.5h3.5a1.5 1.5 0 011.5 1.5V7"></path></svg>
                        <span class="text-lg font-medium">Patients</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span class="text-lg font-medium">Doctors</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-lg font-medium">Appointments</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="text-lg font-medium">Billing</span>
                    </a>
                </li>
                <!-- Add more menu items as needed -->
            </ul>
        </nav>
        <div class="p-4 border-t border-blue-800">
            <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 ease-in-out">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="text-lg font-medium">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white shadow-sm py-4 px-6 flex items-center justify-between rounded-bl-xl">
            <h2 class="text-2xl font-semibold text-gray-800">Dashboard Overview</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, Admin!</span>
                <div class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center text-blue-800 font-bold">A</div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
