<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ingreso administrativo</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-10">
            <section class="w-full rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Panel interno</p>
                <h1 class="mt-2 text-3xl font-semibold">Ingreso administrativo</h1>
                <p class="mt-2 text-sm text-zinc-600">Acceso reservado para el administrador del restaurante.</p>

                @if (session('status'))
                    <x-alert class="mt-5">{{ session('status') }}</x-alert>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <x-form-input label="Correo electronico" name="email" type="email" required autofocus autocomplete="email" />
                    <x-form-input label="Contrasena" name="password" type="password" required autocomplete="current-password" />

                    <label class="flex items-center gap-2 text-sm text-zinc-700">
                        <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300">
                        Recordar sesion
                    </label>

                    <x-button class="w-full justify-center">Ingresar</x-button>
                </form>
            </section>
        </main>
    </body>
</html>
