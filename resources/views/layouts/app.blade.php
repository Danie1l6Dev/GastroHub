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
        <header class="border-b border-zinc-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('home') }}" class="text-xl font-semibold tracking-normal text-zinc-950">
                    {{ $restaurant->name ?? 'GastroHub' }}
                </a>
                <nav class="flex flex-wrap items-center gap-2 text-sm font-medium text-zinc-700">
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('home') }}">Inicio</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('menu') }}">Menu</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.dashboard') }}">Panel</a>
                </nav>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="border-t border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-zinc-600 sm:flex-row sm:items-center sm:justify-between">
                <p>{{ $restaurant->name ?? 'GastroHub' }}</p>
                <p>Menu digital y pedidos de mesa para prototipo.</p>
            </div>
        </footer>
    </body>
</html>
