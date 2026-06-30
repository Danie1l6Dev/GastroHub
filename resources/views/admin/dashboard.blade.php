@extends('layouts.admin', ['title' => 'Dashboard'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Panel interno</p>
            <h1 class="gh-page-title">Operacion del restaurante</h1>
            <span class="sr-only">Dashboard administrativo</span>
            <p class="gh-page-copy">Resumen de pedidos, mesas y ventas simuladas con datos reales del prototipo.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="gh-btn gh-btn-primary">Ver pedidos</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($cards as $card)
            <x-stat-card :label="$card['label']" :value="$card['value']" :hint="$card['hint'] ?? null" :tone="$card['tone'] ?? 'neutral'" />
        @endforeach
    </div>

    <div class="mt-8 grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="gh-panel">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Pedidos recientes</h2>
                    <p class="mt-1 text-sm text-zinc-500">Ultimos pedidos registrados en mesa.</p>
                </div>
                <a href="{{ route('admin.orders.index', ['date' => 'all']) }}" class="text-sm font-semibold text-emerald-700">Todos</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentOrders as $order)
                    <article class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-zinc-950">{{ $order->tableSession?->diningTable?->name ?? 'Mesa' }} · {{ $order->tableGuest?->alias ?? 'Cliente' }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $order->placed_at?->format('d/m/Y H:i') ?? 'Sin hora' }} · {{ $order->items->count() }} productos</p>
                            </div>
                            <p class="gh-price">${{ number_format((int) ($order->subtotal ?: $order->total), 0, ',', '.') }}</p>
                        </div>
                    </article>
                @empty
                    <x-empty-state title="Sin pedidos recientes" description="Cuando una mesa confirme pedidos, apareceran aqui." />
                @endforelse
            </div>
        </section>

        <section class="gh-panel">
            <h2 class="text-lg font-semibold">Estado de mesas</h2>
            <p class="mt-1 text-sm text-zinc-500">Disponibilidad operativa por estado.</p>
            <div class="mt-5 space-y-3">
                @foreach ([
                    'available' => ['label' => 'Disponibles', 'tone' => 'success'],
                    'occupied' => ['label' => 'Ocupadas', 'tone' => 'warning'],
                    'payment_pending' => ['label' => 'Pendiente de pago', 'tone' => 'info'],
                ] as $status => $meta)
                    <div class="flex items-center justify-between rounded-2xl border border-zinc-200 bg-white p-4">
                        <x-badge :tone="$meta['tone']">{{ $meta['label'] }}</x-badge>
                        <span class="text-2xl font-semibold tabular-nums">{{ (int) ($tableStatusCounts[$status] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
