@extends('layouts.app')

@section('page_title', 'IPD Patients')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">IPD Patients (Desktop)</h2>
        @if(config('hospital.pathology_only'))
        <a href="{{ route('pathology.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Pathology Lab
        </a>
        @endif
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6">{{ session('error') }}</div>
    @endif

    @unless($desktopReady)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-xl mb-6">
            <strong>Desktop database not available.</strong>
            {{ $desktopError ?? 'Check DESKTOP_DB_* settings in .env and SQL Server connection.' }}
        </div>
    @endunless

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 mb-6">
        <a href="{{ route('ipd.index', array_merge(request()->except('page'), ['tab' => 'history'])) }}"
            class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors {{ $tab === 'history' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Indoor Patient History
        </a>
        <a href="{{ route('ipd.index', array_merge(request()->except('page'), ['tab' => 'detail'])) }}"
            class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors {{ $tab === 'detail' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            IPD Patient Detail
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Search & Filters</h3>
        <form method="GET" action="{{ route('ipd.index') }}" class="space-y-4">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" required
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" required
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MR No</label>
                    <input type="text" name="mr" value="{{ $filters['mr'] }}" placeholder="MR number"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $tab === 'detail' ? 'Slip ID' : 'Slip No' }}</label>
                    <input type="text" name="slip" value="{{ $filters['slip'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                    <input type="text" name="name" value="{{ $filters['name'] }}" placeholder="Partial name search"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile No</label>
                    <input type="text" name="mobile" value="{{ $filters['mobile'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" value="{{ $filters['cnic'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ward No</label>
                    <input type="text" name="ward" value="{{ $filters['ward'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bed No</label>
                    <input type="text" name="bed" value="{{ $filters['bed'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient Type</label>
                    <input type="text" name="patient_type" value="{{ $filters['patient_type'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="all" @selected($filters['status'] === 'all')>All</option>
                        <option value="admitted" @selected($filters['status'] === 'admitted')>Admitted (Not Discharged)</option>
                        <option value="discharged" @selected($filters['status'] === 'discharged')>Discharged</option>
                    </select>
                </div>
                @if($tab === 'detail')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Doctor Name</label>
                    <input type="text" name="doctor" value="{{ $filters['doctor'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <input type="text" name="department" value="{{ $filters['department'] }}"
                        class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Active Record</label>
                    <select name="active" class="w-full border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="all" @selected($filters['active'] === 'all')>All</option>
                        <option value="yes" @selected($filters['active'] === 'yes')>Active Only</option>
                        <option value="no" @selected($filters['active'] === 'no')>Inactive</option>
                    </select>
                </div>
                @endif
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">Search</button>
                <a href="{{ route('ipd.index', ['tab' => $tab]) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    @if($desktopReady && $summary)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500">
                <p class="text-xs uppercase text-gray-500">Total Records</p>
                <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
                <p class="text-xs uppercase text-gray-500">Admitted</p>
                <p class="text-2xl font-bold text-green-600">{{ $summary['admitted'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-gray-500">
                <p class="text-xs uppercase text-gray-500">Discharged</p>
                <p class="text-2xl font-bold text-gray-600">{{ $summary['discharged'] }}</p>
            </div>
            @if($tab === 'detail' && isset($summary['active']))
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-indigo-500">
                <p class="text-xs uppercase text-gray-500">Active</p>
                <p class="text-2xl font-bold text-indigo-600">{{ $summary['active'] }}</p>
            </div>
            @endif
        </div>
    @endif

    @if($desktopReady && $records)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ $tab === 'history' ? 'Indoor Patient History' : 'IPD Patient Detail' }}
                    <span class="text-sm font-normal text-gray-500">({{ $records->total() }} records)</span>
                </h3>
            </div>

            @if($records->isEmpty())
                <p class="text-gray-600">No records found for selected filters.</p>
            @elseif($tab === 'history')
                @include('ipd.partials.history-table', ['records' => $records])
            @else
                @include('ipd.partials.detail-table', ['records' => $records])
            @endif

            @if($records->hasPages())
                <div class="mt-6">{{ $records->links() }}</div>
            @endif
        </div>
    @endif
@endsection
