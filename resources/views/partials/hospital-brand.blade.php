@php
    $variant = $variant ?? 'text';
    $class = $class ?? '';
    $branding = app(\App\Services\HospitalBrandingService::class);
    $subtitle = $subtitle ?? config('hospital.tagline');
    $name = config('hospital.name');
    $shortName = config('hospital.short_name');
    $city = config('hospital.city');
    $address = config('hospital.address');
    $phone = config('hospital.phone');
    $email = config('hospital.email');
    $logoUrl = $branding->logoUrl();
    $logoPath = $branding->logoPath();
    $contactLine = collect([$city, $phone, $email])->filter()->implode(' · ');
@endphp

@if ($variant === 'sidebar' || $variant === 'rail')
    <img src="{{ $logoUrl }}" alt="{{ $name }}" class="h-10 w-10 rounded-full object-contain bg-white p-0.5 shadow-md" title="{{ $shortName }}">
@elseif ($variant === 'sidebar-wide')
    <div class="flex flex-col items-center gap-2 text-center px-1">
        <img src="{{ $logoUrl }}" alt="{{ $name }}" class="h-16 w-16 rounded-full object-contain bg-white p-1 shadow-md">
        <div>
            <h1 class="text-sm font-bold tracking-wide leading-tight">{{ $shortName }}</h1>
            <p class="text-xs text-blue-200 mt-1">{{ $city }}</p>
        </div>
    </div>
@elseif ($variant === 'login-hero')
    <div class="flex flex-col items-center">
        <img src="{{ $logoUrl }}" alt="{{ $name }}" class="h-28 w-28 object-contain mb-4 bg-white rounded-full p-2 shadow-lg">
        <h1 class="text-2xl font-bold text-center leading-tight">{{ $shortName }}</h1>
        <p class="text-sm text-blue-200 text-center mt-2">{{ $city }}</p>
        @if($address)
            <p class="text-xs text-blue-100 text-center mt-1 max-w-xs">{{ $address }}</p>
        @endif
    </div>
@elseif ($variant === 'login')
    <div class="flex flex-col items-center mb-6">
        <img src="{{ $logoUrl }}" alt="{{ $name }}" class="h-24 w-24 object-contain mb-3">
        <h1 class="text-lg font-bold text-center text-gray-800 leading-tight">{{ $shortName }}</h1>
        <p class="text-sm text-gray-500 text-center">{{ $city }}</p>
        @if($address)
            <p class="text-xs text-gray-400 text-center mt-1 max-w-xs">{{ $address }}</p>
        @endif
    </div>
@elseif ($variant === 'print-pdf')
    <table style="width:100%; border-bottom: 2px solid #004a99; padding-bottom: 10px; margin-bottom: 15px;">
        <tr>
            <td style="width: 70px; vertical-align: middle;">
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="{{ $name }}" style="height: 55px; width: 55px; object-fit: contain;">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <h1 style="color: #004a99; margin: 0; font-size: 18px; text-transform: uppercase;">{{ $shortName }}</h1>
                <p style="margin: 2px 0 0; font-size: 10px; color: #666;">{{ $subtitle }}</p>
                @if($address)
                    <p style="margin: 2px 0 0; font-size: 9px; color: #666;">{{ $address }}</p>
                @endif
                @if($contactLine)
                    <p style="margin: 2px 0 0; font-size: 9px; color: #666;">{{ $contactLine }}</p>
                @endif
            </td>
        </tr>
    </table>
@elseif ($variant === 'print-html')
    <div class="logo-area" style="display: flex; align-items: center; gap: 12px;">
        <img src="{{ $logoUrl }}" alt="{{ $name }}" style="height: 60px; width: 60px; object-fit: contain;">
        <div>
            <h1>{{ $shortName }}</h1>
            <p>{{ $subtitle }}</p>
            @if($address)
                <p style="font-size: 10px; color: #666; margin: 2px 0 0;">{{ $address }}</p>
            @endif
            @if($contactLine)
                <p style="font-size: 10px; color: #666; margin: 2px 0 0;">{{ $contactLine }}</p>
            @endif
        </div>
    </div>
@else
    <h1 class="{{ $class }}">{{ $name }}</h1>
@endif
