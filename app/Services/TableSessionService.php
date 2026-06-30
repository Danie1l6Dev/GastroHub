<?php

namespace App\Services;

use App\Enums\TableAccountMode;
use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\TableGuest;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;

class TableSessionService
{
    public function openSessionFor(DiningTable $table): TableSession
    {
        return DB::transaction(function () use ($table): TableSession {
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

    public function join(DiningTable $table, string $alias, ?int $guestId = null): TableGuest
    {
        return DB::transaction(function () use ($table, $alias, $guestId): TableGuest {
            $session = $this->openSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');
            abort_if($session->confirmed_at, 422, 'Este pedido ya fue confirmado.');

            $guest = $guestId
                ? TableGuest::whereKey($guestId)->where('table_session_id', $session->id)->first()
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

    public function changeItem(DiningTable $table, TableGuest $guest, Product $product, int $delta): void
    {
        if (! $product->is_available) {
            abort(422, 'Este producto esta agotado.');
        }

        DB::transaction(function () use ($table, $guest, $product, $delta): void {
            $session = $this->openSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');
            abort_if($session->confirmed_at, 422, 'Este pedido ya fue confirmado.');
            abort_if($guest->is_ready, 422, 'Esta persona ya marco su seleccion como lista.');

            $order = Order::firstOrCreate(
                [
                    'table_session_id' => $session->id,
                    'table_guest_id' => $guest->id,
                    'status' => 'pending',
                ],
                ['total' => 0]
            );

            $item = $order->items()->where('product_id', $product->id)->first();

            if ($delta > 0) {
                if ($item) {
                    $item->quantity++;
                    $item->subtotal = $item->quantity * $item->unit_price;
                    $item->save();
                } else {
                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => 1,
                        'subtotal' => $product->price,
                    ]);
                }
            } elseif ($item) {
                $item->quantity--;

                if ($item->quantity <= 0) {
                    $item->delete();
                } else {
                    $item->subtotal = $item->quantity * $item->unit_price;
                    $item->save();
                }
            }

            $order->update(['total' => $order->items()->sum('subtotal')]);
        });
    }

    public function setGuestReady(DiningTable $table, TableGuest $guest, bool $isReady): void
    {
        DB::transaction(function () use ($table, $guest, $isReady): void {
            $session = $this->openSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');
            abort_if($session->confirmed_at, 422, 'Este pedido ya fue confirmado.');

            $guest->update([
                'is_ready' => $isReady,
                'ready_at' => $isReady ? now() : null,
            ]);
        });
    }

    public function confirmOrder(DiningTable $table, TableGuest $guest): void
    {
        DB::transaction(function () use ($table, $guest): void {
            $session = $this->openSessionFor($table);

            $this->defaultExistingSessionToSeparateMode($session);

            $session->load(['guests.orders.items']);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_unless($session->account_mode, 422, 'Primero elige como se pagara esta mesa.');
            abort_if($session->confirmed_at, 422, 'Este pedido ya fue confirmado.');

            $coordinator = $this->coordinatorGuest($session);

            abort_unless($coordinator?->is($guest), 403, 'Solo la primera persona de la mesa puede confirmar el pedido.');
            abort_unless($this->sessionHasItems($session), 422, 'Agrega al menos un producto antes de confirmar.');
            abort_unless($this->allGuestsReady($session), 422, 'Todas las personas deben marcar su seleccion como lista.');

            $session->orders()->where('status', 'pending')->update(['status' => 'confirmed']);
            $session->update([
                'confirmed_at' => now(),
                'confirmed_by_guest_id' => $guest->id,
            ]);
        });
    }

    public function state(DiningTable $table, ?int $guestId = null, ?int $coordinatorGuestId = null): array
    {
        $session = TableSession::with(['confirmedByGuest', 'guests.orders.items'])
            ->where('dining_table_id', $table->id)
            ->where('status', 'open')
            ->first();

        if ($session) {
            $this->defaultExistingSessionToSeparateMode($session);
        }

        $guests = $session
            ? $session->guests->map(function (TableGuest $guest): array {
                $items = $guest->orders
                    ->flatMap->items
                    ->map(fn ($item): array => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product_name,
                        'unit_price' => $item->unit_price,
                        'unit_price_formatted' => $this->money($item->unit_price),
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                        'subtotal_formatted' => $this->money($item->subtotal),
                    ])
                    ->values();

                $subtotal = $items->sum('subtotal');

                return [
                    'id' => $guest->id,
                    'alias' => $guest->alias,
                    'is_ready' => (bool) $guest->is_ready,
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'subtotal_formatted' => $this->money($subtotal),
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
            ? $guests->first(fn (array $guest): bool => $guest['items']->isNotEmpty())
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
        ];
    }

    private function money(int $value): string
    {
        return '$'.number_format($value, 0, ',', '.');
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
            fn (TableGuest $guest): bool => $guest->orders->flatMap->items->isNotEmpty()
        );
    }
}
