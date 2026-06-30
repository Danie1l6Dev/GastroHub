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
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.products.index') }}">Menu</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.tables.index') }}">Mesas</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.orders.index') }}">Pedidos</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.settings.edit') }}">Configuracion</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('admin.categories.index') }}">Categorias</a>
                    <a class="rounded-md px-3 py-2 hover:bg-zinc-100" href="{{ route('home') }}">Ver sitio</a>
                </nav>
                <div class="mt-6 border-t border-zinc-200 pt-4 text-sm">
                    <p class="font-medium text-zinc-900">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-zinc-500">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <x-button variant="secondary" class="w-full">Cerrar sesion</x-button>
                    </form>
                </div>
            </aside>
            <main class="p-4 sm:p-6 lg:p-8">
                @if (session('status'))
                    <x-alert class="mb-6">{{ session('status') }}</x-alert>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
