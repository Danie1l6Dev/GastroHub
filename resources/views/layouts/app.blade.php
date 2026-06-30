<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'GastroHub') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-50 text-zinc-950 antialiased">
        @if (session('status'))
            <span data-flash-message class="hidden">{{ session('status') }}</span>
        @endif

        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/85 shadow-sm shadow-zinc-950/[0.03] backdrop-blur-xl">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-zinc-950 text-sm font-semibold text-white">
                        {{ mb_substr($restaurant->name ?? 'G', 0, 1) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-base font-semibold tracking-tight text-zinc-950">{{ $restaurant->name ?? 'GastroHub' }}</span>
                        <span class="block text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">Menu y mesa digital</span>
                    </span>
                </a>
                <nav class="flex items-center gap-1 text-sm font-semibold text-zinc-700">
                    <a class="rounded-xl px-3 py-2 transition hover:bg-zinc-100 {{ request()->routeIs('home') ? 'bg-zinc-950 text-white hover:bg-zinc-900' : '' }}" href="{{ route('home') }}">Inicio</a>
                    <a class="rounded-xl px-3 py-2 transition hover:bg-zinc-100 {{ request()->routeIs('menu') ? 'bg-zinc-950 text-white hover:bg-zinc-900' : '' }}" href="{{ route('menu') }}">Menu</a>
                    <a class="hidden rounded-xl px-3 py-2 transition hover:bg-zinc-100 sm:inline-flex" href="{{ route('admin.dashboard') }}">Panel</a>
                </nav>
            </div>
        </header>

        <main class="gh-reveal">
            @yield('content')
        </main>

        <footer class="border-t border-zinc-200 bg-white">
            <div class="mx-auto grid max-w-6xl gap-4 px-4 py-8 text-sm text-zinc-600 sm:grid-cols-[1fr_auto] sm:items-center">
                <div>
                    <p class="font-semibold text-zinc-950">{{ $restaurant->name ?? 'GastroHub' }}</p>
                    <p class="mt-1">Menu digital, pedidos por QR y cuenta compartida.</p>
                </div>
                <a href="{{ route('menu') }}" class="gh-btn gh-btn-secondary">Ver menu</a>
            </div>
        </footer>
    </body>
</html>
