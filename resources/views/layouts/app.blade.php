<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'GastroHub') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-white text-brand-ink antialiased">
        @if (session('status'))
            <span data-flash-message class="hidden">{{ session('status') }}</span>
        @endif

        @php($isQrLayout = $qrLayout ?? false)

        <header class="sticky top-0 z-40 border-b border-brand-brown/10 bg-white/90 shadow-sm shadow-brand-ink/[0.03] backdrop-blur-xl">
            @if ($isQrLayout)
                <div class="mx-auto flex max-w-6xl items-center justify-center px-4 py-3">
                    <a href="{{ route('home') }}" class="inline-flex min-h-12 max-w-full items-center justify-center gap-3 rounded-2xl px-3 transition hover:bg-brand-red-soft" aria-label="Ir al inicio del restaurante">
                        @if ($restaurant?->logoUrl())
                            <img src="{{ $restaurant->logoUrl() }}" alt="" class="h-11 w-11 shrink-0 rounded-2xl object-cover">
                        @else
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-red text-sm font-semibold text-white">
                                {{ mb_substr($restaurant->name ?? 'G', 0, 1) }}
                            </span>
                        @endif
                        <span class="max-w-[14rem] truncate text-base font-semibold tracking-tight text-brand-ink sm:max-w-md">
                            {{ $restaurant->name ?? 'GastroHub' }}
                        </span>
                    </a>
                </div>
            @else
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-red text-sm font-semibold text-white shadow-sm shadow-brand-red/20">
                            {{ mb_substr($restaurant->name ?? 'G', 0, 1) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-base font-semibold tracking-tight text-brand-ink">{{ $restaurant->name ?? 'GastroHub' }}</span>
                            <span class="block text-xs font-medium uppercase tracking-[0.16em] text-brand-brown">Menu y mesa digital</span>
                        </span>
                    </a>
                    <nav class="flex items-center gap-1 text-sm font-semibold text-zinc-700">
                        <a class="rounded-xl px-3 py-2 transition hover:bg-brand-orange-soft hover:text-brand-ink {{ request()->routeIs('home') ? 'bg-brand-red text-white hover:bg-brand-red-dark' : '' }}" href="{{ route('home') }}">Inicio</a>
                        <a class="rounded-xl px-3 py-2 transition hover:bg-brand-orange-soft hover:text-brand-ink {{ request()->routeIs('menu') ? 'bg-brand-red text-white hover:bg-brand-red-dark' : '' }}" href="{{ route('menu') }}">Menu</a>
                        <a class="hidden rounded-xl px-3 py-2 transition hover:bg-brand-orange-soft hover:text-brand-ink sm:inline-flex" href="{{ route('admin.dashboard') }}">Panel</a>
                    </nav>
                </div>
            @endif
        </header>

        <main class="gh-reveal">
            @yield('content')
        </main>

        @unless ($isQrLayout)
            <footer class="border-t border-brand-ink bg-brand-ink">
                <div class="mx-auto grid max-w-6xl gap-4 px-4 py-8 text-sm text-zinc-300 sm:grid-cols-[1fr_auto] sm:items-center">
                    <div>
                        <p class="font-semibold text-white">{{ $restaurant->name ?? 'GastroHub' }}</p>
                        <p class="mt-1">Menu digital, pedidos por QR y cuenta compartida.</p>
                    </div>
                    <a href="{{ route('menu') }}" class="gh-btn gh-btn-accent">Ver menu</a>
                </div>
            </footer>
        @endunless
    </body>
</html>
