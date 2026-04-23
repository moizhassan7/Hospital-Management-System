@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Doctors Management</h2>
        <a href="{{ route('dashboard') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Main Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        <!-- Card: Total Doctors -->
        <div
            class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300 hidden">
            <div class="text-blue-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Total Doctors</h3>
            <p class="text-5xl font-bold text-blue-800">120</p>
            <p class="text-gray-600 text-center text-sm mt-2">All registered medical staff</p>
        </div>

        <!-- Card: Doctors on Duty Today -->
        <div
            class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300 hidden">
            <div class="text-green-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Doctors on Duty Today</h3>
            <p class="text-5xl font-bold text-green-800">75</p>
            <p class="text-gray-600 text-center text-sm mt-2">Currently available doctors</p>
        </div>

        <!-- Card: Add New Doctor -->
        <div
            class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-purple-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Add New Doctor</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Register a new medical professional.</p>
            <a href="{{ route('doctors.create') }}"
                class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Add
                Doctor</a>
        </div>

        <!-- Card: View All Doctors -->
        <div
            class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-yellow-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">View All Doctors</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Browse and manage all registered doctors.</p>
            <a href="{{ route('doctors.all') }}"
                class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">View
                Doctors</a>
        </div>
    </div>

    <hr class="my-8 border-gray-300">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 hidden">
        <!-- Card: Doctors by Speciality -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Doctors by Speciality</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Speciality</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Cardiology</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">15</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Pediatrics</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">12</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Neurology</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">10</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Orthopedics</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">13</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">General Surgery</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">18</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card: Doctor Shift Overview -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Doctor Shift Overview</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Doctors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Morning Shift</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">30</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Evening Shift</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">25</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Night Shift</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">20</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
