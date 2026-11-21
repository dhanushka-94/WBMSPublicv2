<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>@yield('title') — Dunsinane Water</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Fraunces', 'Georgia', 'serif'],
                        sans: ['Figtree', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        water: {
                            50: '#eef9fd',
                            100: '#d6f1fb',
                            200: '#aee2f6',
                            400: '#3bb8e8',
                            500: '#00adef',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#0c4a6e',
                            900: '#0b3b57',
                        },
                        leaf: {
                            500: '#82bc41',
                            600: '#6aa032',
                            700: '#528028',
                        },
                        earth: {
                            500: '#c4783a',
                            600: '#a65f2a',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        body {
            background:
                radial-gradient(900px 420px at 0% -5%, rgba(0, 173, 239, 0.18), transparent 55%),
                radial-gradient(700px 380px at 100% 5%, rgba(130, 188, 65, 0.12), transparent 50%),
                linear-gradient(180deg, #eaf7fc 0%, #f3fafd 42%, #eef6f8 100%);
            min-height: 100vh;
        }
        .water-band {
            height: 6px;
            background: linear-gradient(90deg, #00adef 0%, #36b5e0 35%, #82bc41 70%, #c4783a 100%);
        }
        .fade-up {
            animation: fadeUp 0.45s ease-out both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans text-water-900 antialiased">
    <div class="water-band"></div>

    <header class="px-4 pt-6 pb-2">
        <div class="mx-auto flex max-w-lg flex-col items-center text-center fade-up">
            <img
                src="{{ asset('images/wassip-logo.jpg') }}"
                alt="Water Supply and Sanitation Improvement Project"
                class="h-20 w-auto object-contain drop-shadow-sm sm:h-24"
            >
            <p class="mt-3 font-display text-xl leading-tight text-water-900">Dunsinane Water</p>
            <p class="mt-0.5 text-xs tracking-wide text-water-700/80">Supply Scheme · Customer notice</p>
        </div>
    </header>

    <main class="mx-auto max-w-lg px-4 pb-8 pt-3">
        <div class="fade-up" style="animation-delay: 60ms">
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-water-200/80 bg-white/80 px-4 py-7 backdrop-blur-sm">
        <div class="mx-auto flex max-w-lg flex-col items-center text-center">
            <p class="text-sm font-medium text-water-900">Pay at the Dunsinane Water Supply Scheme office</p>
            <p class="mt-1 max-w-sm text-xs leading-relaxed text-water-700/80">
                Keep your receipt. This secure link shows only your account details.
            </p>

            <div class="mt-5 w-full rounded-3xl border border-water-100 bg-gradient-to-b from-water-50 to-white px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-water-600">Hotline</p>
                <a href="tel:{{ config('app.project.hotline_tel') }}"
                   class="mt-1 inline-block font-display text-2xl tracking-wide text-water-800 hover:text-water-900">
                    {{ config('app.project.hotline') }}
                </a>
                <p class="mt-1 text-xs text-water-700/75">{{ config('app.project.name') }}</p>
            </div>

            <div class="mt-5 space-y-1 text-xs text-water-700/70">
                <p>AquaBill by olexto</p>
                <p>{{ config('app.developer.name') }}</p>
                <p>
                    <a href="tel:{{ preg_replace('/\D+/', '', config('app.developer.phone')) }}" class="hover:text-water-900">{{ config('app.developer.phone') }}</a>
                    <span class="mx-1.5 text-water-300">·</span>
                    <a href="mailto:{{ config('app.developer.email') }}" class="hover:text-water-900">{{ config('app.developer.email') }}</a>
                    <span class="mx-1.5 text-water-300">·</span>
                    <a href="{{ config('app.developer.website') }}" target="_blank" rel="noopener noreferrer" class="hover:text-water-900">{{ parse_url(config('app.developer.website'), PHP_URL_HOST) }}</a>
                </p>
            </div>

            <p class="mt-4 text-[11px] text-water-700/55">© {{ date('Y') }} AquaBill by olexto · v{{ config('changelog.0.version') }}</p>
        </div>
    </footer>
</body>
</html>
