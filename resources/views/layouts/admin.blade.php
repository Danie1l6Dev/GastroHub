<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Panel del restaurante' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
        <div class="mx-auto grid min-h-screen max-w-7xl lg:grid-cols-[16rem_1fr]">
            <aside class="border-b border-zinc-200 bg-white p-4 lg:border-b-0 lg:border-r">
                <a href="{{ route('admin.dashboard') }}" class="block text-xl font-semibold">GastroHub</a>
                <nav class="mt-6 flex gap-2 overflow-x-auto text-sm font-medium lg:flex-col lg:overflow-visible">
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.dashboard') }}">Resumen</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.categories.index') }}">Categorias</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.products.index') }}">Productos</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.tables.index') }}">Mesas QR</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('home') }}">Ver sitio</a>
                </nav>
            </aside>
            <main class="p-4 sm:p-6 lg:p-8">
                @if (session('status'))
                    <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
