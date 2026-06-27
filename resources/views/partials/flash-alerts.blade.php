@if(session('success'))
    <div class="hms-alert hms-alert-success" role="alert">
        <strong class="font-semibold">Success</strong>
        <span class="ml-1">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="hms-alert hms-alert-error" role="alert">
        <strong class="font-semibold">Error</strong>
        <span class="ml-1">{{ session('error') }}</span>
    </div>
@endif

@if(session('info'))
    <div class="hms-alert hms-alert-info" role="alert">
        <strong class="font-semibold">Info</strong>
        <span class="ml-1">{{ session('info') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="hms-alert hms-alert-error" role="alert">
        <strong class="font-semibold">Please fix the following</strong>
        <ul class="mt-2 list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
