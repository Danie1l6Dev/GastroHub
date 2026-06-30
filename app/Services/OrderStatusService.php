<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'new' => ['preparing', 'cancelled'],
        'preparing' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    /** @return array<string, string> */
    public function labels(): array
    {
        return [
            'new' => 'Nuevo',
            'preparing' => 'Preparando',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];
    }

    /** @return list<string> */
    public function statuses(): array
    {
        return array_keys($this->labels());
    }

    public function label(string $status): string
    {
        return $this->labels()[$status] ?? ucfirst($status);
    }

    /** @return list<string> */
    public function allowedTransitions(Order $order): array
    {
        return self::TRANSITIONS[$order->status] ?? [];
    }

    public function transition(Order $order, string $nextStatus): void
    {
        if (! in_array($nextStatus, $this->allowedTransitions($order), true)) {
            throw ValidationException::withMessages([
                'status' => 'La transicion de estado no es valida para este pedido.',
            ]);
        }

        $order->forceFill(['status' => $nextStatus])->save();
    }

    /** @param  iterable<Order>|Collection<int, Order>  $orders */
    public function transitionMany(iterable $orders, string $nextStatus): void
    {
        $orders = collect($orders);

        if ($orders->isEmpty()) {
            throw ValidationException::withMessages([
                'status' => 'No hay pedidos para actualizar.',
            ]);
        }

        $orders->each(function (Order $order) use ($nextStatus): void {
            if (! in_array($nextStatus, $this->allowedTransitions($order), true)) {
                throw ValidationException::withMessages([
                    'status' => 'La transicion de estado no es valida para este pedido.',
                ]);
            }
        });

        Order::query()
            ->whereKey($orders->pluck('id'))
            ->update(['status' => $nextStatus]);
    }
}
