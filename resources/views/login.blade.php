<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in · {{ get_setting('hospital_name', config('app.name', 'Hospital')) }}</title>

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
                <p class="font-display text-[34px] leading-[1.15] font-extrabold tracking-tight">Every patient, admission and report in one workspace.</p>
                <p class="mt-4 text-[15px] leading-relaxed text-ink-300">Registration, OPD, indoor care, emergency, diagnostics, store and finance for the hospital team.</p>
            </div>

            <div class="relative text-[13px] text-ink-300">
                <p class="font-semibold text-white">Powered by Switch2itech</p>
                <p class="mt-1">For inquiries and information, email <a href="mailto:support@switch2itech.com" class="font-semibold text-white underline-offset-2 hover:underline">support@switch2itech.com</a></p>
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

                <h1 class="font-display text-[26px] font-extrabold tracking-tight">Sign in</h1>
                <p class="mt-1.5 text-sm text-fg-muted">Use the email address and password your administrator gave you.</p>

                @if ($errors->any())
                    <x-ui.alert tone="danger" class="mt-6">{{ $errors->first() }}</x-ui.alert>
                @endif

                <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-4" onsubmit="const b = this.querySelector('button[type=submit]'); b.disabled = true; b.querySelector('[data-label]').textContent = 'Signing in…';">
                    @csrf
                    <x-ui.field label="Email address" for="email">
                        <div class="input-group has-start">
                            <span class="input-affix is-start"><x-ui.icon name="mail" /></span>
                            <input type="email" id="email" name="email" @class(['input h-11', 'is-invalid' => $errors->any()]) placeholder="name@hospital.com" value="{{ old('email') }}" autocomplete="username" required autofocus>
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
                    Forgot your password or need an account? Contact your system administrator.
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
