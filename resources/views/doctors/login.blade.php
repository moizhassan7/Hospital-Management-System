<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctor portal · {{ get_setting('hospital_name', config('app.name', 'Hospital')) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @php
        $hospitalName = get_setting('hospital_name', config('app.name', 'Hospital'));
        $hospitalLogo = get_setting('logo');
        $hospitalAddress = get_setting('address');
    @endphp

    <div class="auth-shell">
        <aside class="auth-brand" aria-label="{{ $hospitalName }}">
            <svg class="pointer-events-none absolute -right-24 -bottom-24 size-[420px] text-white/[0.04]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M9 2h6v7h7v6h-7v7H9v-7H2V9h7z" />
            </svg>

            <div class="relative flex items-center gap-3">
                <span class="sidebar-logo size-12 rounded-xl">
                    @if ($hospitalLogo)
                        <img src="{{ asset('storage/' . $hospitalLogo) }}" alt="">
                    @else
                        <x-ui.icon name="cross" class="size-6" />
                    @endif
                </span>
                <div>
                    <p class="font-display text-lg font-extrabold tracking-wide">{{ $hospitalName }}</p>
                    @if ($hospitalAddress)
                        <p class="text-[13px] text-ink-300">{{ $hospitalAddress }}</p>
                    @endif
                </div>
            </div>

            <div class="relative max-w-md">
                <p class="font-display text-[34px] leading-[1.15] font-extrabold tracking-tight">Your patients, appointments and prescriptions.</p>
                <p class="mt-4 text-[15px] leading-relaxed text-ink-300">Sign in with the doctor portal email set on your profile. Staff users continue to use the hospital sign-in.</p>
            </div>
        </aside>

        <main class="auth-panel">
            <div class="auth-card">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="sidebar-logo size-11 rounded-xl border border-line">
                        @if ($hospitalLogo)
                            <img src="{{ asset('storage/' . $hospitalLogo) }}" alt="">
                        @else
                            <x-ui.icon name="cross" class="size-6" />
                        @endif
                    </span>
                    <p class="font-display text-base font-extrabold tracking-wide">{{ $hospitalName }}</p>
                </div>

                <h1 class="font-display text-[26px] font-extrabold tracking-tight">Doctor portal</h1>
                <p class="mt-1.5 text-sm text-fg-muted">Use the portal email and password from your doctor profile.</p>

                @if ($errors->any())
                    <x-ui.alert tone="danger" class="mt-6">{{ $errors->first() }}</x-ui.alert>
                @endif

                <form action="{{ route('doctors.login.post') }}" method="POST" class="mt-6 space-y-4" onsubmit="const b = this.querySelector('button[type=submit]'); b.disabled = true; b.querySelector('[data-label]').textContent = 'Signing in…';">
                    @csrf
                    <x-ui.field label="Email address" for="email">
                        <div class="input-group has-start">
                            <span class="input-affix is-start"><x-ui.icon name="mail" /></span>
                            <input type="email" id="email" name="email" @class(['input h-11', 'is-invalid' => $errors->any()]) placeholder="doctor@hospital.com" value="{{ old('email') }}" autocomplete="username" required autofocus>
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Password" for="password">
                        <div class="input-group has-start has-end">
                            <span class="input-affix is-start"><x-ui.icon name="lock" /></span>
                            <input type="password" id="password" name="password" class="input h-11 pr-16" placeholder="Your password" autocomplete="current-password" required>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-ghost btn-sm h-full" aria-controls="password" aria-pressed="false" data-password-toggle>Show</button>
                            </span>
                        </div>
                    </x-ui.field>

                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">
                        <span data-label>Sign in</span>
                        <x-ui.icon name="arrow-right" />
                    </button>
                </form>

                <p class="mt-6 border-t border-line pt-5 text-[13px] text-fg-muted">
                    Hospital staff? <a href="{{ route('login') }}" class="font-semibold text-fg">Sign in to the workspace</a>.
                </p>
            </div>
        </main>
    </div>

    <script>
        document.querySelector('[data-password-toggle]').addEventListener('click', function () {
            const field = document.getElementById('password');
            const reveal = field.type === 'password';
            field.type = reveal ? 'text' : 'password';
            this.textContent = reveal ? 'Hide' : 'Show';
            this.setAttribute('aria-pressed', String(reveal));
        });
    </script>
</body>

</html>
