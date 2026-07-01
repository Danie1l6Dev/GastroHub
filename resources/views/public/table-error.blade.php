@extends('layouts.app', ['title' => 'Mesa no disponible', 'qrLayout' => true])

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-12">
        <div class="rounded-2xl border border-brand-red/25 bg-brand-red-soft p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-red">Mesa no disponible</p>
            <h1 class="mt-2 text-3xl font-semibold text-brand-ink">No pudimos abrir este QR</h1>
            <p class="mt-3 text-brand-red">{{ $message }}</p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-xl bg-brand-red px-5 py-3 text-sm font-semibold text-white hover:bg-brand-red-dark">Volver al inicio</a>
        </div>
    </section>
@endsection
