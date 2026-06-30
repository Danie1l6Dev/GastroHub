<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Panel del restaurante' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 text-zinc-950 antialiased">
        @if (session('status'))
            <span data-flash-message class="hidden">{{ session('status') }}</span>
        @endif
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
        <div class="mx-auto grid min-h-screen max-w-[90rem] lg:grid-cols-[17rem_1fr]">
            <aside class="sticky top-0 z-30 border-b border-zinc-200 bg-white/95 p-4 shadow-sm shadow-zinc-950/[0.03] backdrop-blur-xl lg:h-screen lg:border-b-0 lg:border-r lg:shadow-none">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-zinc-950 text-sm font-semibold text-white">GH</span>
                    <span>
                        <span class="block text-xl font-semibold tracking-tight">GastroHub</span>
                        <span class="block text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">Panel restaurante</span>
                    </span>
                </a>
                <nav class="mt-6 flex gap-2 overflow-x-auto pb-1 text-sm font-medium lg:flex-col lg:overflow-visible lg:pb-0">
                    @foreach ($navItems as $item)
                        <a
                            class="shrink-0 rounded-xl px-3 py-2 transition {{ request()->routeIs($item['active']) ? 'bg-zinc-950 text-white shadow-sm shadow-zinc-950/10' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950' }}"
                            href="{{ route($item['route']) }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <a class="shrink-0 rounded-xl px-3 py-2 text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-950" href="{{ route('home') }}">Ver sitio</a>
                </nav>
                <div class="mt-6 hidden border-t border-zinc-200 pt-4 text-sm lg:block">
                    <p class="font-medium text-zinc-900">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-zinc-500">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <x-button variant="secondary" class="w-full">Cerrar sesion</x-button>
                    </form>
                </div>
            </aside>
            <main class="min-w-0 p-4 sm:p-6 lg:p-8">
                <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-white/70 bg-white/75 p-4 shadow-sm shadow-zinc-950/[0.03] backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Administracion</p>
                        <h1 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950">{{ $title ?? 'Panel del restaurante' }}</h1>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="rounded-xl bg-zinc-100 px-3 py-2 font-medium text-zinc-700">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                            @csrf
                            <x-button variant="secondary">Salir</x-button>
                        </form>
                    </div>
                </div>

                @if (session('status'))
                    <x-alert class="mb-6">{{ session('status') }}</x-alert>
                @endif

                <div class="gh-reveal">
                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>
