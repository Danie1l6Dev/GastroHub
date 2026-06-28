@extends('layouts.app', ['title' => 'Mesa no disponible'])

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-12">
        <div class="rounded-md border border-red-200 bg-red-50 p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-700">Mesa no disponible</p>
            <h1 class="mt-2 text-3xl font-semibold text-red-950">No pudimos abrir este QR</h1>
            <p class="mt-3 text-red-900">{{ $message }}</p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-md bg-red-700 px-5 py-3 text-sm font-semibold text-white hover:bg-red-800">Volver al inicio</a>
        </div>
    </section>
@endsection
