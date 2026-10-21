@extends('layouts.app')

@section('title', isset($testHead) ? 'Edit test head' : ($category ?? 'Laboratory') . ' test heads')

@section('content')
    @php
        $label = $category ?? 'Laboratory';
        $hubRoute = $category == 'Pathology' ? route('pathology.index') : ($category == 'Radiology' ? route('radiology.index') : route('laboratory.index'));
        $listRoute = $category == 'Pathology' ? route('pathology.test_head') : ($category == 'Radiology' ? route('radiology.test_head') : route('test_head'));
    @endphp

    <x-ui.page-header :title="isset($testHead) ? 'Edit test head' : $label . ' test heads'" :subtitle="$category == 'Radiology' ? 'Modalities used to group radiology scans.' : 'Headings used to group tests in the catalogue and on result reports.'">
        <x-slot:actions>
            <a href="{{ $hubRoute }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />{{ $label }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($testHead) ? 'Edit ' . $testHead->name : 'Add test head'" :description="isset($testHead) ? 'The new name appears on the catalogue and on new registrations.' : 'For example Haematology, Biochemistry or X-Ray.'" :icon="isset($testHead) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            @if(isset($testHead))
                <form action="{{ route('test_head.update', $testHead->id) }}" method="POST">
                    @csrf
                    @method('PUT')
            @else
                <form action="{{ route('test_head.store') }}" method="POST">
                    @csrf
            @endif
                    <input type="hidden" name="category" value="{{ $category ?? 'Pathology' }}">
                    <div class="panel-body">
                        <x-ui.field label="Test head name" for="test_head_name" error="test_head_name" required>
                            <input type="text" id="test_head_name" name="test_head_name" @class(['input', 'is-invalid' => $errors->has('test_head_name')]) placeholder="e.g. Haematology" value="{{ old('test_head_name', $testHead->name ?? '') }}" autocomplete="off" required autofocus>
                        </x-ui.field>
                    </div>
                    <div class="panel-footer">
                        @isset($testHead)
                            <a href="{{ $listRoute }}" class="btn btn-ghost">Cancel</a>
                        @endisset
                        <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                            <x-ui.icon name="{{ isset($testHead) ? 'save' : 'plus' }}" />{{ isset($testHead) ? 'Update test head' : 'Add test head' }}
                        </button>
                    </div>
                </form>
        </x-ui.panel>

        <x-ui.panel title="Existing test heads" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#test-heads-table">{{ count($testHeads) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter test heads" data-table-filter="#test-heads-table" />
            </div>
            <div class="table-wrap">
                <table id="test-heads-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Test head</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($testHeads as $index => $head)
                            <tr @class(['bg-brand-50/50' => isset($testHead) && $testHead->id === $head->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td class="cell-strong">{{ $head->name }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('test_head.edit', $head->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <form action="{{ route('test_head.destroy', $head->id) }}" method="POST" class="inline"
                                            data-confirm="“{{ $head->name }}” and its tests will no longer be offered. Existing results stay on record."
                                            data-confirm-title="Delete this test head?" data-confirm-label="Delete test head">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="3" class="p-0">
                                    <x-ui.empty-state icon="layers" title="No test heads yet" description="Add a heading such as Haematology before you create tests under it." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
@endsection
