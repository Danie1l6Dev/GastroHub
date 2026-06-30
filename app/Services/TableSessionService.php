<?php

namespace App\Services;

use App\Enums\TableAccountMode;
use App\Enums\TableStatus;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\TableGuest;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;

class TableSessionService
{
    public function __construct(private readonly TableBillingService $billingService) {}

    public function openSessionFor(DiningTable $table): TableSession
    {
        return DB::transaction(function () use ($table): TableSession {
            abort_if(
                $table->sessions()->where('status', 'payment_pending')->exists(),
                422,
                'Esta mesa ya esta pagada y espera cierre del restaurante.'
            );

            $session = TableSession::firstOrCreate(
                ['dining_table_id' => $table->id, 'status' => 'open'],
                ['opened_at' => now()]
            );

            if ($table->current_status === TableStatus::Available) {
                $table->update(['current_status' => TableStatus::Occupied]);
            }

            return $session;
        });
    }

    public function chooseAccountMode(DiningTable $table, TableAccountMode $accountMode): TableSession
    {
        return DB::transaction(function () use ($table, $accountMode): TableSession {
            $session = $this->openSessionFor($table);

            abort_if($session->guests()->exists(), 422, 'La mesa ya tiene personas registradas.');

            $session->update(['account_mode' => $accountMode]);

            return $session;
        });
    }

    public function join(DiningTable $table, string $alias, ?string $guestToken = null): TableGuest
    {
        return DB::transaction(function () use ($table, $alias, $guestToken): TableGuest {
            $session = $this->openSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');
            abort_if($session->confirmed_at, 422, 'Este pedido ya fue confirmado.');

            $guest = $guestToken
                ? TableGuest::where('guest_token', $guestToken)->where('table_session_id', $session->id)->first()
                : null;

            if ($guest) {
                $guest->update(['alias' => $alias, 'status' => 'active']);

                return $guest;
            }

            abort_if(
                $session->account_mode === TableAccountMode::Joint && $session->guests()->exists(),
                422,
                'Esta mesa usa pago en conjunto y ya tiene una persona encargada del pedido.'
            );

            return $session->guests()->create([
                'alias' => $alias,
                'status' => 'active',
            ]);
        });
    }

    public function changeItem(DiningTable $table, TableGuest $guest, Product $product, int $delta, ?string $notes = null): void
    {
        if ($delta > 0 && ! $product->is_available) {
            abort(422, 'Este producto esta agotado.');
        }

        DB::transaction(function () use ($table, $guest, $product, $delta, $notes): void {
            $session = $this->currentOpenSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');
            abort_if($guest->is_ready, 422, 'Esta persona ya marco su seleccion como lista.');

            $item = $guest->cartItems()->where('product_id', $product->id)->first();

            if ($delta > 0) {
                if ($item) {
                    $item->quantity++;
                    $item->line_total = $item->quantity * $item->unit_price;
                    $item->notes = $notes ?? $item->notes;
                    $item->save();
                } else {
                    $guest->cartItems()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => 1,
                        'line_total' => $product->price,
                        'notes' => $notes,
                    ]);
                }
            } elseif ($item) {
                if ($delta === 0) {
                    $item->update(['notes' => $notes]);

                    return;
                }

                $item->quantity--;
                if ($item->quantity <= 0) {
                    $item->delete();
                } else {
                    $item->line_total = $item->quantity * $item->unit_price;
                    $item->save();
                }
            }
        });
    }

    public function clearCart(DiningTable $table, TableGuest $guest): void
    {
        DB::transaction(function () use ($table, $guest): void {
            $session = $this->currentOpenSessionFor($table);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_if($guest->is_ready, 422, 'Esta persona ya marco su seleccion como lista.');

            $guest->cartItems()->delete();
        });
    }

    public function setGuestReady(DiningTable $table, TableGuest $guest, bool $isReady): void
    {
        DB::transaction(function () use ($table, $guest, $isReady): void {
            $session = $this->currentOpenSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');

            if ($isReady && ! $guest->is_ready) {
                $this->placeOrderFromCart($session, $guest);
            } elseif (! $isReady && $guest->is_ready && ! $session->confirmed_at) {
                $this->restoreLatestOrderToCart($guest);
            }

            $guest->update([
                'is_ready' => $isReady,
                'ready_at' => $isReady ? now() : null,
            ]);
        });
    }

    public function confirmOrder(DiningTable $table, TableGuest $guest): void
    {
        DB::transaction(function () use ($table, $guest): void {
            $session = $this->currentOpenSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            $session->load(['guests.orders.items', 'guests.cartItems']);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');
            abort_if($session->confirmed_at, 422, 'Este pedido ya fue confirmado.');

            $coordinator = $this->coordinatorGuest($session);

            abort_unless($coordinator?->is($guest), 403, 'Solo la primera persona de la mesa puede confirmar el pedido.');
            abort_unless($this->sessionHasItems($session), 422, 'Agrega al menos un producto antes de confirmar.');
            abort_unless($this->allGuestsReady($session), 422, 'Todas las personas deben marcar su seleccion como lista.');

            $session->update([
                'confirmed_at' => now(),
                'confirmed_by_guest_id' => $guest->id,
            ]);
        });
    }

    public function state(DiningTable $table, ?int $guestId = null, ?int $coordinatorGuestId = null): array
    {
        $session = TableSession::with(['confirmedByGuest', 'guests.cartItems', 'guests.orders.items', 'payments.tableGuest'])
            ->where('dining_table_id', $table->id)
            ->whereIn('status', ['open', 'payment_pending'])
            ->first();

        if ($session) {
            $this->defaultExistingSessionToSeparateMode($session);
        }

        $orderedGuests = $session ? $session->guests->sortBy('id')->values() : collect();
        $aliasTotals = $orderedGuests->countBy(fn (TableGuest $guest): string => mb_strtolower(trim($guest->alias)));
        $aliasOccurrences = [];

        $guests = $session
            ? $orderedGuests->map(function (TableGuest $guest) use ($aliasTotals, &$aliasOccurrences): array {
                $aliasKey = mb_strtolower(trim($guest->alias));
                $aliasOccurrences[$aliasKey] = ($aliasOccurrences[$aliasKey] ?? 0) + 1;
                $isAliasDuplicate = $aliasTotals->get($aliasKey, 0) > 1;
                $items = $guest->cartItems
                    ->map(fn (CartItem $item): array => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product_name,
                        'unit_price' => $item->unit_price,
                        'unit_price_formatted' => $this->money($item->unit_price),
                        'quantity' => $item->quantity,
                        'subtotal' => $item->line_total,
                        'subtotal_formatted' => $this->money($item->line_total),
                        'notes' => $item->notes,
                    ])
                    ->values();

                $subtotal = $items->sum('subtotal');
                $orders = $guest->orders
                    ->sortBy('id')
                    ->map(fn (Order $order): array => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'status_label' => $this->orderStatusLabel($order->status),
                        'subtotal' => $order->subtotal,
                        'subtotal_formatted' => $this->money($order->subtotal),
                        'placed_at' => $order->placed_at?->toIso8601String(),
                        'items' => $order->items->map(fn ($item): array => [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'name' => $item->product_name,
                            'unit_price' => $item->unit_price,
                            'unit_price_formatted' => $this->money($item->unit_price),
                            'quantity' => $item->quantity,
                            'subtotal' => $item->line_total,
                            'subtotal_formatted' => $this->money($item->line_total),
                            'notes' => $item->notes,
                        ])->values(),
                    ])
                    ->values();
                $ordersTotal = $orders->sum('subtotal');
                $personalTotal = $subtotal + $ordersTotal;

                return [
                    'id' => $guest->id,
                    'guest_token' => $guest->guest_token,
                    'alias' => $guest->alias,
                    'display_alias' => $isAliasDuplicate
                        ? $guest->alias.' #'.$aliasOccurrences[$aliasKey]
                        : $guest->alias,
                    'is_alias_duplicate' => $isAliasDuplicate,
                    'joined_at' => $guest->joined_at?->toIso8601String(),
                    'is_ready' => (bool) $guest->is_ready,
                    'items' => $items,
                    'cart_subtotal' => $subtotal,
                    'cart_subtotal_formatted' => $this->money($subtotal),
                    'orders' => $orders,
                    'orders_total' => $ordersTotal,
                    'orders_total_formatted' => $this->money($ordersTotal),
                    'subtotal' => $personalTotal,
                    'subtotal_formatted' => $this->money($personalTotal),
                ];
            })->values()
            : collect();

        $categories = Category::query()
            ->where('is_active', true)
            ->with(['visibleProducts'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'products' => $category->visibleProducts->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image_url' => $product->imageUrl(),
                    'price' => $product->price,
                    'price_formatted' => $product->formattedPrice(),
                    'is_available' => $product->is_available,
                ])->values(),
            ])->values();

        $total = $guests->sum('subtotal');
        $jointOrderOwner = $session?->account_mode === TableAccountMode::Joint
            ? $guests->first(fn (array $guest): bool => $guest['items']->isNotEmpty() || $guest['orders']->isNotEmpty())
            : null;
        $jointOrderLocked = (bool) $jointOrderOwner && $jointOrderOwner['id'] !== $guestId;
        $coordinator = $session ? $this->coordinatorGuest($session) : null;
        $hasItems = $total > 0;
        $allGuestsReady = $session ? $this->allGuestsReady($session) : false;
        $orderConfirmed = (bool) $session?->confirmed_at;
        $deviceCanConfirmOrder = $coordinator
            && $coordinator->id === $coordinatorGuestId;
        $canConfirmOrder = ! $orderConfirmed
            && $hasItems
            && $allGuestsReady
            && $deviceCanConfirmOrder;

        return [
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
            ],
            'account_mode' => $session?->account_mode?->value,
            'account_mode_label' => $session?->account_mode?->label(),
            'session_status' => $session?->status,
            'requires_account_mode' => ! $session?->account_mode,
            'joint_order_locked' => $jointOrderLocked,
            'joint_order_owner_alias' => $jointOrderOwner['alias'] ?? null,
            'order_confirmed' => $orderConfirmed,
            'confirmed_by_alias' => $session?->confirmedByGuest?->alias,
            'coordinator_guest_id' => $coordinator?->id,
            'coordinator_alias' => $coordinator?->alias,
            'device_can_confirm_order' => $deviceCanConfirmOrder,
            'all_guests_ready' => $allGuestsReady,
            'has_items' => $hasItems,
            'can_confirm_order' => $canConfirmOrder,
            'current_guest_id' => $guestId,
            'guests' => $guests,
            'categories' => $categories,
            'total' => $total,
            'total_formatted' => $this->money($total),
            'bill' => $session ? $this->billingService->summary($session) : null,
        ];
    }

    private function money(int $value): string
    {
        return '$'.number_format($value, 0, ',', '.');
    }

    private function currentOpenSessionFor(DiningTable $table): TableSession
    {
        $session = TableSession::where('dining_table_id', $table->id)
            ->where('status', 'open')
            ->first();

        abort_unless($session, 403, 'Esta sesion de mesa ya no esta abierta.');

        return $session;
    }

    private function placeOrderFromCart(TableSession $session, TableGuest $guest): Order
    {
        $cartItems = $guest->cartItems()->get();

        abort_unless($cartItems->isNotEmpty(), 422, 'Agrega al menos un producto antes de confirmar tu pedido.');

        $subtotal = $cartItems->sum('line_total');

        $order = $session->orders()->create([
            'table_guest_id' => $guest->id,
            'status' => 'new',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'placed_at' => now(),
        ]);

        $cartItems->each(function (CartItem $cartItem) use ($order): void {
            $order->items()->create([
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product_name,
                'unit_price' => $cartItem->unit_price,
                'quantity' => $cartItem->quantity,
                'line_total' => $cartItem->line_total,
                'subtotal' => $cartItem->line_total,
                'notes' => $cartItem->notes,
            ]);
        });

        $guest->cartItems()->delete();

        return $order;
    }

    private function restoreLatestOrderToCart(TableGuest $guest): void
    {
        if ($guest->cartItems()->exists()) {
            return;
        }

        $order = $guest->orders()
            ->with('items')
            ->where('status', 'new')
            ->latest('id')
            ->first();

        if (! $order) {
            return;
        }

        $order->items->each(function ($item) use ($guest): void {
            $guest->cartItems()->updateOrCreate(
                ['product_id' => $item->product_id],
                [
                    'product_name' => $item->product_name,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                    'notes' => $item->notes,
                ]
            );
        });

        $order->delete();
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'new' => 'Nuevo',
            'preparing' => 'Preparando',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => ucfirst($status),
        };
    }

    private function defaultExistingSessionToSeparateMode(TableSession $session): void
    {
        if ($session->account_mode || ! $session->guests()->exists()) {
            return;
        }

        $session->update(['account_mode' => TableAccountMode::Separate]);
        $session->account_mode = TableAccountMode::Separate;
    }

    private function coordinatorGuest(TableSession $session): ?TableGuest
    {
        return $session->guests->sortBy('id')->first();
    }

    private function allGuestsReady(TableSession $session): bool
    {
        return $session->guests->isNotEmpty()
            && $session->guests->every(fn (TableGuest $guest): bool => (bool) $guest->is_ready);
    }

    private function sessionHasItems(TableSession $session): bool
    {
        return $session->guests->some(
            fn (TableGuest $guest): bool => $guest->orders->isNotEmpty() || $guest->cartItems->isNotEmpty()
        );
    }
}
