@extends('layouts.admin', ['title' => 'Mesas QR'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold">Mesas QR</h1>
            <p class="mt-1 text-sm text-zinc-600">Cada mesa tiene un enlace unico y un QR regenerable.</p>
        </div>
        <a href="{{ route('admin.tables.create') }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Nueva mesa</a>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse ($tables as $table)
            @php
                $billing = $billingSummaries[$table->id] ?? null;
            @endphp
            <article class="rounded-md border border-zinc-200 bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $table->name }}</h2>
                        <p class="mt-1 text-sm text-zinc-600">
                            Codigo {{ $table->code }} · Capacidad: {{ $table->capacity ?: 'Sin definir' }} · {{ $table->is_active ? 'Activa' : 'Inactiva' }} · {{ $table->current_status->label() }}
                        </p>
                    </div>
                    <a href="{{ route('admin.tables.edit', $table) }}" class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Editar</a>
                </div>

                <div class="mt-4 rounded-md bg-zinc-100 p-3 text-xs text-zinc-700">
                    {{ $table->qrUrl() }}
                </div>

                @if ($billing)
                    <div class="mt-4 grid gap-3 rounded-md border border-zinc-200 p-3 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Cuenta</p>
                            <p class="mt-1 font-semibold tabular-nums text-zinc-950">{{ $billing['total_formatted'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Pagado</p>
                            <p class="mt-1 font-semibold tabular-nums text-emerald-700">{{ $billing['paid_formatted'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Saldo</p>
                            <p class="mt-1 font-semibold tabular-nums text-amber-700">{{ $billing['balance_formatted'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Estado</p>
                            <p class="mt-1 font-semibold {{ $billing['is_paid'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $billing['is_paid'] ? 'Pagada' : 'Pendiente' }}</p>
                        </div>
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ $table->qrUrl() }}" target="_blank" rel="noopener noreferrer" class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Ver enlace</a>
                    <a href="{{ route('admin.tables.qr.download', $table) }}" class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Descargar QR</a>
                    <a href="{{ route('admin.tables.qr.print', $table) }}" target="_blank" rel="noopener noreferrer" class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Imprimir</a>
                    <form method="POST" action="{{ route('admin.tables.regenerate-token', $table) }}">
                        @csrf
                        <button class="rounded-md border border-amber-200 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">Regenerar token</button>
                    </form>
                    @if ($billing && $billing['is_paid'])
                        <form method="POST" action="{{ route('admin.tables.close-session', $table) }}">
                            @csrf
                            <button class="rounded-md border border-emerald-200 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Cerrar mesa</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.tables.destroy', $table) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Eliminar</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-zinc-300 bg-white p-5 text-sm text-zinc-600">No hay mesas.</p>
        @endforelse
    </div>
@endsection
