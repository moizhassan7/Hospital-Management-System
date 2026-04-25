@extends('layouts.app')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="font-black text-3xl text-slate-800 leading-tight tracking-tight">
                    Clinical Insights <span class="text-blue-600">& Analytics</span>
                </h2>
                <p class="text-slate-500 font-medium mt-1">Track practice growth and patient trends.</p>
            </div>
            <a href="{{ route('doctors.dashboard') }}" class="group flex items-center bg-white border border-slate-200 text-slate-700 font-bold py-2.5 px-5 rounded-2xl shadow-sm hover:shadow-md hover:bg-slate-50 transition-all duration-200">
                <svg class="w-5 h-5 mr-2 text-slate-400 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Dashboard
            </a>
        </div>
        
        <!-- Key Performance Indicators (KPIs) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <!-- Total Visits -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-50 p-3 rounded-2xl text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <span class="text-xs font-black text-blue-500 uppercase tracking-widest bg-blue-50 px-2 py-1 rounded-md">Lifetime</span>
                </div>
                <p class="text-sm font-bold text-slate-500">Total Visits</p>
                <h3 class="text-3xl font-black text-slate-900 mt-1">{{ $totalVisits }}</h3>
            </div>

            <!-- Today's Visits -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-emerald-50 p-3 rounded-2xl text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-xs font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 px-2 py-1 rounded-md">Live</span>
                </div>
                <p class="text-sm font-bold text-slate-500">Today's Visits</p>
                <h3 class="text-3xl font-black text-slate-900 mt-1">{{ $todaysVisits->count() }}</h3>
            </div>

            <!-- This Month -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-50 p-3 rounded-2xl text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <span class="text-xs font-black text-purple-500 uppercase tracking-widest bg-purple-50 px-2 py-1 rounded-md">Monthly</span>
                </div>
                <p class="text-sm font-bold text-slate-500">This Month</p>
                <h3 class="text-3xl font-black text-slate-900 mt-1">{{ $thisMonthVisits->count() }}</h3>
            </div>

            <!-- Peak Day -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-amber-50 p-3 rounded-2xl text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <span class="text-xs font-black text-amber-500 uppercase tracking-widest bg-amber-50 px-2 py-1 rounded-md">Record</span>
                </div>
                <p class="text-sm font-bold text-slate-500">Peak Daily Visits</p>
                <h3 class="text-3xl font-black text-slate-900 mt-1">{{ $dailyVisits->max('visit_count') ?? 0 }}</h3>
            </div>
        </div>

        <!-- Analysis Dashboard -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            
            <!-- Advanced Filters -->
            <div class="p-8 border-b border-slate-50 bg-slate-50/30">
                <div class="flex items-center mb-6">
                    <div class="w-1.5 h-6 bg-blue-600 rounded-full mr-3"></div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Report Configuration</h3>
                </div>
                
                <form method="GET" action="{{ route('doctors.reports') }}" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 ml-1">Start Date</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" 
                                class="w-full bg-white border-slate-200 rounded-2xl py-3 px-4 shadow-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 ml-1">End Date</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" 
                                class="w-full bg-white border-slate-200 rounded-2xl py-3 px-4 shadow-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 ml-1">Data Dimension</label>
                            <select name="report_type" class="w-full bg-white border-slate-200 rounded-2xl py-3 px-4 shadow-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 font-bold text-slate-700">
                                <option value="visits" {{ $reportType == 'visits' ? 'selected' : '' }}>Detailed Visits Log</option>
                                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Daily Volume Summary</option>
                                <option value="today" {{ $reportType == 'today' ? 'selected' : '' }}>Real-time Today Stats</option>
                                <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Strategic Monthly View</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-start">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-10 rounded-2xl shadow-lg shadow-blue-200 transition-all duration-300 hover:-translate-y-1 active:translate-y-0 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 2v-6m-8 2.25V17.25c0 .414.336.75.75.75h14.5a.75.75 0 00.75-.75V5.25a.75.75 0 00-.75-.75H4.25a.75.75 0 00-.75.75v12.75c0 .414.336.75.75.75H9z"></path></svg>
                            Generate Dynamic Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- Result Content Area -->
            <div class="p-8">
                @if($reportType == 'daily')
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Daily Volume Summary</h3>
                        <span class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-bold uppercase tracking-wider">Aggregated View</span>
                    </div>
                    <div class="overflow-hidden rounded-3xl border border-slate-100 shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Analysis Date</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Visit Volume</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Weekday</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($dailyVisits as $daily)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-5 text-slate-900 font-bold">{{ \Carbon\Carbon::parse($daily->visit_date)->format('M d, Y') }}</td>
                                        <td class="px-8 py-5">
                                            <span class="bg-blue-600 text-white text-xs font-black px-4 py-1.5 rounded-full shadow-sm shadow-blue-200">
                                                {{ $daily->visit_count }} Visits
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-slate-500 font-medium italic">{{ \Carbon\Carbon::parse($daily->visit_date)->format('l') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-8 py-10 text-center text-slate-400 font-bold italic bg-slate-50/20">No analytics data available for this range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($reportType == 'today')
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Today's Real-time Feed</h3>
                        <div class="flex items-center bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl text-sm font-bold animate-pulse">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                            Live Data Update
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-3xl border border-slate-100 shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">MR #</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Patient Name</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Time</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Complaints</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($todaysVisits as $prescription)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-5 font-black text-slate-400">#{{ $prescription->patient->mr_number }}</td>
                                        <td class="px-8 py-5 font-bold text-slate-900">{{ $prescription->patient->name }}</td>
                                        <td class="px-8 py-5">
                                            <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg text-xs font-bold">{{ $prescription->created_at->format('h:i A') }}</span>
                                        </td>
                                        <td class="px-8 py-5 text-slate-500 font-medium italic">{{ Str::limit($prescription->complaints, 40) }}</td>
                                        <td class="px-8 py-5 text-right space-x-3">
                                            <a href="{{ route('doctors.write-prescription', ['prescription_id' => $prescription->id]) }}" class="text-blue-600 hover:bg-blue-50 px-3 py-1 rounded-lg font-bold transition-colors">View</a>
                                            <button type="button" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-lg text-xs font-black shadow-md shadow-emerald-100 reprint-prescription-btn" data-id="{{ $prescription->id }}">Reprint Rx</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-8 py-10 text-center text-slate-400 font-bold italic bg-slate-50/20">Awaiting first visit for today...</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($reportType == 'monthly')
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-8">Strategic Monthly Overview</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Monthly Trend -->
                        <div class="bg-slate-50 rounded-[2rem] p-8">
                            <h4 class="text-lg font-black text-slate-800 mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                Growth Trends
                            </h4>
                            <div class="space-y-4">
                                @foreach($monthlyVisits as $month)
                                    <div class="flex justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                        <span class="font-bold text-slate-700">
                                            {{ \Carbon\Carbon::createFromDate($month->year, $month->month, 1)->format('F Y') }}
                                        </span>
                                        <span class="bg-blue-600 text-white text-xs font-black px-4 py-1 rounded-full">
                                            {{ $month->visit_count }} Visits
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Recent Pulse -->
                        <div class="bg-slate-50 rounded-[2rem] p-8">
                            <h4 class="text-lg font-black text-slate-800 mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Recent Patient Pulse
                            </h4>
                            <div class="space-y-3 max-h-[25rem] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($thisMonthVisits->take(15) as $prescription)
                                    <div class="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-emerald-200 transition-all cursor-default">
                                        <div>
                                            <p class="font-black text-slate-800 leading-none mb-1">{{ $prescription->patient->name }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ $prescription->created_at->format('d M | h:i A') }}</p>
                                        </div>
                                        <button type="button" class="bg-slate-100 hover:bg-emerald-500 hover:text-white text-slate-500 p-2 rounded-xl transition-all duration-200 reprint-prescription-btn" data-id="{{ $prescription->id }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                @else
                    <!-- Detailed Log View -->
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Comprehensive Visit History</h3>
                        <span class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-bold uppercase tracking-wider">Audit View</span>
                    </div>
                    <div class="overflow-hidden rounded-3xl border border-slate-100 shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">MR #</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Patient Details</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-center">Timestamp</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Primary Complaint</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($prescriptions as $prescription)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-5 font-black text-slate-400">#{{ $prescription->patient->mr_number }}</td>
                                        <td class="px-8 py-5 font-bold text-slate-900">{{ $prescription->patient->name }}</td>
                                        <td class="px-8 py-5 text-center">
                                            <p class="text-sm font-bold text-slate-800 leading-none">{{ $prescription->created_at->format('Y-m-d') }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $prescription->created_at->format('h:i A') }}</p>
                                        </td>
                                        <td class="px-8 py-5 text-slate-500 font-medium italic">{{ Str::limit($prescription->complaints, 40) }}</td>
                                        <td class="px-8 py-5 text-right space-x-3">
                                            <a href="{{ route('doctors.write-prescription', ['prescription_id' => $prescription->id]) }}" class="text-blue-600 hover:bg-blue-50 px-3 py-1 rounded-lg font-bold transition-colors">Audit</a>
                                            <button type="button" class="bg-slate-800 hover:bg-black text-white px-4 py-1.5 rounded-lg text-xs font-black shadow-md reprint-prescription-btn" data-id="{{ $prescription->id }}">Reprint</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-8 py-10 text-center text-slate-400 font-bold italic bg-slate-50/20">No history records found for this criteria.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Modern Pagination -->
                    @if($prescriptions->hasPages())
                        <div class="mt-10 px-4">
                            {{ $prescriptions->appends(request()->input())->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    
    @media print {
        .no-print { display: none !important; }
        body { -webkit-print-color-adjust: exact; background: white; }
        .shadow-sm, .shadow-md, .shadow-lg { shadow: none !important; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Reprint prescription functionality
        const reprintButtons = document.querySelectorAll('.reprint-prescription-btn');
        reprintButtons.forEach(button => {
            button.addEventListener('click', function() {
                const prescriptionId = this.getAttribute('data-id');
                if (prescriptionId) {
                    // Create a hidden iframe for printing if it doesn't exist
                    let iframe = document.getElementById('reprint_iframe');
                    if (!iframe) {
                        iframe = document.createElement('iframe');
                        iframe.id = 'reprint_iframe';
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);
                    }
                    
                    // Set the source to the print route
                    iframe.src = `{{ route('doctors.print-prescription', '') }}/${prescriptionId}`;
                }
            });
        });
    });
</script>
@endsection
