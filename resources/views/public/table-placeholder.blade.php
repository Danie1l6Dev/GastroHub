@extends('layouts.app', ['title' => 'Mesa '.$table->name])

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12">
        <div class="rounded-md border border-zinc-200 bg-white p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">QR de mesa</p>
            <h1 class="mt-2 text-3xl font-semibold">{{ $table->name }}</h1>
            <p class="mt-3 text-zinc-600">
                Esta ruta ya identifica la mesa por su codigo QR. En la siguiente fase se agregara el ingreso por alias, pedidos individuales y totales de la mesa.
            </p>
            <a href="{{ route('menu') }}" class="mt-6 inline-flex rounded-md bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Ver menu</a>
        </div>
    </section>
@endsection
