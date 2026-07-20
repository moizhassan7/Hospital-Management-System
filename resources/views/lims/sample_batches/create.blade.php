@extends('layouts.app')

@section('page_title', 'New Sample Batch')

@section('content')
<div class="hms-re-page">
    @include('partials.page-shell-start', [
        'title' => 'New sample batch',
        'subtitle' => 'Open a manifest for collected samples bound for Main Lab',
        'backUrl' => route('pathology.sample_batches.index'),
        'backLabel' => 'Back to transit',
    ])

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="hms-panel hms-panel-padded max-w-xl">
        <form method="POST" action="{{ route('pathology.sample_batches.store') }}">
            @csrf

            <div class="space-y-4 mb-6">
                @if($lockedCenter)
                    <x-form.field label="Collection center" for="collection_center_display">
                        <input type="text" id="collection_center_display" class="hms-input" value="{{ $lockedCenter->code }} — {{ $lockedCenter->name }}" disabled>
                    </x-form.field>
                @elseif($centers)
                    <x-form.field label="Collection center" for="collection_center_id" :required="true" hint="Origin spoke for this manifest">
                        <select id="collection_center_id" name="collection_center_id" class="hms-input" required>
                            <option value="">Select center…</option>
                            @foreach($centers as $cc)
                                <option value="{{ $cc->id }}" @selected((string) old('collection_center_id') === (string) $cc->id)>
                                    {{ $cc->code }} — {{ $cc->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-form.field>
                @endif

                <x-form.field label="Notes" for="notes">
                    <textarea id="notes" name="notes" class="hms-input" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                </x-form.field>
            </div>

            <button type="submit" class="hms-btn hms-btn-primary">Create open batch</button>
        </form>
    </div>
</div>
@endsection
