@extends('layouts.admin', ['title' => 'Mesas QR'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Salon</p>
            <h1 class="gh-page-title">Mesas QR</h1>
            <p class="gh-page-copy">Cada mesa tiene un enlace unico, QR descargable y estado operativo visible.</p>
        </div>
        <a href="{{ route('admin.tables.create') }}" class="gh-btn gh-btn-primary">Nueva mesa</a>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse ($tables as $table)
            @php
                $billing = $billingSummaries[$table->id] ?? null;
            @endphp
            <article class="gh-panel gh-card-hover min-w-0">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold">{{ $table->name }}</h2>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                            <span class="font-semibold text-zinc-700">Codigo {{ $table->code }}</span>
                            <span class="text-zinc-400">&middot;</span>
                            <span class="text-zinc-500">Capacidad: {{ $table->capacity ?: 'Sin definir' }}</span>
                            <span class="rounded-full px-2 py-1 font-semibold {{ $table->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-200 text-zinc-700' }}">
                                {{ $table->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                            <span class="rounded-full bg-zinc-100 px-2 py-1 font-semibold text-zinc-700">{{ $table->current_status->label() }}</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.tables.edit', $table) }}" class="gh-btn gh-btn-secondary min-h-10 w-full px-3 sm:w-auto">Editar</a>
                </div>

                <div class="mt-4 break-all rounded-2xl bg-zinc-100 p-3 text-xs leading-5 text-zinc-700">
                    {{ $table->qrUrl() }}
                </div>

                @if ($billing)
                    <div class="mt-4 grid gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Cuenta</p>
                            <p class="mt-1 font-semibold tabular-nums text-zinc-950">{{ $billing['total_formatted'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Por cobrar</p>
                            <p class="mt-1 font-semibold tabular-nums text-amber-700">{{ $billing['balance_formatted'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Pedidos</p>
                            <p class="mt-1 font-semibold {{ $billing['payment_ready'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $billing['payment_ready'] ? 'Entregados' : 'En proceso' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Estado</p>
                            <p class="mt-1 font-semibold {{ $billing['is_paid'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $billing['is_paid'] ? 'Pago confirmado' : 'Pendiente de cobro' }}</p>
                        </div>
                    </div>
                @endif

                <div class="mt-4 grid gap-2 sm:flex sm:flex-wrap">
                    <a href="{{ $table->qrUrl() }}" target="_blank" rel="noopener noreferrer" class="gh-btn gh-btn-secondary min-h-10 px-3">Ver enlace</a>
                    <a href="{{ route('admin.tables.qr.download', $table) }}" class="gh-btn gh-btn-secondary min-h-10 px-3">Descargar QR</a>
                    <a href="{{ route('admin.tables.qr.print', $table) }}" target="_blank" rel="noopener noreferrer" class="gh-btn gh-btn-secondary min-h-10 px-3">Imprimir</a>
                    <form method="POST" action="{{ route('admin.tables.regenerate-token', $table) }}">
                        @csrf
                        <button class="gh-btn min-h-10 w-full border border-amber-200 bg-white px-3 text-amber-800 hover:bg-amber-50 sm:w-auto" data-confirm="Regenerar el QR cerrara la sesion activa de esta mesa. Continuar?">Regenerar token</button>
                    </form>
                    @if ($billing && ($billing['payment_ready'] || $billing['is_paid']))
                        <form method="POST" action="{{ route('admin.tables.close-session', $table) }}">
                            @csrf
                            <button class="gh-btn min-h-10 w-full border border-emerald-200 bg-white px-3 text-emerald-800 hover:bg-emerald-50 sm:w-auto" data-confirm="Confirmar el pago recibido y cerrar esta mesa?">Confirmar pago y cerrar mesa</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.tables.destroy', $table) }}">
                        @csrf
                        @method('DELETE')
                        <button class="gh-btn gh-btn-danger min-h-10 w-full px-3 sm:w-auto" data-confirm="Eliminar esta mesa?">Eliminar</button>
                    </form>
                </div>
            </article>
        @empty
            <x-empty-state title="No hay mesas" description="Crea mesas para generar enlaces QR." class="lg:col-span-2" />
        @endforelse
    </div>
@endsection
