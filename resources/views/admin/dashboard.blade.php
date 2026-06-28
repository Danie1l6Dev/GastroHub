@extends('layouts.admin', ['title' => 'Panel del restaurante'])

@section('content')
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Panel interno</p>
            <h1 class="mt-2 text-3xl font-semibold">Dashboard administrativo</h1>
            <p class="mt-2 text-sm text-zinc-600">Vista inicial del prototipo. Los indicadores se conectaran al flujo de pedidos en una fase posterior.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <x-stat-card :label="$card['label']" :value="$card['value']" />
        @endforeach
    </div>

    <section class="mt-8 rounded-md border border-zinc-200 bg-white p-5">
        <h2 class="text-lg font-semibold">Accesos del panel</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50" href="{{ route('admin.products.index') }}">Menu</a>
            <a class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50" href="{{ route('admin.tables.index') }}">Mesas</a>
            <a class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-500" href="#">Pedidos</a>
            <a class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-500" href="#">Configuracion</a>
        </div>
    </section>
@endsection
