@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Manage Pathology Test Head</h2></div>
        <a href="{{ route('pathology.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Pathology
        </a>
    </div>

    @include('partials.flash-alerts')

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

    <div class="hms-panel hms-panel-padded mb-5">
        @if(isset($testHead))
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Edit Pathology Test Head</h3>
            <form action="{{ route('test_head.update', $testHead->id) }}" method="POST">
                @csrf
                @method('PUT')
        @else
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Add New Pathology Test Head</h3>
            <form action="{{ route('test_head.store') }}" method="POST">
                @csrf
        @endif
            <input type="hidden" name="category" value="{{ $category ?? 'Pathology' }}">
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div>
                    <label for="test_head_name" class="hms-label">Test Head Name:</label>
                    <input type="text" id="test_head_name" name="test_head_name" class="hms-input" placeholder="e.g., Hematology, Biochemistry" value="{{ old('test_head_name', $testHead->name ?? '') }}" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="hms-btn hms-btn-primary">
                    {{ isset($testHead) ? 'Update Test Head' : 'Add Test Head' }}
                </button>
            </div>
        </form>
    </div>

    @php $currentSearch = $search ?? ''; @endphp
    <div class="hms-panel hms-panel-padded">
        <div class="flex flex-col gap-4 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">Existing Test Heads</h3>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $testHeads->total() }} {{ \Illuminate\Support\Str::plural('test head', $testHeads->total()) }} found@if($currentSearch !== '') for “{{ $currentSearch }}”@endif.
                </p>
            </div>
            <form method="GET" action="{{ route('pathology.test_head') }}" class="w-full sm:max-w-md">
                <div class="hms-search-bar w-full flex items-center gap-2">
                    <input type="text" name="q" id="existing-test-heads-search" class="hms-input flex-1" placeholder="Search test head…" autocomplete="off" value="{{ $currentSearch }}">
                    <button type="submit" class="hms-btn hms-btn-primary">Search</button>
                    @if($currentSearch !== '')
                        <a href="{{ route('pathology.test_head') }}" class="hms-btn hms-btn-ghost">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        @if($testHeads->isEmpty())
            <p class="text-sm text-gray-500 py-6 text-center">
                @if($currentSearch !== '')
                    No test heads match your search.
                @else
                    No test heads have been defined yet.
                @endif
            </p>
        @else
            <div class="hms-table-wrap">
                <table class="hms-table" id="existing-test-heads-table">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="existing-test-heads-body">
                        @foreach($testHeads as $head)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $testHeads->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $head->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('pathology.test_head.edit', $head->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                    <form action="{{ route('test_head.destroy', $head->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this test head?');">
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

            <div class="mt-4">
                {{ $testHeads->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection