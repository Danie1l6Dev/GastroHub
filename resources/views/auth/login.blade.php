<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ingreso administrativo</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 text-zinc-950 antialiased">
        <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
            <section class="relative hidden overflow-hidden bg-zinc-950 text-white lg:block">
                <img src="{{ asset('images/restaurant-hero.png') }}" alt="Ambiente de restaurante" class="absolute inset-0 h-full w-full object-cover opacity-55">
                <div class="absolute inset-0 bg-zinc-950/55"></div>
                <div class="relative flex h-full flex-col justify-between p-10">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-sm font-semibold text-zinc-950">GH</span>
                        <span>
                            <span class="block text-xl font-semibold">GastroHub</span>
                            <span class="block text-xs font-medium uppercase tracking-[0.16em] text-zinc-300">Restaurante digital</span>
                        </span>
                    </a>

                    <div class="max-w-xl">
                        <p class="gh-page-kicker text-emerald-300">Panel interno</p>
                        <h1 class="mt-4 text-5xl font-semibold tracking-tight">Gestiona mesas, pedidos y cuenta desde un solo lugar.</h1>
                        <p class="mt-5 text-base leading-7 text-zinc-200">Una experiencia lista para demostrar a restaurantes: QR por mesa, pedidos agrupados y pagos simulados sin friccion.</p>
                    </div>
                </div>
            </section>

            <section class="flex min-h-screen items-center justify-center px-4 py-10">
                <div class="w-full max-w-md">
                    <div class="mb-8 text-center lg:hidden">
                        <a href="{{ route('home') }}" class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-950 text-sm font-semibold text-white">GH</a>
                        <p class="mt-3 text-lg font-semibold">GastroHub</p>
                    </div>

                    <div class="gh-panel p-6 sm:p-8">
                        <p class="gh-page-kicker">Acceso seguro</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Ingreso administrativo</h1>
                        <p class="mt-2 text-sm leading-6 text-zinc-600">Acceso reservado para administrar el restaurante de demostracion.</p>

                        @if (session('status'))
                            <x-alert class="mt-5">{{ session('status') }}</x-alert>
                        @endif

                        @if ($errors->any())
                            <x-alert type="error" class="mt-5">{{ $errors->first() }}</x-alert>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                            @csrf

                            <x-form-input label="Correo electronico" name="email" type="email" required autofocus autocomplete="email" placeholder="admin@restaurante.test" />

                            <div>
                                <label class="gh-label" for="password">Contrasena</label>
                                <div class="relative">
                                    <input id="password" name="password" type="password" required autocomplete="current-password" class="gh-field pr-24">
                                    <button type="button" data-password-toggle="#password" class="absolute right-2 top-2 inline-flex min-h-8 items-center rounded-lg px-3 text-xs font-semibold text-zinc-600 hover:bg-zinc-100">
                                        Mostrar
                                    </button>
                                </div>
                                @error('password')
                                    <p class="gh-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <label class="flex min-h-11 items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-700">
                                <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300 text-emerald-600">
                                Recordar sesion
                            </label>

                            <x-button class="w-full">Ingresar al panel</x-button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
