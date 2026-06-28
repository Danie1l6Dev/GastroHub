<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>QR {{ $table->name }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white text-zinc-950">
        <main class="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center gap-5 p-8 text-center">
            <h1 class="text-3xl font-semibold">{{ $table->name }}</h1>
            <p class="text-sm text-zinc-600">Escanea para ingresar a la mesa.</p>
            <div class="w-80 max-w-full">{!! $qrSvg !!}</div>
            <p class="break-all rounded-md bg-zinc-100 p-3 text-xs text-zinc-600">{{ $table->qrUrl() }}</p>
            <button onclick="window.print()" class="rounded-md bg-zinc-950 px-5 py-3 text-sm font-semibold text-white print:hidden">Imprimir</button>
        </main>
    </body>
</html>
