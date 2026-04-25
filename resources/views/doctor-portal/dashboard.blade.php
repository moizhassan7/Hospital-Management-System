@extends('layouts.app')

@section('content')
<div class="py-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Welcome Section -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Welcome back, Dr. {{ Auth::user()->name }}!</h1>
                <p class="text-gray-600 text-lg">Manage your clinic, patients, and prescriptions with ease.</p>
            </div>
            <div class="mt-6 md:mt-0 flex space-x-4">
                <div class="bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                    <span class="block text-2xl font-bold text-blue-600">Prescribe</span>
                    <span class="text-xs text-blue-400 uppercase tracking-wider font-semibold">Active Mode</span>
                </div>
            </div>
        </div>

        <!-- Primary Actions Grid -->
        <div class="mb-12">
            <h3 class="text-lg font-bold text-gray-700 uppercase tracking-wider mb-6 ml-1">Quick Clinical Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Write Prescription -->
                <a href="{{ route('doctors.write-prescription') }}" class="group relative overflow-hidden bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:-translate-y-2">
                    <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10">
                        <div class="bg-blue-600 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-200">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h4 class="text-2xl font-bold text-gray-900 mb-2">Write Prescription</h4>
                        <p class="text-gray-500">Generate and print new patient prescriptions quickly.</p>
                    </div>
                </a>

                <!-- Register Patient -->
                <a href="{{ route('doctors.register-patient') }}" class="group relative overflow-hidden bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:-translate-y-2">
                    <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 bg-green-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10">
                        <div class="bg-green-600 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-green-200">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <h4 class="text-2xl font-bold text-gray-900 mb-2">Register Patient</h4>
                        <p class="text-gray-500">Add new patients to your medical database.</p>
                    </div>
                </a>

                <!-- View Reports -->
                <a href="{{ route('doctors.reports') }}" class="group relative overflow-hidden bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:-translate-y-2">
                    <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 bg-red-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10">
                        <div class="bg-red-500 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-red-200">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h4 class="text-2xl font-bold text-gray-900 mb-2">View Analytics</h4>
                        <p class="text-gray-500">Track clinic performance and patient visit trends.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Pending Patients / Drafts Section -->
        @if($drafts->count() > 0)
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6 ml-1">
                <h3 class="text-lg font-bold text-gray-700 uppercase tracking-wider">Pending Patients</h3>
                <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-xs font-black uppercase">{{ $drafts->count() }} Waiting</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($drafts as $draft)
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-amber-100 hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-amber-50 text-amber-600 p-3 rounded-2xl group-hover:bg-amber-500 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $draft->updated_at->diffForHumans() }}</span>
                    </div>
                    <h4 class="text-xl font-black text-slate-900 mb-1">{{ $draft->patient->name }}</h4>
                    <p class="text-sm font-bold text-slate-500 mb-6">MR# {{ $draft->patient->mr_number }}</p>
                    <a href="{{ route('doctors.write-prescription', ['prescription_id' => $draft->id]) }}" class="w-full bg-slate-900 text-white text-center py-3 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-blue-600 transition-all inline-block">
                        Continue Rx
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
