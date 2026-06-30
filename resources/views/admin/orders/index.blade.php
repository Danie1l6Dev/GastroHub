@extends('layouts.admin', ['title' => 'Pedidos'])

@section('content')
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Cocina y servicio</p>
            <h1 class="mt-2 text-3xl font-semibold">Mesas con pedido</h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-600">Cada mesa muestra su pedido general y, debajo, los adicionales confirmados despues.</p>
        </div>
        <a href="{{ route('admin.orders.index', request()->query()) }}" class="inline-flex min-h-11 items-center justify-center rounded-md border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-zinc-50">
            Actualizar
        </a>
    </div>

    @if ($errors->any())
        <x-alert type="error" class="mt-6">
            {{ $errors->first() }}
        </x-alert>
    @endif

    <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-6 grid gap-3 rounded-md border border-zinc-200 bg-white p-4 lg:grid-cols-[1fr_1fr_1fr_auto_auto]">
        <label class="text-sm font-medium text-zinc-700">
            <span class="mb-1 block">Estado</span>
            <select name="status" class="min-h-11 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos los estados</option>
                @foreach ($orderStatuses->labels() as $status => $label)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="text-sm font-medium text-zinc-700">
            <span class="mb-1 block">Mesa</span>
            <select name="table_id" class="min-h-11 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                <option value="">Todas las mesas</option>
                @foreach ($tables as $table)
                    <option value="{{ $table->id }}" @selected((string) ($filters['table_id'] ?? '') === (string) $table->id)>{{ $table->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="text-sm font-medium text-zinc-700">
            <span class="mb-1 block">Fecha</span>
            <select name="date" class="min-h-11 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                <option value="today" @selected(($filters['date'] ?? 'today') === 'today')>Hoy</option>
                <option value="all" @selected(($filters['date'] ?? 'today') === 'all')>Todos</option>
            </select>
        </label>

        <button class="min-h-11 self-end rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Filtrar</button>
        <a href="{{ route('admin.orders.index') }}" class="inline-flex min-h-11 items-center justify-center self-end rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Limpiar</a>
    </form>

    <div class="mt-5 flex flex-col gap-2 text-sm text-zinc-600 sm:flex-row sm:items-center sm:justify-between">
        <p><span class="font-semibold text-zinc-950">{{ $tableGroups->count() }}</span> mesas encontradas</p>
        <p>Dentro de cada mesa, los adicionales quedan como secciones separadas.</p>
    </div>

    <section class="mt-4 grid gap-4">
        @forelse ($tableGroups as $group)
            @php
                $session = $group['session'];
            @endphp

            <article class="overflow-hidden rounded-md border border-zinc-200 bg-white">
                <div class="grid gap-4 border-b border-zinc-100 bg-zinc-50/70 p-4 xl:grid-cols-[1fr_auto] xl:items-center">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-600">{{ $group['id'] }}</span>
                            <span class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">{{ $group['sections']->count() }} secciones</span>
                        </div>
                        <div class="mt-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:gap-3">
                            <h2 class="text-2xl font-semibold text-zinc-950">{{ $session->diningTable?->name ?? 'Mesa sin nombre' }}</h2>
                            <p class="pb-1 text-sm text-zinc-600">{{ $group['guest_names']->join(', ') ?: 'Sin participantes' }}</p>
                        </div>
                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">
                            Confirmada {{ $session->confirmed_at?->format('d/m/Y H:i') ?? 'sin hora' }}
                        </p>
                    </div>

                    <div class="xl:text-right">
                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">Total mesa</p>
                        <p class="mt-1 text-3xl font-semibold tabular-nums text-zinc-950">${{ number_format((int) $group['total'], 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="grid gap-4 p-4">
                    @foreach ($group['sections'] as $ticket)
                        @php
                            $badgeClasses = [
                                'new' => 'bg-amber-100 text-amber-900 ring-amber-200',
                                'preparing' => 'bg-sky-100 text-sky-900 ring-sky-200',
                                'delivered' => 'bg-emerald-100 text-emerald-900 ring-emerald-200',
                                'cancelled' => 'bg-zinc-100 text-zinc-600 ring-zinc-200',
                            ][$ticket['status']] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-200';
                            $typeClasses = $ticket['type'] === 'extra'
                                ? 'border-orange-200 bg-orange-50 text-orange-800'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-800';
                        @endphp

                        <section class="rounded-md border border-zinc-200">
                            <div class="grid gap-3 border-b border-zinc-100 px-3 py-3 lg:grid-cols-[1fr_auto] lg:items-center">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $badgeClasses }}">{{ $ticket['status_label'] }}</span>
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $typeClasses }}">{{ $ticket['type_label'] }}</span>
                                    <span class="text-xs font-medium text-zinc-500">{{ $ticket['id'] }}</span>
                                    <span class="text-xs text-zinc-500">{{ $ticket['placed_at']?->format('d/m/Y H:i') ?? 'Sin hora' }}</span>
                                </div>

                                <p class="text-lg font-semibold tabular-nums text-zinc-950 lg:text-right">${{ number_format((int) $ticket['total'], 0, ',', '.') }}</p>
                            </div>

                            <div class="grid gap-4 p-3 xl:grid-cols-[1fr_14rem] xl:items-start">
                                <div class="grid gap-2">
                                    @foreach ($ticket['items'] as $item)
                                        <div class="rounded-md bg-zinc-50 px-3 py-2">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-zinc-950">{{ $item['quantity'] }} x {{ $item['product_name'] }}</p>
                                                    <p class="mt-1 text-xs text-zinc-500">
                                                        ${{ number_format((int) $item['unit_price'], 0, ',', '.') }} c/u
                                                        @if ($item['guest_quantities']->isNotEmpty())
                                                            &middot; {{ $item['guest_quantities']->map(fn ($guest) => $guest['quantity'].'x '.$guest['guest'])->join(', ') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <p class="shrink-0 text-sm font-semibold tabular-nums text-zinc-900">${{ number_format((int) $item['line_total'], 0, ',', '.') }}</p>
                                            </div>
                                            @if ($item['notes'])
                                                <p class="mt-2 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-sm text-amber-900">Nota: {{ $item['notes'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-wrap gap-2 xl:justify-end">
                                    @forelse ($ticket['allowed_transitions'] as $nextStatus)
                                        <form method="POST" action="{{ $ticket['type'] === 'main' ? route('admin.orders.sessions.main.status', $session) : route('admin.orders.status', $ticket['source_order']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $nextStatus }}">
                                            <button class="min-h-10 rounded-md border px-3 py-2 text-sm font-semibold transition {{ $nextStatus === 'cancelled' ? 'border-red-200 text-red-700 hover:bg-red-50' : 'border-zinc-200 text-zinc-800 hover:bg-zinc-50' }}">
                                                {{ $nextStatus === 'preparing' ? 'Preparar' : ($nextStatus === 'delivered' ? 'Entregar' : 'Cancelar') }}
                                            </button>
                                        </form>
                                    @empty
                                        <span class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-500">Sin acciones</span>
                                    @endforelse
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="rounded-md border border-zinc-200 bg-white p-8 text-center">
                <p class="text-lg font-semibold text-zinc-950">No hay mesas para estos filtros.</p>
                <p class="mt-2 text-sm text-zinc-600">Los pedidos generales aparecen aqui cuando el encargado confirma toda la mesa.</p>
            </div>
        @endforelse
    </section>
@endsection
