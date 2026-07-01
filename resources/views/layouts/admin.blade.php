<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Panel del restaurante' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-stone text-brand-ink antialiased">
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
        <details data-admin-mobile-menu class="sticky top-0 z-50 border-b border-brand-brown/15 bg-white/95 shadow-sm shadow-brand-ink/[0.03] backdrop-blur-xl lg:hidden">
            <summary data-admin-menu-toggle class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 [&::-webkit-details-marker]:hidden">
                <span class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-red text-sm font-semibold text-white">GH</span>
                    <span class="min-w-0">
                        <span class="block truncate text-base font-semibold tracking-tight">GastroHub</span>
                        <span class="block text-xs font-medium uppercase tracking-[0.16em] text-brand-brown">Panel restaurante</span>
                    </span>
                </span>
                <span class="rounded-xl border border-brand-brown/20 bg-white px-3 py-2 text-sm font-semibold text-brand-ink shadow-sm">Menu</span>
            </summary>
            <div data-admin-menu-backdrop class="fixed inset-0 top-16 z-40 bg-brand-ink/40"></div>
            <div class="fixed inset-x-3 top-20 z-50 max-h-[calc(100dvh-6rem)] overflow-y-auto rounded-3xl border border-brand-brown/20 bg-white p-3 shadow-2xl shadow-brand-ink/20">
                <nav class="grid gap-2 text-sm font-semibold">
                    @foreach ($navItems as $item)
                        <a
                            class="min-h-11 rounded-2xl px-4 py-3 transition {{ request()->routeIs($item['active']) ? 'bg-brand-red text-white shadow-sm shadow-brand-red/20' : 'text-zinc-700 hover:bg-brand-orange-soft hover:text-brand-ink' }}"
                            href="{{ route($item['route']) }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <a class="min-h-11 rounded-2xl px-4 py-3 text-zinc-700 transition hover:bg-brand-orange-soft hover:text-brand-ink" href="{{ route('home') }}">Ver sitio</a>
                </nav>
                <div class="mt-3 rounded-2xl bg-brand-white p-3 text-sm">
                    <p class="font-medium text-brand-ink">{{ auth()->user()->name }}</p>
                    <p class="mt-1 break-all text-brand-brown-dark">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <x-button variant="secondary" class="w-full">Cerrar sesion</x-button>
                    </form>
                </div>
            </div>
        </details>

        <div class="mx-auto grid min-h-screen max-w-[90rem] lg:grid-cols-[17rem_1fr]">
            <aside class="sticky top-0 z-30 hidden bg-brand-ink p-4 text-white shadow-sm shadow-brand-ink/[0.03] lg:block lg:h-screen lg:shadow-none">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-red text-sm font-semibold text-white">GH</span>
                    <span>
                        <span class="block text-xl font-semibold tracking-tight">GastroHub</span>
                        <span class="block text-xs font-medium uppercase tracking-[0.16em] text-brand-orange">Panel restaurante</span>
                    </span>
                </a>
                <nav class="mt-6 flex flex-col gap-2 text-sm font-medium">
                    @foreach ($navItems as $item)
                        <a
                            class="rounded-xl px-3 py-2 transition {{ request()->routeIs($item['active']) ? 'bg-brand-red text-white shadow-sm shadow-brand-red/20' : 'text-zinc-300 hover:bg-white/10 hover:text-white' }}"
                            href="{{ route($item['route']) }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <a class="rounded-xl px-3 py-2 text-zinc-300 transition hover:bg-white/10 hover:text-white" href="{{ route('home') }}">Ver sitio</a>
                </nav>
                <div class="mt-6 hidden border-t border-white/10 pt-4 text-sm lg:block">
                    <p class="font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-zinc-400">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <x-button variant="secondary" class="w-full">Cerrar sesion</x-button>
                    </form>
                </div>
            </aside>
            <main class="min-w-0 p-4 sm:p-6 lg:p-8">
                <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm shadow-brand-ink/[0.03] backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-brown">Administracion</p>
                        <h1 class="mt-1 text-xl font-semibold tracking-tight text-brand-ink">{{ $title ?? 'Panel del restaurante' }}</h1>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="rounded-xl bg-brand-orange-soft px-3 py-2 font-medium text-brand-ink">{{ auth()->user()->name }}</span>
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
