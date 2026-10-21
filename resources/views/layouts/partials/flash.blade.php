{{-- Session feedback for every page. Messages stay until dismissed: some carry reference numbers. --}}
<div class="flash-region" aria-live="polite">
    @if (session('success'))
        <x-ui.alert tone="success" dismissible>{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('status'))
        <x-ui.alert tone="info" dismissible>{{ session('status') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert tone="danger" dismissible>{{ session('error') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert tone="danger" title="Some details need attention" dismissible>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif
</div>
