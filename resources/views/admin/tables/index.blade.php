@extends('layouts.admin', ['title' => 'Mesas QR'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold">Mesas QR</h1>
            <p class="mt-1 text-sm text-zinc-600">Cada mesa tiene una URL estable para su codigo QR.</p>
        </div>
        <a href="{{ route('admin.tables.create') }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Nueva mesa</a>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse ($tables as $table)
            <article class="rounded-md border border-zinc-200 bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $table->name }}</h2>
                        <p class="mt-1 text-sm text-zinc-600">Capacidad: {{ $table->capacity ?: 'Sin definir' }} · {{ $table->is_active ? 'Activa' : 'Inactiva' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.tables.edit', $table) }}" class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Editar</a>
                    </div>
                </div>
                <div class="mt-4 rounded-md bg-zinc-100 p-3 text-xs text-zinc-700">
                    {{ $table->qrUrl() }}
                </div>
                <form method="POST" action="{{ route('admin.tables.destroy', $table) }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Eliminar</button>
                </form>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-zinc-300 bg-white p-5 text-sm text-zinc-600">No hay mesas.</p>
        @endforelse
    </div>
@endsection
