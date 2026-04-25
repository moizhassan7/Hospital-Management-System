@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-10">
            <div class="flex items-center">
                <div class="bg-blue-600 p-3 rounded-2xl shadow-lg shadow-blue-200 mr-5 text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Patient Registration</h1>
                    <p class="text-slate-500 font-bold text-sm">Add a new patient to the clinical directory</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('doctors.dashboard') }}" class="bg-white hover:bg-slate-50 text-slate-600 font-black py-3 px-6 rounded-2xl shadow-sm border border-slate-100 transition-all flex items-center group">
                    <svg class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-white">
            <div class="bg-slate-900 px-10 py-6 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    <span class="text-slate-400 text-xs font-black uppercase tracking-[0.2em]">New Entry</span>
                </div>
                <span class="text-white font-black text-sm">MR Number: {{ str_pad(\App\Models\Patient::count() + 1, 4, '0', STR_PAD_LEFT) }}</span>
            </div>

            <form id="patient_registration_form" method="POST" action="{{ route('doctors.store-patient') }}" class="p-10">
                @csrf
                
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl mb-8 flex items-center animate-in fade-in slide-in-from-top duration-500">
                        <svg class="w-6 h-6 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-bold">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Basic Info Section -->
                    <div class="space-y-6">
                        <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest ml-1">Basic Information</h3>
                        
                        <div>
                            <label for="patient_name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                            <input type="text" id="patient_name" name="patient_name" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-blue-50 transition-all" placeholder="Enter patient's full name" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="age" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Age</label>
                                <input type="number" id="age" name="age" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-blue-50 transition-all" placeholder="Years" required>
                            </div>
                            <div>
                                <label for="gender" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Gender</label>
                                <select id="gender" name="gender" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-blue-50 transition-all appearance-none" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="contact_number" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-blue-50 transition-all" placeholder="e.g., 0300-1234567" required>
                        </div>
                    </div>

                    <!-- Classification Section -->
                    <div class="space-y-6">
                        <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest ml-1">Classification & Address</h3>
                        
                        <div>
                            <label for="patient_type" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Patient Category</label>
                            <select id="patient_type" name="patient_type" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-50 transition-all appearance-none" required>
                                <option value="General">General Patient</option>
                                <option value="Family">Family Patient</option>
                            </select>
                        </div>

                        <div id="family_fields_container" class="hidden space-y-4 animate-in slide-in-from-right duration-300">
                            <div>
                                <label for="family_name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Family Reference Name</label>
                                <input type="text" id="family_name" name="family_name" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-50 transition-all" placeholder="e.g., Malik Family">
                            </div>
                            <div>
                                <label for="family_relation" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Relation to Head</label>
                                <input type="text" id="family_relation" name="family_relation" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-50 transition-all" placeholder="e.g., Self, Spouse, Child">
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Residential Address</label>
                            <textarea id="address" name="address" rows="4" class="w-full bg-slate-50 border-slate-100 rounded-3xl py-4 px-6 text-slate-900 font-bold focus:ring-4 focus:ring-blue-50 transition-all resize-none" placeholder="Enter complete address details..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between mt-12 pt-8 border-t border-slate-50">
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest max-w-[200px]">Ensure all data is accurate before registering</p>
                    <div class="flex space-x-4">
                        <button type="reset" class="bg-slate-100 text-slate-600 font-black py-4 px-8 rounded-2xl hover:bg-slate-200 transition-all">Reset Form</button>
                        <button type="submit" class="bg-blue-600 text-white font-black py-4 px-12 rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition-all active:translate-y-0">
                            Register Patient
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const patientTypeSelect = document.getElementById('patient_type');
        const familyFieldsContainer = document.getElementById('family_fields_container');

        function toggleFamilyFields() {
            if (patientTypeSelect.value === 'Family') {
                familyFieldsContainer.classList.remove('hidden');
                familyFieldsContainer.classList.add('block');
            } else {
                familyFieldsContainer.classList.add('hidden');
                familyFieldsContainer.classList.remove('block');
            }
        }

        patientTypeSelect.addEventListener('change', toggleFamilyFields);
        toggleFamilyFields();
    });
</script>
@endsection
