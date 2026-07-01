<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use Illuminate\Support\Collection;

class OrderTicketService
{
    public function __construct(private readonly OrderStatusService $orderStatuses) {}

    /** @return Collection<int, array<string, mixed>> */
    public function tickets(array $filters = []): Collection
    {
        $sessions = TableSession::query()
            ->with(['diningTable', 'orders.items', 'orders.tableGuest'])
            ->whereNotNull('confirmed_at')
            ->whereHas('orders')
            ->when($filters['table_id'] ?? null, function ($query, int|string $tableId): void {
                $query->where('dining_table_id', $tableId);
            })
            ->get();

        return $sessions
            ->flatMap(fn (TableSession $session): Collection => $this->ticketsForSession($session))
            ->when(($filters['date'] ?? 'today') === 'today', function (Collection $tickets): Collection {
                return $tickets->filter(fn (array $ticket): bool => $ticket['placed_at']?->isToday() ?? false);
            })
            ->when($filters['status'] ?? null, function (Collection $tickets, string $status): Collection {
                return $tickets->filter(fn (array $ticket): bool => $ticket['status'] === $status);
            })
            ->sortBy([
                fn (array $a, array $b): int => $this->statusPriority($a['status']) <=> $this->statusPriority($b['status']),
                fn (array $a, array $b): int => ($b['placed_at']?->timestamp ?? 0) <=> ($a['placed_at']?->timestamp ?? 0),
            ])
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function tableGroups(array $filters = []): Collection
    {
        return $this->tickets($filters)
            ->groupBy(fn (array $ticket): int => $ticket['session']->id)
            ->map(function (Collection $tickets): array {
                $firstTicket = $tickets->first();
                $session = $firstTicket['session'];

                return [
                    'id' => 'session-'.$session->id,
                    'session' => $session,
                    'status' => $this->groupStatus($tickets),
                    'placed_at' => $tickets->min('placed_at'),
                    'guest_names' => $tickets
                        ->flatMap(fn (array $ticket): Collection => $ticket['guest_names'])
                        ->unique()
                        ->values(),
                    'sections' => $tickets
                        ->sortBy([
                            fn (array $a, array $b): int => $this->sectionPriority($a['type']) <=> $this->sectionPriority($b['type']),
                            fn (array $a, array $b): int => ($a['placed_at']?->timestamp ?? 0) <=> ($b['placed_at']?->timestamp ?? 0),
                        ])
                        ->values(),
                    'total' => (int) $tickets->sum('total'),
                ];
            })
            ->sortBy([
                fn (array $a, array $b): int => $this->statusPriority($a['status']) <=> $this->statusPriority($b['status']),
                fn (array $a, array $b): int => ($b['placed_at']?->timestamp ?? 0) <=> ($a['placed_at']?->timestamp ?? 0),
            ])
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function ticketsForSession(TableSession $session): Collection
    {
        $orders = $session->orders->sortBy('id')->values();
        $confirmedAt = $session->confirmed_at;

        if (! $confirmedAt) {
            return collect();
        }

        $mainOrders = $orders
            ->filter(fn (Order $order): bool => ! $order->is_additional)
            ->values();
        $extraOrders = $orders
            ->filter(fn (Order $order): bool => (bool) $order->is_additional)
            ->values();

        return collect()
            ->when($mainOrders->isNotEmpty(), function (Collection $tickets) use ($session, $mainOrders): Collection {
                return $tickets->push($this->makeTicket($session, $mainOrders, 'main'));
            })
            ->merge($extraOrders->map(fn (Order $order): array => $this->makeTicket($session, collect([$order]), 'extra', $order)));
    }

    /** @param  Collection<int, Order>  $orders */
    private function makeTicket(TableSession $session, Collection $orders, string $type, ?Order $sourceOrder = null): array
    {
        $status = $this->ticketStatus($orders);
        $allowedTransitions = $this->allowedTransitions($orders);

        return [
            'id' => $type === 'main' ? 'session-'.$session->id.'-main' : 'order-'.$sourceOrder?->id,
            'type' => $type,
            'type_label' => $type === 'main' ? 'Pedido general' : 'Adicional',
            'session' => $session,
            'source_order' => $sourceOrder,
            'orders' => $orders,
            'status' => $status,
            'status_label' => $this->orderStatuses->label($status),
            'allowed_transitions' => $allowedTransitions,
            'placed_at' => $type === 'main'
                ? $session->confirmed_at
                : $sourceOrder?->placed_at,
            'guest_names' => $orders
                ->map(fn (Order $order): ?string => $order->tableGuest?->alias)
                ->filter()
                ->unique()
                ->values(),
            'items' => $this->aggregateItems($orders),
            'total' => (int) $orders->sum(fn (Order $order): int => (int) ($order->subtotal ?: $order->total)),
        ];
    }

    /** @param  Collection<int, Order>  $orders */
    private function ticketStatus(Collection $orders): string
    {
        return $orders
            ->pluck('status')
            ->unique()
            ->sortBy(fn (string $status): int => $this->statusPriority($status))
            ->first() ?? 'new';
    }

    /** @param  Collection<int, Order>  $orders */
    private function allowedTransitions(Collection $orders): array
    {
        return $orders
            ->map(fn (Order $order): array => $this->orderStatuses->allowedTransitions($order))
            ->reduce(function (?array $carry, array $transitions): array {
                if ($carry === null) {
                    return $transitions;
                }

                return array_values(array_intersect($carry, $transitions));
            }) ?? [];
    }

    /** @param  Collection<int, Order>  $orders */
    private function aggregateItems(Collection $orders): Collection
    {
        return $orders
            ->flatMap(fn (Order $order): Collection => $order->items->map(fn (OrderItem $item): array => [
                'product_name' => $item->product_name,
                'unit_price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'line_total' => (int) ($item->line_total ?: $item->subtotal),
                'notes' => $item->notes,
                'guest' => $order->tableGuest?->alias,
            ]))
            ->groupBy(fn (array $item): string => implode('|', [
                mb_strtolower($item['product_name']),
                $item['unit_price'],
                mb_strtolower(trim((string) $item['notes'])),
            ]))
            ->map(function (Collection $items): array {
                $first = $items->first();
                $guestQuantities = $items
                    ->filter(fn (array $item): bool => filled($item['guest']))
                    ->groupBy(fn (array $item): string => (string) $item['guest'])
                    ->map(fn (Collection $guestItems, string $guest): array => [
                        'guest' => $guest,
                        'quantity' => $guestItems->sum('quantity'),
                    ])
                    ->values();

                return [
                    'product_name' => $first['product_name'],
                    'product_sort_key' => mb_strtolower($first['product_name']),
                    'unit_price' => $first['unit_price'],
                    'quantity' => $items->sum('quantity'),
                    'line_total' => $items->sum('line_total'),
                    'notes' => $first['notes'],
                    'guest_names' => $items->pluck('guest')->filter()->unique()->values(),
                    'guest_quantities' => $guestQuantities,
                ];
            })
            ->sortBy([
                fn (array $a, array $b): int => $a['product_sort_key'] <=> $b['product_sort_key'],
                fn (array $a, array $b): int => (bool) $a['notes'] <=> (bool) $b['notes'],
                fn (array $a, array $b): int => strcmp((string) $a['notes'], (string) $b['notes']),
            ])
            ->values();
    }

    private function groupStatus(Collection $tickets): string
    {
        return $tickets
            ->pluck('status')
            ->unique()
            ->sortBy(fn (string $status): int => $this->statusPriority($status))
            ->first() ?? 'new';
    }

    private function statusPriority(string $status): int
    {
        return match ($status) {
            'new' => 0,
            'preparing' => 1,
            'delivered' => 2,
            'cancelled' => 3,
            default => 4,
        };
    }

    private function sectionPriority(string $type): int
    {
        return $type === 'main' ? 0 : 1;
    }
}
