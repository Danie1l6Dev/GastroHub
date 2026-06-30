<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Panel del restaurante' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
        @php
            $navItems = [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                ['label' => 'Menu', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
                ['label' => 'Categorias', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
                ['label' => 'Mesas', 'route' => 'admin.tables.index', 'active' => 'admin.tables.*'],
                ['label' => 'Pedidos', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
                ['label' => 'Configuracion', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*'],
            ];
        @endphp
        <div class="mx-auto grid min-h-screen max-w-7xl lg:grid-cols-[16rem_1fr]">
            <aside class="border-b border-zinc-200 bg-white/95 p-4 shadow-sm shadow-zinc-950/[0.03] lg:border-b-0 lg:border-r lg:shadow-none">
                <a href="{{ route('admin.dashboard') }}" class="block text-xl font-semibold tracking-tight">GastroHub</a>
                <p class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">Panel restaurante</p>
                <nav class="mt-6 flex gap-2 overflow-x-auto pb-1 text-sm font-medium lg:flex-col lg:overflow-visible lg:pb-0">
                    @foreach ($navItems as $item)
                        <a
                            class="shrink-0 rounded-md px-3 py-2 transition {{ request()->routeIs($item['active']) ? 'bg-zinc-950 text-white shadow-sm' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950' }}"
                            href="{{ route($item['route']) }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <a class="shrink-0 rounded-md px-3 py-2 text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-950" href="{{ route('home') }}">Ver sitio</a>
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
