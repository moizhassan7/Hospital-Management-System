@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Doctor Shares Report</h2>
        <a href="{{ route('reports.hospital_shares') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200">
            View Hospital Shares
        </a>
    </div>

    <!-- Filter Section -->
    <form action="{{ route('reports.doctor_shares') }}" method="GET" class="mb-8 bg-gray-50 p-4 rounded-lg flex items-end space-x-4">
        <div class="flex-grow">
            <label for="procedure_id" class="block text-gray-700 text-sm font-bold mb-2">Filter by Procedure:</label>
            <select name="procedure_id" id="procedure_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Procedures</option>
                @foreach($procedures as $proc)
                    <option value="{{ $proc->id }}" {{ $procedureId == $proc->id ? 'selected' : '' }}>
                        {{ $proc->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-6 rounded-lg shadow-lg">Filter</button>
            <a href="{{ route('reports.doctor_shares') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded-lg shadow-lg ml-2">Clear</a>
        </div>
    </form>

    <!-- Report Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded-lg overflow-hidden shadow-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-6 text-left">Doctor Name</th>
                    <th class="py-3 px-6 text-left">Doctor Code</th>
                    <th class="py-3 px-6 text-left">Procedure</th>
                    <th class="py-3 px-6 text-center">Doctor Share (%)</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($shares as $share)
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-6 text-left font-medium">{{ $share->doctor_name }}</td>
                        <td class="py-3 px-6 text-left">{{ $share->doctor_code }}</td>
                        <td class="py-3 px-6 text-left">{{ $share->procedure_name }}</td>
                        <td class="py-3 px-6 text-center">
                            <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-sm font-bold">
                                {{ $share->share_percentage }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 px-6 text-center text-gray-500">No share records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
