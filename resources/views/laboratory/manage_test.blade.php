@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Manage Test</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Please fix the following errors:</span>
            <ul class="mt-3 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        @if(isset($test))
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Edit Test</h3>
            <form action="{{ route('laboratory.manage_test.update', $test->id) }}" method="POST">
                @csrf
                @method('PUT')
        @else
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Add New Test</h3>
            <form action="{{ route('laboratory.manage_test.store') }}" method="POST">
                @csrf
        @endif
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="test_id" class="block text-gray-700 text-sm font-bold mb-2">Test ID:</label>
                    <input type="text" id="test_id" name="test_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., T001" value="{{ old('test_id', $test->test_id ?? '') }}" required>
                </div>
                <div>
                    <label for="test_name" class="block text-gray-700 text-sm font-bold mb-2">Test Name:</label>
                    <input type="text" id="test_name" name="test_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Complete Blood Count" value="{{ old('test_name', $test->name ?? '') }}" required>
                </div>
                <div>
                    <label for="test_price" class="block text-gray-700 text-sm font-bold mb-2">Price (PKR):</label>
                    <input type="number" id="test_price" name="test_price" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 50.00" min="0" step="0.01" value="{{ old('test_price', $test->price ?? '') }}" required>
                </div>
                <div>
                    <label for="test_type" class="block text-gray-700 text-sm font-bold mb-2">Test Type:</label>
                    <select id="test_type" name="test_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Type</option>
                        <option value="Blood" {{ (old('test_type', $test->type ?? '') == 'Blood') ? 'selected' : '' }}>Blood</option>
                        <option value="Urine" {{ (old('test_type', $test->type ?? '') == 'Urine') ? 'selected' : '' }}>Urine</option>
                        <option value="Imaging" {{ (old('test_type', $test->type ?? '') == 'Imaging') ? 'selected' : '' }}>Imaging</option>
                        <option value="Other" {{ (old('test_type', $test->type ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label for="test_head_id" class="block text-gray-700 text-sm font-bold mb-2">Test Head:</label>
                    <select id="test_head_id" name="test_head_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Test Head</option>
                        @foreach($testHeads as $head)
                            <option value="{{ $head->id }}" {{ (old('test_head_id', $test->test_head_id ?? '') == $head->id) ? 'selected' : '' }}>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="priority" class="block text-gray-700 text-sm font-bold mb-2">Priority:</label>
                    <select id="priority" name="priority" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Priority</option>
                        <option value="Routine" {{ (old('priority', $test->priority ?? '') == 'Routine') ? 'selected' : '' }}>Routine</option>
                        <option value="Urgent" {{ (old('priority', $test->priority ?? '') == 'Urgent') ? 'selected' : '' }}>Urgent</option>
                        <option value="STAT" {{ (old('priority', $test->priority ?? '') == 'STAT') ? 'selected' : '' }}>STAT (Critical)</option>
                    </select>
                </div>
                <div>
                    <label for="report_time" class="block text-gray-700 text-sm font-bold mb-2">Report Time (Hours):</label>
                    <input type="number" id="report_time" name="report_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 24" min="0" value="{{ old('report_time', $test->report_time ?? '') }}" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ isset($test) ? 'Update Test' : 'Add Test' }}
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Tests</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report (Hours)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($tests as $index => $test)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->test_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs {{ number_format($test->price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->testHead->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->priority }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test->report_time }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('laboratory.manage_test.edit', $test->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <form action="{{ route('laboratory.manage_test.destroy', $test->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this test?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection