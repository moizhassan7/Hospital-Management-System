@php
    $currentSearch = $search ?? '';
    $totalParticulars = $testParticulars->total();
@endphp

<p class="text-sm text-gray-500 mt-1" id="existing-particulars-summary">
    {{ $totalParticulars }} {{ \Illuminate\Support\Str::plural('particular', $totalParticulars) }} found{{ $currentSearch !== '' ? ' for “'.$currentSearch.'”' : '' }}.
</p>

@if($testParticulars->isEmpty())
    <p class="text-sm text-gray-500 py-6 text-center" id="existing-particulars-empty">
        @if($currentSearch !== '')
            No particulars match your search.
        @else
            No test particulars have been defined yet.
        @endif
    </p>
@else
    <div class="hms-table-wrap">
        <table class="hms-table" id="existing-particulars-table">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Normal Range</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="existing-particulars-body">
                @foreach($testParticulars as $index => $listedParticular)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $testParticulars->firstItem() + $index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->sort_order }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->test->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->test->testHead->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->unit }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listedParticular->normal_range_min }} - {{ $listedParticular->normal_range_max }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('pathology.add_test_particulars.edit', $listedParticular->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <form action="{{ route('laboratory.add_test_particulars.destroy', $listedParticular->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this particular?');">
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

    <div class="mt-4 existing-particulars-pagination">
        {{ $testParticulars->onEachSide(1)->links() }}
    </div>
@endif
