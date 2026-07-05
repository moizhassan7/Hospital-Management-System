@php
    $currentSearch = $search ?? '';
    $totalTests = $tests->total();
@endphp

<p class="text-sm text-gray-500 mt-1" id="existing-tests-summary">
    {{ $totalTests }} {{ \Illuminate\Support\Str::plural('test', $totalTests) }} found
    @if($currentSearch !== '')
        for “{{ $currentSearch }}”
    @endif
    .
</p>

@if($tests->isEmpty())
    <p class="text-sm text-gray-500 py-6 text-center" id="existing-tests-empty">
        @if($currentSearch !== '')
            No tests match your search.
        @else
            No tests have been defined yet.
        @endif
    </p>
@else
    <div class="hms-table-wrap">
        <table class="hms-table" id="existing-tests-table">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report (Hours)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sample Vial</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vial Volume</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry (Hrs)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="existing-tests-body">
                @foreach($tests as $listedTest)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $tests->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->testHead->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->priority }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->report_time }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->sample_vial ?? '—' }} ({{ $listedTest->vials_required ?? 1 }}x)</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->vial_volume ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedTest->sample_expiry_hours ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('pathology.manage_test.edit', $listedTest->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <form action="{{ route('laboratory.manage_test.destroy', $listedTest->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this test?');">
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

    <div class="mt-4 existing-tests-pagination">
        {{ $tests->onEachSide(1)->links() }}
    </div>
@endif
