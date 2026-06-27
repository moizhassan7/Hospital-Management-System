@php
    $variant = $variant ?? 'text';
    $class = $class ?? '';
    $subtitle = $subtitle ?? config('hospital.tagline');
    $name = config('hospital.name');
    $shortName = config('hospital.short_name');
    $city = config('hospital.city');
    $logoUrl = asset(config('hospital.logo'));
    $logoPath = public_path(config('hospital.logo'));
@endphp

@if ($variant === 'sidebar')
    <div class="flex flex-col items-center gap-2 text-center px-1">
        <img src="{{ $logoUrl }}" alt="{{ $name }}" class="h-16 w-16 rounded-full object-contain bg-white p-1 shadow-md">
        <div>
            <h1 class="text-sm font-bold tracking-wide leading-tight">{{ $shortName }}</h1>
            <p class="text-xs text-blue-200 mt-1">{{ $city }}</p>
        </div>
    </div>
@elseif ($variant === 'login')
    <div class="flex flex-col items-center mb-6">
        <img src="{{ $logoUrl }}" alt="{{ $name }}" class="h-24 w-24 object-contain mb-3">
        <h1 class="text-lg font-bold text-center text-gray-800 leading-tight">{{ $shortName }}</h1>
        <p class="text-sm text-gray-500 text-center">{{ $city }}</p>
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
                <p style="margin: 2px 0 0; font-size: 10px; color: #666;">{{ $city }} &middot; {{ $subtitle }}</p>
            </td>
        </tr>
    </table>
@elseif ($variant === 'print-html')
    <div class="logo-area" style="display: flex; align-items: center; gap: 12px;">
        <img src="{{ $logoUrl }}" alt="{{ $name }}" style="height: 60px; width: 60px; object-fit: contain;">
        <div>
            <h1>{{ $shortName }}</h1>
            <p>{{ $city }} &middot; {{ $subtitle }}</p>
        </div>
    </div>
@else
    <h1 class="{{ $class }}">{{ $name }}</h1>
@endif
