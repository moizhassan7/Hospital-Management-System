@extends('layouts.app')

@section('title', isset($diagnosis) ? 'Edit diagnosis' : 'Diagnoses')

@section('content')
    <x-ui.page-header :title="isset($diagnosis) ? 'Edit diagnosis' : 'Diagnoses'" subtitle="Diagnosis names offered on the discharge form. Keep one entry per condition.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($diagnosis) ? 'Edit ' . $diagnosis->name : 'Add diagnosis'" :description="isset($diagnosis) ? 'The new name appears on future discharges.' : 'For example Typhoid fever or Acute appendicitis.'" :icon="isset($diagnosis) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($diagnosis) ? route('diagnoses.update', $diagnosis) : route('diagnoses.store') }}" method="POST">
                @csrf
                @isset($diagnosis)
                    @method('PUT')
                @endisset
                <div class="panel-body">
                    <x-ui.field label="Diagnosis name" for="name" error="name" required>
                        <input type="text" id="name" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="e.g. Typhoid fever" value="{{ old('name', $diagnosis->name ?? '') }}" autocomplete="off" maxlength="255" required autofocus>
                    </x-ui.field>
                </div>
                <div class="panel-footer">
                    @isset($diagnosis)
                        <a href="{{ route('diagnoses.index') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($diagnosis) ? 'save' : 'plus' }}" />{{ isset($diagnosis) ? 'Update diagnosis' : 'Add diagnosis' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Diagnosis list" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#diagnoses-table">{{ $diagnoses->count() }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter diagnoses" data-table-filter="#diagnoses-table" />
            </div>
            <div class="table-wrap">
                <table id="diagnoses-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Diagnosis</th>
                            <th>Added</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($diagnoses as $index => $item)
                            <tr @class(['bg-brand-50/50' => isset($diagnosis) && $diagnosis->id === $item->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td class="cell-strong">{{ $item->name }}</td>
                                <td class="nowrap muted">{{ optional($item->created_at)->format('d M Y') ?? '—' }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('diagnoses.edit', $item) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <form action="{{ route('diagnoses.destroy', $item) }}" method="POST" class="inline"
                                            data-confirm="“{{ $item->name }}” will no longer be offered on the discharge form. Discharges already saved keep their diagnoses."
                                            data-confirm-title="Delete this diagnosis?" data-confirm-label="Delete diagnosis">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="4" class="p-0">
                                    <x-ui.empty-state icon="clipboard" title="No diagnoses defined yet" description="Diagnoses you add here become searchable on the discharge form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
@endsection
