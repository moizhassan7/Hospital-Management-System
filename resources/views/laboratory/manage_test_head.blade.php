@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Manage Test Head</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    <!-- Add Test Head Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Add New Test Head</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div>
                    <label for="test_head_name" class="block text-gray-700 text-sm font-bold mb-2">Test Head Name:</label>
                    <input type="text" id="test_head_name" name="test_head_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Hematology, Biochemistry" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add Test Head
                </button>
            </div>
        </form>
    </div>

    <!-- Test Head List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Test Heads</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Test Head Data --}}
                    @php
                        $testHeads = [
                            ['id' => 'TH001', 'name' => 'Hematology'],
                            ['id' => 'TH002', 'name' => 'Biochemistry'],
                            ['id' => 'TH003', 'name' => 'Microbiology'],
                            ['id' => 'TH004', 'name' => 'Pathology'],
                            ['id' => 'TH005', 'name' => 'Serology'],
                        ];
                    @endphp
                    @foreach($testHeads as $index => $head)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $head['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $head['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
