@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    /* Modern TomSelect Styles */
    .modern-tom-select + .ts-wrapper .ts-control {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 0.75rem 1rem;
        background-color: #f8fafc;
        box-shadow: none;
        transition: all 0.2s ease;
        font-family: inherit;
        font-weight: 700;
        color: #0f172a;
    }

    .modern-tom-select + .ts-wrapper.focus .ts-control {
        border-color: #3b82f6;
        ring: 4px solid #eff6ff;
        background-color: #ffffff;
    }

    .modern-tom-select + .ts-wrapper .ts-dropdown {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        margin-top: 5px;
        overflow: hidden;
        padding: 0.5rem;
    }

    .modern-tom-select + .ts-wrapper .ts-dropdown .option {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        font-weight: 600;
    }

    .modern-tom-select + .ts-wrapper .ts-dropdown .active {
        background-color: #eff6ff;
        color: #1d4ed8;
    }

    /* Dark Section TomSelect (for Abstains) */
    .bg-slate-900 .modern-tom-select + .ts-wrapper .ts-control {
        background-color: #1e293b;
        border-color: #334155;
        color: #ffffff;
    }

    .bg-slate-900 .modern-tom-select + .ts-wrapper.focus .ts-control {
        border-color: #fbbf24;
        background-color: #0f172a;
    }

    /* Dark Section Dropdown (via specific class) */
    .ts-dropdown.dark-theme-dropdown {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #ffffff !important;
        border-radius: 1rem !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
        z-index: 9999 !important;
        max-height: 300px !important;
        overflow-y: auto !important;
    }

    .ts-dropdown.dark-theme-dropdown .option {
        color: #cbd5e1 !important;
        padding: 0.75rem 1rem !important;
        border-radius: 0.75rem !important;
    }

    .ts-dropdown.dark-theme-dropdown .active {
        background-color: #334155 !important;
        color: #fbbf24 !important;
    }

    .ts-dropdown.dark-theme-dropdown .optgroup-header {
        background-color: #0f172a !important;
        color: #94a3b8 !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        font-size: 10px !important;
    }

    .modern-tom-select + .ts-wrapper .item {
        background: #3b82f6 !important;
        color: white !important;
        border-radius: 6px !important;
        padding: 2px 8px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
    }

    .bg-slate-900 .modern-tom-select + .ts-wrapper .item {
        background: #fbbf24 !important;
        color: #0f172a !important;
    }

    .modern-tom-select + .ts-wrapper .ts-control input::placeholder {
        color: #94a3b8;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="py-6 bg-slate-50 min-h-screen">
    <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="font-black text-3xl text-slate-800 leading-tight tracking-tight">
                    New <span class="text-blue-600">Prescription</span>
                </h2>
                <p class="text-slate-500 font-medium mt-1">Create a comprehensive medical record for your patient.</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" id="drafts_list_btn"
                    class="group flex items-center bg-blue-600 text-white font-bold py-2.5 px-5 rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pending Patients
                    <span
                        class="ml-2 bg-white text-blue-600 px-2 py-0.5 rounded-lg text-xs font-black">{{ $drafts->count() }}</span>
                </button>
                <a href="{{ route('doctors.dashboard') }}"
                    class="group flex items-center bg-white border border-slate-200 text-slate-700 font-bold py-2.5 px-5 rounded-2xl shadow-sm hover:shadow-md hover:bg-slate-50 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2 text-slate-400 group-hover:-translate-x-1 transition-transform duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>

        <form id="prescription_form" action="{{ route('doctors.store-prescription') }}" method="POST">
            @csrf

            @if(session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-8 rounded-r-2xl shadow-sm animate-bounce"
                    role="alert">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <p class="font-bold">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Sidebar: Patient Info & Vitals -->
                <div class="lg:col-span-3 space-y-8">

                    <!-- Patient Profile Card -->
                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">Patient Profile</h3>
                            <button type="button" id="patient_search_btn"
                                class="bg-blue-50 text-blue-600 p-2 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                class="bg-slate-50 p-4 rounded-2xl border border-slate-100 group focus-within:ring-4 focus-within:ring-blue-50 transition-all">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Patient
                                    Search</label>
                                <input type="text" id="patient_quick_search"
                                    class="w-full bg-transparent border-none p-0 text-slate-900 font-black placeholder-slate-300 focus:ring-0"
                                    placeholder="Name, Phone or MR#" value="{{ $patient->mr_number ?? '' }}" {{ isset($patient) ? 'readonly' : '' }}>
                                <input type="hidden" name="mr_number" id="mr_number_hidden"
                                    value="{{ $patient->mr_number ?? '' }}">
                            </div>

                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Full
                                    Name</label>
                                <input type="text" id="patient_name" name="patient_name"
                                    class="w-full bg-white border-slate-100 rounded-xl py-2 px-3 text-slate-900 font-bold focus:ring-2 focus:ring-blue-100"
                                    value="{{ $patient->name ?? '' }}" readonly>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Age</label>
                                    <input type="text" id="age" name="age"
                                        class="w-full bg-white border-slate-100 rounded-xl py-2 px-3 text-slate-900 font-bold"
                                        value="{{ $patient->age ?? '' }}" readonly>
                                </div>
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Gender</label>
                                    <input type="text" id="sex" name="sex"
                                        class="w-full bg-white border-slate-100 rounded-xl py-2 px-3 text-slate-900 font-bold"
                                        value="{{ $patient->gender ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vitals Card -->
                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-black text-slate-800 tracking-tight mb-6">Patient Vitals</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">B.P.
                                    (mmHg)</label>
                                <input type="text" id="bp" name="bp"
                                    class="w-full bg-slate-50 border-slate-100 rounded-xl py-3 px-4 text-slate-900 font-black focus:ring-4 focus:ring-blue-50/50"
                                    placeholder="120/80">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Pulse</label>
                                <input type="number" id="pulse" name="pulse"
                                    class="w-full bg-slate-50 border-slate-100 rounded-xl py-3 px-4 text-slate-900 font-black"
                                    placeholder="72">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Temp
                                    °F</label>
                                <input type="number" id="temperature" name="temperature"
                                    class="w-full bg-slate-50 border-slate-100 rounded-xl py-3 px-4 text-slate-900 font-black"
                                    placeholder="98.6" step="0.1">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Oxygen
                                    %</label>
                                <input type="number" id="oxygen" name="oxygen"
                                    class="w-full bg-slate-50 border-slate-100 rounded-xl py-3 px-4 text-slate-900 font-black"
                                    placeholder="98">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Weight
                                    (kg)</label>
                                <input type="number" id="weight" name="weight"
                                    class="w-full bg-slate-50 border-slate-100 rounded-xl py-3 px-4 text-slate-900 font-black"
                                    placeholder="70">
                            </div>
                        </div>
                    </div>

                    <!-- Visit History Timeline -->
                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-black text-slate-800 tracking-tight mb-6">Recent History</h3>
                        <div id="history_list"
                            class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            @if($patient)
                                @forelse ($patient->prescriptions->where('is_draft', false) as $visit)
                                    <div class="relative pl-6 pb-2 border-l-2 border-slate-100 last:border-0 view-history-btn cursor-pointer group"
                                        data-id="{{ $visit->id }}">
                                        <div
                                            class="absolute -left-[9px] top-0 w-4 h-4 bg-white border-2 border-blue-500 rounded-full group-hover:bg-blue-500 transition-colors">
                                        </div>
                                        <p class="text-xs font-black text-blue-600 leading-none mb-1">
                                            {{ $visit->created_at->format('d M, Y') }}
                                        </p>
                                        <p class="text-sm font-bold text-slate-700 truncate">{{ $visit->complaints }}</p>
                                    </div>
                                @empty
                                    <p class="text-slate-400 text-sm font-bold italic">No previous visits.</p>
                                @endforelse
                            @else
                                <p class="text-slate-400 text-sm font-bold italic text-center py-4">Search patient to
                                    load history.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Area: Complaints, Rx, & Advice -->
                <div class="lg:col-span-9 space-y-8">

                    <!-- Top Content: Complaints & Diagnoses -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Complaints Card -->
                        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                            <div class="flex items-center mb-4">
                                <div class="w-1.5 h-6 bg-red-500 rounded-full mr-3"></div>
                                <h3 class="text-xl font-black text-slate-800 tracking-tight">Chief Complaints</h3>
                            </div>
                            <textarea id="complaints" name="complaints" rows="4"
                                class="w-full bg-slate-50 border-slate-100 rounded-[1.5rem] p-4 text-slate-900 font-medium focus:ring-4 focus:ring-red-50"
                                placeholder="Patient's primary concerns..."></textarea>
                        </div>

                        <!-- Diagnoses & Reports Card -->
                        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-1.5 h-6 bg-amber-500 rounded-full mr-3"></div>
                                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Clinical Assessment
                                    </h3>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" id="add_diagnoses_btn"
                                        class="bg-amber-50 text-amber-600 p-2 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm"
                                        title="Add Diagnosis">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                    <button type="button" id="add_reports_btn"
                                        class="bg-indigo-50 text-indigo-600 p-2 rounded-xl hover:bg-indigo-500 hover:text-white transition-all shadow-sm"
                                        title="Add Report">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 17v-2m3 2v-4m3 2v-6m-8 2.25V17.25c0 .414.336.75.75.75h14.5a.75.75 0 00.75-.75V5.25a.75.75 0 00-.75-.75H4.25a.75.75 0 00-.75.75v12.75c0 .414.336.75.75.75H9z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Diagnoses</label>
                                    <select id="diagnoses_select" multiple name="diagnoses[]"
                                        class="modern-tom-select"></select>
                                </div>
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Recommended
                                        Reports</label>
                                    <select id="reports_select" multiple name="reports[]"
                                        class="modern-tom-select"></select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prescription (Rx) Section -->
                    <div
                        class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 overflow-hidden relative">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50">
                        </div>

                        <div class="flex items-center mb-8 relative z-10">
                            <div class="bg-blue-600 text-white p-3 rounded-2xl mr-4 shadow-lg shadow-blue-200">
                                <span class="text-2xl font-black italic">Rx</span>
                            </div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Prescription Details</h3>
                        </div>

                        <div
                            class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-10 relative z-10 bg-slate-50/50 p-6 rounded-3xl border border-slate-100">
                            <div class="md:col-span-5">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Search
                                    Medicine</label>
                                <div class="flex items-center space-x-2">
                                    <div class="relative w-full">
                                        <input type="text" id="medicine_search"
                                            class="w-full bg-white border-slate-200 rounded-xl py-3 px-4 text-slate-900 font-bold focus:ring-4 focus:ring-blue-100"
                                            placeholder="Paracetamol, Amoxicillin...">
                                        <button type="button" id="search_medicine_btn"
                                            class="absolute right-3 top-3 text-slate-400 hover:text-blue-600 z-20 cursor-pointer">
                                            <svg class="w-5 h-5 pointer-events-none" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="button" id="add_new_medicine_btn"
                                        class="bg-white text-blue-600 p-3 rounded-xl border border-blue-100 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="md:col-span-4">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Dosage
                                    & Frequency</label>
                                <select id="dosage_frequency"
                                    class="w-full bg-white border-slate-200 rounded-xl py-3 px-4 text-slate-900 font-bold focus:ring-4 focus:ring-blue-100">
                                    <option value="">Select Dosage</option>
                                    @foreach($dosagesList as $dosage)
                                        <option value="{{ $dosage->name }}">{{ $dosage->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Duration
                                    (Days)/Units</label>
                                <input type="number" id="duration"
                                    class="w-full bg-white border-slate-200 rounded-xl py-3 px-4 text-slate-900 font-black focus:ring-4 focus:ring-blue-100"
                                    placeholder="7">
                            </div>

                            <div
                                class="md:col-span-12 flex items-center justify-between mt-2 pt-4 border-t border-slate-200/50">
                                <div class="flex items-center space-x-3">
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Group:</label>
                                    <select id="medicine_groups"
                                        class="bg-white border-slate-200 rounded-xl py-2 px-4 text-sm font-bold text-slate-700 min-w-[200px]">
                                        <option value="">Select Group</option>
                                        @foreach($medicineGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="add_medicine_group_btn"
                                        class="text-blue-600 hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-9-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                                <button type="button" id="add_medicine_btn"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-10 rounded-2xl shadow-lg shadow-blue-200 transition-all hover:-translate-y-1 active:translate-y-0">
                                    Add to List
                                </button>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-3xl border border-slate-100 shadow-sm mb-10">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            #</th>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Medicine Name</th>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Dosage Schedule</th>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Duration</th>
                                        <th
                                            class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                                            Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="prescription_table_body" class="divide-y divide-slate-50">
                                    <!-- Dynamic Content -->
                                </tbody>
                            </table>
                            <input type="hidden" name="medicines_data" id="medicines_input">
                        </div>

                        <!-- Advice / Abstain Section -->
                        <div class="bg-slate-900 rounded-3xl p-8 text-white">
                            <div class="flex items-center mb-6">
                                <h4 class="text-xl font-black tracking-tight flex items-center">
                                    <svg class="w-6 h-6 mr-3 text-amber-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                    Dietary Advice & Abstains
                                </h4>
                            </div>

                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Select Advice (Parhaiz)</label>
                                <select id="abstains_select" multiple name="abstains[]" class="modern-tom-select">
                                    @foreach($abstainsList as $group)
                                        <optgroup label="{{ $group->name }}">
                                            @foreach($group->items as $item)
                                                <option value="{{ $item->id }}">{{ $item->item }} {{ $item->duration ? "({$item->duration})" : '' }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Final Section: Notes & Save Actions -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div class="space-y-4">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Doctor's
                                    Private Notes</label>
                                <textarea id="notes" name="notes" rows="3"
                                    class="w-full bg-slate-50 border-slate-100 rounded-2xl p-4 text-slate-900 font-medium focus:ring-4 focus:ring-blue-50"
                                    placeholder="Internal notes or follow-up instructions..."></textarea>
                            </div>
                            <div class="space-y-4">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Schedule
                                    Next Visit</label>
                                <input type="date" id="next_visit_date" name="next_visit_date"
                                    class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-black focus:ring-4 focus:ring-blue-50">
                            </div>
                        </div>

                        <div
                            class="flex flex-wrap items-center justify-end gap-4 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <button type="button" id="save_temp_btn"
                                class="bg-amber-500 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-amber-200 hover:bg-amber-600 transition-all flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Save Draft
                            </button>

                            <button type="button" id="save_temp_and_print_btn"
                                class="bg-amber-100 border-2 border-amber-500 text-amber-700 font-black py-4 px-8 rounded-2xl hover:bg-amber-200 transition-all flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                    </path>
                                </svg>
                                Draft & Print
                            </button>

                            <button type="submit" id="save_btn"
                                class="bg-white border-2 border-slate-200 text-slate-700 font-black py-4 px-8 rounded-2xl hover:bg-slate-100 transition-all flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                    </path>
                                </svg>
                                Save Only
                            </button>

                            <button type="button" id="save_and_print_btn"
                                class="bg-blue-600 text-white font-black py-4 px-12 rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition-all flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                    </path>
                                </svg>
                                Save & Print Rx
                            </button>

                            <button type="button" id="print_btn"
                                class="bg-slate-800 text-white font-black py-4 px-8 rounded-2xl hover:bg-black transition-all flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                Quick Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Metadata -->
            <input type="hidden" name="prescription_id" id="prescription_id" value="{{ $draftPrescription->id ?? '' }}">
            <input type="hidden" name="is_draft" id="is_draft_input" value="0">
            <input type="hidden" name="current_date" id="current_date">
            <input type="hidden" name="current_time" id="current_time">
        </form>
    </div>
</div>

<!-- Modals -->
<!-- Patient Search Modal -->
<div id="patientSearchModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100] transition-all duration-300">
    <div
        class="relative top-10 mx-auto p-0 border-0 w-full max-w-2xl shadow-2xl rounded-[2.5rem] bg-white overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <div class="flex items-center">
                <div class="bg-blue-100 text-blue-600 p-2.5 rounded-2xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight">Patient Directory</h3>
            </div>
            <button type="button"
                class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all"
                onclick="closeModal('patientSearchModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <div class="relative mb-8">
                <input type="text" id="patient_search_input"
                    class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-12 text-slate-900 font-bold focus:ring-4 focus:ring-blue-50 transition-all"
                    placeholder="Search by name, phone, or MR number...">
                <svg class="w-5 h-5 text-slate-400 absolute left-4 top-4.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">MR
                                Number</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Name</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Info</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody id="patient_search_results_body" class="divide-y divide-slate-50">
                        <!-- Results -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Medicine Search Modal -->
<div id="medicineSearchModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100]">
    <div
        class="relative top-10 mx-auto p-0 border-0 w-full max-w-2xl shadow-2xl rounded-[2.5rem] bg-white overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <div class="flex items-center">
                <div class="bg-indigo-100 text-indigo-600 p-2.5 rounded-2xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.642.321a2 2 0 01-1.584.07l-3.99-1.33a2 2 0 00-1.265.017l-1.955.651a2 2 0 00-1.332 1.897V21a2 2 0 002 2h14a2 2 0 002-2v-3.572a2 2 0 00-1.956-2.001z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight">Medicine Finder</h3>
            </div>
            <button type="button"
                class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all"
                onclick="closeModal('medicineSearchModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <div class="relative mb-8">
                <input type="text" id="medicine_search_input_modal"
                    class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-12 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-50"
                    placeholder="Search for generic or brand name...">
                <svg class="w-5 h-5 text-slate-400 absolute left-4 top-4.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Medicine Name</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody id="medicine_search_results_body" class="divide-y divide-slate-50"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add New Medicine Modal -->
<div id="addMedicineModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100]">
    <div
        class="relative top-20 mx-auto p-0 border-0 w-full max-md shadow-2xl rounded-[2.5rem] bg-white overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">New Medicine</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl"
                onclick="closeModal('addMedicineModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form id="new_medicine_form" class="p-8">
            <div class="mb-8">
                <label
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Medicine
                    Name</label>
                <input type="text" id="new_medicine_name"
                    class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-blue-50"
                    placeholder="e.g., Paracetamol 500mg" required>
            </div>
            <div class="flex space-x-3">
                <button type="button"
                    class="flex-1 bg-slate-100 text-slate-600 font-black py-4 rounded-2xl hover:bg-slate-200 transition-all"
                    onclick="closeModal('addMedicineModal')">Cancel</button>
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">Add
                    to System</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Diagnosis Modal -->
<div id="addDiagnosisModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100]">
    <div
        class="relative top-20 mx-auto p-0 border-0 w-full max-w-md shadow-2xl rounded-[2.5rem] bg-white overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">New Diagnosis</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl"
                onclick="closeModal('addDiagnosisModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <div class="mb-8">
                <label
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Diagnosis
                    Name</label>
                <input type="text" id="new_diagnosis_name"
                    class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-amber-50"
                    placeholder="e.g., Hypertension">
            </div>
            <div class="flex space-x-3">
                <button type="button"
                    class="flex-1 bg-slate-100 text-slate-600 font-black py-4 rounded-2xl hover:bg-slate-200 transition-all"
                    onclick="closeModal('addDiagnosisModal')">Cancel</button>
                <button type="button" id="save_new_diagnosis_btn"
                    class="flex-1 bg-amber-500 text-white font-black py-4 rounded-2xl shadow-lg shadow-amber-200 hover:bg-amber-600 transition-all">Save
                    Diagnosis</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Report Modal -->
<div id="addReportModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100]">
    <div
        class="relative top-20 mx-auto p-0 border-0 w-full max-w-md shadow-2xl rounded-[2.5rem] bg-white overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">New Report Type</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl"
                onclick="closeModal('addReportModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <div class="mb-8">
                <label
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Test/Report
                    Name</label>
                <input type="text" id="new_report_name"
                    class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-50"
                    placeholder="e.g., CBC with Diff">
            </div>
            <div class="flex space-x-3">
                <button type="button"
                    class="flex-1 bg-slate-100 text-slate-600 font-black py-4 rounded-2xl hover:bg-slate-200 transition-all"
                    onclick="closeModal('addReportModal')">Cancel</button>
                <button type="button" id="save_new_report_btn"
                    class="flex-1 bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">Add
                    Report</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Medicine Group Modal -->
<div id="addMedicineGroupModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100]">
    <div
        class="relative top-10 mx-auto p-0 border-0 w-full max-w-4xl shadow-2xl rounded-[3rem] bg-white overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Create Medicine Group</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl"
                onclick="closeModal('addMedicineGroupModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <div class="mb-8">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 ml-1">Group
                    Name</label>
                <input type="text" id="new_group_name"
                    class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-black text-xl focus:ring-4 focus:ring-blue-50"
                    placeholder="e.g., Common Cold Protocol">
            </div>
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Medicines in
                        this Group</label>
                    <button type="button" id="add_group_medicine_field_btn"
                        class="text-blue-600 font-black text-sm flex items-center hover:underline">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Another
                    </button>
                </div>
                <div id="new_group_medicines_container"
                    class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar"></div>
            </div>
            <div class="flex justify-end space-x-3 pt-6 border-t border-slate-100">
                <button type="button"
                    class="bg-slate-100 text-slate-600 font-black py-4 px-8 rounded-2xl hover:bg-slate-200 transition-all"
                    onclick="closeModal('addMedicineGroupModal')">Cancel</button>
                <button type="button" id="save_new_group_btn"
                    class="bg-blue-600 text-white font-black py-4 px-12 rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all">Save
                    Protocol</button>
            </div>
        </div>
    </div>
</div>

<!-- Pending Patients (Drafts) Modal -->
<div id="draftsModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100] transition-all duration-300">
    <div
        class="relative top-10 mx-auto p-0 border-0 w-full max-w-4xl shadow-2xl rounded-[2.5rem] bg-white overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <div class="flex items-center">
                <div class="bg-amber-100 text-amber-600 p-2.5 rounded-2xl mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight">Pending Patients (Drafts)</h3>
            </div>
            <button type="button"
                class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all"
                onclick="closeModal('draftsModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">MR
                                Number</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Name</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Saved At</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($drafts as $draft)
                            <tr class="hover:bg-slate-50 transition-all">
                                <td class="px-6 py-4">
                                    <span
                                        class="bg-amber-50 text-amber-600 px-3 py-1 rounded-lg text-xs font-black uppercase border border-amber-100">{{ $draft->patient->mr_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-slate-900 font-black text-sm">{{ $draft->patient->name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-slate-500 text-xs font-bold">
                                        {{ $draft->updated_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="?prescription_id={{ $draft->id }}"
                                        class="bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-xl hover:bg-blue-600 transition-all inline-block">
                                        Continue
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold italic">No
                                    pending patients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div id="historyModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100]">
    <div
        class="relative top-10 mx-auto p-0 border-0 w-full max-w-2xl shadow-2xl rounded-[2.5rem] bg-white overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">Visit History Details</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl"
                onclick="closeModal('historyModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div id="history_modal_content" class="p-8 custom-scrollbar max-h-[80vh] overflow-y-auto"></div>
    </div>
</div>

<!-- Report Content Modal -->
<div id="reportModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[100]">
    <div
        class="relative top-20 mx-auto p-0 border-0 w-full max-w-2xl shadow-2xl rounded-[2.5rem] bg-white overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">Report Insights</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl"
                onclick="closeModal('reportModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div id="report_content" class="p-8 text-slate-700 leading-relaxed font-medium"></div>
    </div>
</div>

<div id="printable-prescription" class="print-only p-8"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    // --- GLOBAL STATE ---
    let prescribedMedicines = {!! json_encode($draftPrescription->medicines ?? []) !!};

    let diagnosesSelect, reportsSelect, abstainsSelect;

    // --- Dynamic Data from Laravel ---
    const ALL_DIAGNOSES = @json($diagnosesList);
    const ALL_REPORTS = @json($reportsList);
    const ALL_MEDICINES = @json($medicinesList);
    const MEDICINE_GROUPS = @json($medicineGroups);
    const ABSTAIN_GROUPS = @json($abstainsList);
    const PATIENT = @json($patient);
    const LATEST_PRESCRIPTION = @json($latestPrescription);
    const PATIENT_VISITS = @json($patient->prescriptions ?? []);
    const ALL_DOSAGES = @json($dosagesList);
    const DRAFT_PRESCRIPTION = @json($draftPrescription);

    // --- HELPER FUNCTIONS ---
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function showMessage(msg) {
        alert(msg); // Placeholder for a better toast system
    }

    function clearMedicineFields() {
        document.getElementById('medicine_search').value = '';
        document.getElementById('dosage_frequency').value = '';
        document.getElementById('duration').value = '';
        resetDosageDropdown();
    }

    function resetDosageDropdown() {
        const dosageDropdown = document.getElementById('dosage_frequency');
        dosageDropdown.innerHTML = '<option value="">Select Dosage</option>';
        ALL_DOSAGES.forEach(dosage => {
            const option = document.createElement('option');
            option.value = dosage.name;
            option.textContent = dosage.name;
            dosageDropdown.appendChild(option);
        });
    }

    function updateDosageDropdown(medicineName) {
        const dosageDropdown = document.getElementById('dosage_frequency');
        const medicine = ALL_MEDICINES.find(m => m.name === medicineName);

        if (medicine && medicine.dosages && medicine.dosages.length > 0) {
            dosageDropdown.innerHTML = '<option value="">Select Dosage</option>';
            medicine.dosages.forEach(dosage => {
                const option = document.createElement('option');
                option.value = dosage.name;
                option.textContent = dosage.name;
                dosageDropdown.appendChild(option);
            });
        } else {
            resetDosageDropdown();
        }
    }

    function renderPrescriptionTable() {
        const prescriptionTableBody = document.getElementById('prescription_table_body');
        prescriptionTableBody.innerHTML = '';
        prescribedMedicines.forEach((med, index) => {
            const row = prescriptionTableBody.insertRow();
            row.className = "hover:bg-slate-50 transition-colors";
            row.innerHTML = `
                <td class="px-6 py-4 text-sm font-black text-slate-400">${index + 1}</td>
                <td class="px-6 py-4">
                    <p class="font-black text-slate-900">${med.name}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="bg-blue-50 text-blue-700 text-xs font-black px-3 py-1 rounded-lg border border-blue-100">
                        ${med.dosage}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        <input type="number" value="${med.duration}" class="w-16 bg-white border-slate-200 rounded-lg py-1 px-2 text-center text-sm font-black edit-duration-input focus:ring-2 focus:ring-blue-100" data-index="${index}">
                        <span class="text-xs font-bold text-slate-400">Days</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <button type="button" class="text-red-400 hover:text-red-600 p-2 rounded-xl hover:bg-red-50 transition-all remove-medicine-btn" data-index="${index}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </td>
            `;
        });
        document.getElementById('medicines_input').value = JSON.stringify(prescribedMedicines);
    }

    function renderMedicineSearchResults(medicines) {
        const medicineSearchResultsBody = document.getElementById('medicine_search_results_body');
        medicineSearchResultsBody.innerHTML = '';
        medicines.forEach(med => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${med.id}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${med.name}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-full select-medicine-btn"
                    data-name="${med.name}">Select</button>
            </td>
        `;
            medicineSearchResultsBody.appendChild(row);
        });
    }

    function resetForm() {
        document.getElementById('prescription_form').reset();
        prescribedMedicines = [];
        renderPrescriptionTable();
        diagnosesSelect.clear();
        reportsSelect.clear();
        if (abstainsSelect) abstainsSelect.clear();
        document.getElementById('patient_name').value = '';
        document.getElementById('age').value = '';
        document.getElementById('sex').value = '';
        document.getElementById('history_list').innerHTML = `<p class="text-gray-500 text-sm">Enter an MR Number to view previous visits.</p>`;
        document.getElementById('current_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('current_time').value = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
    }

    function generatePrintContent() {
        const selectedDiagnoses = diagnosesSelect.getValue().map(id => {
            const diagnosis = ALL_DIAGNOSES.find(d => d.id == id);
            return diagnosis ? diagnosis.name : null;
        }).filter(Boolean);

        const selectedReports = reportsSelect.getValue().map(id => {
            const report = ALL_REPORTS.find(r => r.id == id);
            return report ? report.name : null;
        }).filter(Boolean);

        const vitals = {
            bp: document.getElementById('bp').value || 'N/A',
            pulse: document.getElementById('pulse').value || 'N/A',
            temp: document.getElementById('temperature').value || 'N/A',
            weight: document.getElementById('weight').value || 'N/A',
            oxygen: document.getElementById('oxygen').value || 'N/A'
        };

        const current_date = document.getElementById('current_date').value;
        const patientName = document.getElementById('patient_name').value;
        const age = document.getElementById('age').value;
        const sex = document.getElementById('sex').value;
        const complaints = document.getElementById('complaints').value || 'N/A';
        const notes = document.getElementById('notes').value || 'N/A';
        const next_visit_date = document.getElementById('next_visit_date').value || 'N/A';

        const medicinesList = prescribedMedicines.map((med, index) => `
            <div class="medicine-item">
                <span class="med-number">${index + 1}.</span>
                <span class="med-name">${med.name}</span>
                <span class="med-dosage">${med.dosage}</span>
                <span class="med-duration">(${med.duration} days)</span>
            </div>
        `).join('');

        const selectedAbstains = abstainsSelect.getValue().map(id => {
            let foundItem = null;
            ABSTAIN_GROUPS.forEach(group => {
                const item = group.items.find(i => i.id == id);
                if (item) foundItem = item;
            });
            return foundItem ? `${foundItem.item} ${foundItem.duration ? '(' + foundItem.duration + ')' : ''}` : null;
        }).filter(Boolean);

        const abstainsListHtml = selectedAbstains.map((text, index) => `
            <div class="abstain-item">
                <span class="abstain-number">${index + 1}.</span>
                <span class="abstain-text">${text}</span>
            </div>
        `).join('');

        let content = `
            <div class="print-area font-sans text-gray-800 leading-relaxed">
                <div style="height: 4.5cm;"></div>
                <div class="patient-details-row">
                    <span class="patient-name-val">${patientName}</span>
                    <span class="patient-age-val">${age}</span>
                    <span class="patient-sex-val">${sex}</span>
                    <span class="patient-date-val">${current_date}</span>
                </div>
                <div class="main-content-container">
                    <div class="left-info-container">
                        <div class="section">
                            <h3 class="section-title">Complaints</h3>
                            <p class="section-text">${complaints}</p>
                        </div>
                        <div class="section">
                            <h3 class="section-title">Vitals</h3>
                            <div class="vitals-list">
                                <p><strong>B.P:</strong> ${vitals.bp}</p>
                                <p><strong>Pulse:</strong> ${vitals.pulse} bpm</p>
                                <p><strong>Temp:</strong> ${vitals.temp} °F</p>
                                <p><strong>O2:</strong> ${vitals.oxygen} %</p>
                                <p><strong>Weight:</strong> ${vitals.weight} kg</p>
                            </div>
                        </div>
                        <div class="section">
                            <h3 class="section-title">Diagnoses</h3>
                            <p class="section-text">${selectedDiagnoses.join(', ') || 'N/A'}</p>
                        </div>
                        <div class="section">
                            <h3 class="section-title">Reports</h3>
                            <p class="section-text">${selectedReports.join(', ') || 'N/A'}</p>
                        </div>
                        <div class="section">
                            <h3 class="section-title">Doctor's Notes</h3>
                            <p class="section-text">${notes}</p>
                        </div>
                        <div class="section next-visit">
                            <p><strong>Next Visit:</strong> ${next_visit_date}</p>
                        </div>
                    </div>
                    <div class="right-rx-container">
                        <div class="rx-section">
                            <h3 class="section-title">Rx</h3>
                            <div class="medicines-list-container">
                                ${medicinesList || 'No medicines prescribed.'}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bottom-abstains-section">
                    <div class="section">
                        <h3 class="section-title">Abstain</h3>
                        <div class="abstain-list-container">
                            ${abstainsListHtml || 'No abstains listed.'}
                        </div>
                    </div>
                </div>
                <div style="height: 3cm;"></div>
            </div>
        `;
        return content;
    }

    function printPrescription() {
        const contentToPrint = generatePrintContent();
        let iframe = document.getElementById('print_iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print_iframe';
            iframe.style.visibility = 'hidden';
            iframe.style.position = 'fixed';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);
        }

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Print Prescription</title>
                <style>
                    body { font-family: sans-serif; margin: 0; padding: 0; font-size: 10pt; }
                    .print-area { padding: 0 1cm; }
                    h3.section-title { 
                        font-weight: bold; 
                        margin-bottom: 0.3rem; 
                        font-size: 11pt; 
                        text-transform: uppercase; 
                        border-bottom: 1px solid #ccc; 
                        padding-bottom: 0.2rem; 
                    }
                    .section-text { font-size: 9pt; line-height: 1.3; }
                    .section { margin-bottom: 1rem; }
                    .patient-details-row { position: absolute; top: 4.8cm; left: 0; width: 100%; }
                    .patient-name-val { position: absolute; left: 4cm; width: 6cm; }
                    .patient-age-val { position: absolute; left: 11cm; width: 2cm; }
                    .patient-sex-val { position: absolute; left: 14cm; width: 2cm; }
                    .patient-date-val { position: absolute; right: 2cm; width: 4cm; text-align: right; }
                    .main-content-container { display: flex; justify-content: space-between; margin-top: 2cm; gap: 1cm; }
                    .left-info-container { width: 30%; border-right: 1px solid #ddd; padding-right: 0.8cm; }
                    .right-rx-container { width: 68%; padding-left: 0.5cm; }
                    .vitals-list p { margin: 0.15rem 0; font-size: 9pt; }
                    .rx-section .section-title { font-size: 14pt; margin-bottom: 0.5rem; color: #333; }
                    .medicines-list-container { margin-top: 0.5rem; }
                    .medicine-item { display: flex; align-items: baseline; margin-bottom: 0.4rem; line-height: 1.4; }
                    .med-number { min-width: 2rem; text-align: right; margin-right: 0.5rem; font-weight: bold; font-size: 10pt; }
                    .med-name { font-weight: bold; margin-right: 0.7rem; font-size: 11pt; color: #333; }
                    .med-dosage, .med-duration { font-size: 10pt; color: #666; }
                    .med-dosage { margin-right: 0.5rem; }
                    .abstain-list-container { margin-top: 0.3rem; }
                    .abstain-item { display: flex; align-items: baseline; margin-bottom: 0.25rem; }
                    .abstain-number { min-width: 1.2rem; text-align: right; margin-right: 0.3rem; }
                    .abstain-text { font-size: 8pt; }
                    .next-visit { margin-top: 1rem; font-size: 9pt; font-weight: bold; }
                    @media print { body { margin: 0; } .print-area { padding: 0; } }
                </style>
            </head>
            <body>
                ${contentToPrint}
                <script>window.onload = function() { window.focus(); window.print(); };<\/script>
            </body>
            </html>
        `);
        doc.close();
    }

    // --- EVENT LISTENERS ---
    document.addEventListener('DOMContentLoaded', () => {
        const patientNameInput = document.getElementById('patient_name');
        const ageInput = document.getElementById('age');
        const sexInput = document.getElementById('sex');
        const addMedicineBtn = document.getElementById('add_medicine_btn');
        const medicineGroupsSelect = document.getElementById('medicine_groups');
        const medicineSearchInput = document.getElementById('medicine_search');
        const medicineSearchInputModal = document.getElementById('medicine_search_input_modal');
        const medicineSearchResultsBody = document.getElementById('medicine_search_results_body');
        const printBtn = document.getElementById('print_btn');
        const addDiagnosisBtn = document.getElementById('add_diagnoses_btn');
        const saveNewDiagnosisBtn = document.getElementById('save_new_diagnosis_btn');
        const addReportsBtn = document.getElementById('add_reports_btn');
        const saveNewReportBtn = document.getElementById('save_new_report_btn');
        const addMedicineGroupBtn = document.getElementById('add_medicine_group_btn');
        const addGroupMedicineFieldBtn = document.getElementById('add_group_medicine_field_btn');
        const newGroupMedicinesContainer = document.getElementById('new_group_medicines_container');
        const saveNewGroupBtn = document.getElementById('save_new_group_btn');
        const addNewMedicineBtn = document.getElementById('add_new_medicine_btn');
        const newMedicineForm = document.getElementById('new_medicine_form');
        const saveBtn = document.getElementById('save_btn');
        const saveAndPrintBtn = document.getElementById('save_and_print_btn');
        const patientSearchBtn = document.getElementById('patient_search_btn');
        const patientSearchInput = document.getElementById('patient_search_input');
        const patientSearchResultsBody = document.getElementById('patient_search_results_body');
        const draftsListBtn = document.getElementById('drafts_list_btn');
        const saveTempBtn = document.getElementById('save_temp_btn');
        const saveTempAndPrintBtn = document.getElementById('save_temp_and_print_btn');

        // Initialize Tom Select
        diagnosesSelect = new TomSelect('#diagnoses_select', {
            plugins: ['remove_button'],
            options: ALL_DIAGNOSES.map(d => ({ value: d.id, text: d.name })),
            create: false,
            placeholder: 'Search diagnoses...',
        });

        reportsSelect = new TomSelect('#reports_select', {
            plugins: ['remove_button'],
            options: ALL_REPORTS.map(r => ({ value: r.id, text: r.name })),
            create: false,
            placeholder: 'Search reports...',
        });

        abstainsSelect = new TomSelect('#abstains_select', {
            plugins: ['remove_button'],
            create: false,
            placeholder: 'Select advice/precautions...',
            dropdownParent: 'body',
            render: {
                dropdown: function () {
                    return '<div class="ts-dropdown dark-theme-dropdown"></div>';
                }
            }
        });

        // Populate patient details
        if (PATIENT) {
            patientNameInput.value = PATIENT.name;
            ageInput.value = PATIENT.age;
            sexInput.value = PATIENT.gender;

            if (DRAFT_PRESCRIPTION) {
                document.getElementById('bp').value = DRAFT_PRESCRIPTION.bp || '';
                document.getElementById('pulse').value = DRAFT_PRESCRIPTION.pulse || '';
                document.getElementById('temperature').value = DRAFT_PRESCRIPTION.temperature || '';
                document.getElementById('weight').value = DRAFT_PRESCRIPTION.weight || '';
                document.getElementById('oxygen').value = DRAFT_PRESCRIPTION.oxygen || '';
                document.getElementById('complaints').value = DRAFT_PRESCRIPTION.complaints || '';
                document.getElementById('notes').value = DRAFT_PRESCRIPTION.notes || '';
                document.getElementById('next_visit_date').value = DRAFT_PRESCRIPTION.next_visit_date || '';

                setTimeout(() => {
                    if (DRAFT_PRESCRIPTION.diagnoses) diagnosesSelect.setValue(DRAFT_PRESCRIPTION.diagnoses);
                    if (DRAFT_PRESCRIPTION.reports) reportsSelect.setValue(DRAFT_PRESCRIPTION.reports);
                    if (DRAFT_PRESCRIPTION.abstains) {
                        const ids = DRAFT_PRESCRIPTION.abstains.map(a => typeof a === 'object' ? a.id : a).filter(id => id !== undefined);
                        abstainsSelect.setValue(ids);
                    }
                }, 500);
                renderPrescriptionTable();
            }
        }

        // Patient Search Modal Logic
        patientSearchBtn.addEventListener('click', () => {
            openModal('patientSearchModal');
            patientSearchInput.focus();
        });

        const quickSearchInput = document.getElementById('patient_quick_search');
        if (quickSearchInput) {
            quickSearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const term = quickSearchInput.value.trim();
                    if (term) {
                        window.location.href = `{{ route('doctors.write-prescription') }}?mr_number=${encodeURIComponent(term)}`;
                    }
                }
            });
        }

        patientSearchInput.addEventListener('keyup', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            if (searchTerm.length > 1) {
                patientSearchResultsBody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold italic animate-pulse">Searching patients...</td></tr>';
                fetch(`{{ url('/patients/search?term=') }}${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(patients => {
                        if (patients.length === 0) {
                            patientSearchResultsBody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold italic">No patients found matching your search.</td></tr>';
                            return;
                        }
                        let html = '';
                        patients.forEach(patient => {
                            html += `
                                <tr class="hover:bg-slate-50 cursor-pointer select-patient-row group transition-all" data-mr-number="${patient.mr_number}">
                                    <td class="px-6 py-4">
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-xs font-black uppercase border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors">${patient.mr_number}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-slate-900 font-black text-sm">${patient.name}</p>
                                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-tight">${patient.mobile_number || 'No Phone'}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center space-x-2">
                                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-black">${patient.age}Y</span>
                                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-black uppercase">${patient.gender || 'N/A'}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" class="bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-xl hover:bg-blue-600 transition-all select-patient-btn" data-mr-number="${patient.mr_number}">
                                            Select Patient
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        patientSearchResultsBody.innerHTML = html;
                    })
                    .catch(err => {
                        patientSearchResultsBody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-red-400 font-bold italic">Error searching patients. Please try again.</td></tr>';
                    });
            } else {
                patientSearchResultsBody.innerHTML = '';
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.matches('.select-patient-btn') || e.target.closest('.select-patient-row')) {
                const mrNumber = e.target.dataset.mrNumber || e.target.closest('.select-patient-row').dataset.mrNumber;
                if (mrNumber) {
                    window.location.href = `{{ route('doctors.write-prescription') }}?mr_number=${mrNumber}`;
                }
            }
        });

        // View History Details
        document.addEventListener('click', (e) => {
            if (e.target && e.target.closest('.view-history-btn')) {
                const visitId = e.target.closest('.view-history-btn').dataset.id;
                fetch(`{{ url('/prescriptions/') }}/${visitId}`)
                    .then(response => response.json())
                    .then(visit => {
                        const historyModalContent = document.getElementById('history_modal_content');
                        const abstains = visit.abstains || [];
                        const historyHtml = `
                            <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                                <div class="flex items-center justify-between bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Visit Date</p>
                                        <p class="text-2xl font-black text-slate-800">${new Date(visit.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Visit ID</p>
                                        <p class="text-xl font-black text-blue-600">#RX-${visit.id}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm"><p class="text-[10px] font-black text-slate-400 uppercase mb-1">B.P.</p><p class="font-black text-slate-900">${visit.bp || 'N/A'}</p></div>
                                    <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm"><p class="text-[10px] font-black text-slate-400 uppercase mb-1">Pulse</p><p class="font-black text-slate-900">${visit.pulse || 'N/A'} BPM</p></div>
                                    <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm"><p class="text-[10px] font-black text-slate-400 uppercase mb-1">Temp</p><p class="font-black text-slate-900">${visit.temperature || 'N/A'} °F</p></div>
                                    <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm"><p class="text-[10px] font-black text-slate-400 uppercase mb-1">O2</p><p class="font-black text-slate-900">${visit.oxygen || 'N/A'} %</p></div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-amber-50/50 p-6 rounded-[2rem] border border-amber-100"><h4 class="text-xs font-black text-amber-600 uppercase tracking-widest mb-3">Chief Complaints</h4><p class="text-slate-700 font-bold leading-relaxed">${visit.complaints || 'None recorded.'}</p></div>
                                    <div class="bg-indigo-50/50 p-6 rounded-[2rem] border border-indigo-100"><h4 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-3">Doctor's Notes</h4><p class="text-slate-700 font-bold leading-relaxed">${visit.notes || 'No notes available.'}</p></div>
                                </div>
                                <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                                    <table class="w-full text-left">
                                        <thead><tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50"><th class="px-6 py-4">Medicine</th><th class="px-6 py-4">Dosage</th><th class="px-6 py-4">Duration</th></tr></thead>
                                        <tbody class="divide-y divide-slate-50">${(visit.medicines || []).map(med => `<tr><td class="px-6 py-4 font-black text-slate-900">${med.name}</td><td class="px-6 py-4 font-bold text-slate-600"><span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase">${med.dosage}</span></td><td class="px-6 py-4 font-bold text-slate-600">${med.duration} Days</td></tr>`).join('')}</tbody>
                                    </table>
                                </div>
                                <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl">
                                    <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-4">Advice</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">${abstains.map(item => `<div class="bg-white/5 p-4 rounded-2xl border border-white/10"><p class="text-white font-bold">${item.item}</p><p class="text-slate-400 text-xs italic">${item.duration || 'N/A'}</p></div>`).join('')}</div>
                                </div>
                            </div>
                        `;
                        historyModalContent.innerHTML = historyHtml;
                        openModal('historyModal');
                    });
            }
        });

        // Add Diagnosis
        addDiagnosisBtn.addEventListener('click', () => openModal('addDiagnosisModal'));
        saveNewDiagnosisBtn.addEventListener('click', () => {
            const name = document.getElementById('new_diagnosis_name').value.trim();
            if (name) {
                fetch(`{{ url('/diagnoses') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: name })
                })
                .then(r => r.json())
                .then(data => {
                    ALL_DIAGNOSES.push(data);
                    diagnosesSelect.addOption({ value: data.id, text: data.name });
                    showMessage(`Diagnosis added.`);
                    document.getElementById('new_diagnosis_name').value = '';
                    closeModal('addDiagnosisModal');
                });
            }
        });

        // Add Report
        addReportsBtn.addEventListener('click', () => openModal('addReportModal'));
        saveNewReportBtn.addEventListener('click', () => {
            const name = document.getElementById('new_report_name').value.trim();
            if (name) {
                fetch(`{{ url('/reports') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: name })
                })
                .then(r => r.json())
                .then(data => {
                    ALL_REPORTS.push(data);
                    reportsSelect.addOption({ value: data.id, text: data.name });
                    showMessage(`Report added.`);
                    document.getElementById('new_report_name').value = '';
                    closeModal('addReportModal');
                });
            }
        });

        // Add Medicine
        addNewMedicineBtn.addEventListener('click', () => openModal('addMedicineModal'));
        newMedicineForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('new_medicine_name').value.trim();
            if (name) {
                fetch(`{{ url('/medicines') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: name })
                })
                .then(r => r.json())
                .then(data => {
                    ALL_MEDICINES.push(data);
                    showMessage(`Medicine added.`);
                    document.getElementById('new_medicine_name').value = '';
                    closeModal('addMedicineModal');
                });
            }
        });

        // Add Medicine to Table
        addMedicineBtn.addEventListener('click', () => {
            const name = medicineSearchInput.value;
            const dosage = document.getElementById('dosage_frequency').value;
            const duration = document.getElementById('duration').value;
            if (name && dosage && duration) {
                prescribedMedicines.push({ name, dosage, duration });
                renderPrescriptionTable();
                clearMedicineFields();
            } else {
                showMessage('Please fill all medicine fields.');
            }
        });

        // Medicine Groups
        medicineGroupsSelect.addEventListener('change', (e) => {
            const groupId = e.target.value;
            if (groupId) {
                const group = MEDICINE_GROUPS.find(g => g.id == groupId);
                if (group && group.medicines) {
                    group.medicines.forEach(medItem => {
                        const med = ALL_MEDICINES.find(m => m.id == medItem.pivot.medicine_id);
                        if (med) prescribedMedicines.push({ name: med.name, dosage: medItem.pivot.dosage_frequency, duration: medItem.pivot.duration_days });
                    });
                    renderPrescriptionTable();
                    showMessage(`Added from group: ${group.name}`);
                }
            }
        });

        // Save & Submit logic
        async function submitFormViaAjax(isDraft, shouldPrint) {
            const form = document.getElementById('prescription_form');
            const formData = new FormData(form);
            formData.set('is_draft', isDraft ? '1' : '0');
            formData.set('medicines_data', JSON.stringify(prescribedMedicines));
            const allButtons = [saveBtn, saveAndPrintBtn, saveTempBtn, saveTempAndPrintBtn];
            allButtons.forEach(btn => { if(btn) btn.disabled = true; });

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    if (data.prescription_id) document.getElementById('prescription_id').value = data.prescription_id;
                    if (shouldPrint) printPrescription();
                    if (data.redirect_url) setTimeout(() => window.location.href = data.redirect_url, 500);
                    else showMessage(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error(error);
                alert('Failed to save prescription.');
            } finally {
                allButtons.forEach(btn => { if(btn) btn.disabled = false; });
            }
        }

        saveBtn.addEventListener('click', (e) => { e.preventDefault(); submitFormViaAjax(false, false); });
        saveAndPrintBtn.addEventListener('click', (e) => { e.preventDefault(); submitFormViaAjax(false, true); });
        saveTempBtn.addEventListener('click', (e) => { e.preventDefault(); submitFormViaAjax(true, false); });
        saveTempAndPrintBtn.addEventListener('click', (e) => { e.preventDefault(); submitFormViaAjax(true, true); });
        printBtn.addEventListener('click', (e) => { e.preventDefault(); printPrescription(); });
        draftsListBtn.addEventListener('click', () => openModal('draftsModal'));

        // Initial setup
        document.getElementById('current_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('current_time').value = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
    });
</script>
@endpush
