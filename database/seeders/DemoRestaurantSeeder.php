<?php

namespace Database\Seeders;

use App\Enums\TableAccountMode;
use App\Enums\TableStatus;
use App\Models\CartItem;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\TableGuest;
use App\Models\TableSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoRestaurantSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->clearOperationalDemoData();

            $products = Product::query()
                ->whereIn('name', [
                    'Croquetas de yuca',
                    'Arroz meloso de mar',
                    'Pollo a la brasa',
                    'Pasta de la casa',
                    'Limonada de hierbabuena',
                    'Cafe frio',
                    'Soda de frutos rojos',
                    'Flan de caramelo',
                    'Brownie tibio',
                ])
                ->get()
                ->keyBy('name');

            $this->createClosedSession(
                tableName: 'Mesa 1',
                accountMode: TableAccountMode::Separate,
                openedAt: now()->subDays(2)->setTime(13, 10),
                guests: [
                    [
                        'alias' => 'Daniel',
                        'items' => [
                            ['product' => 'Croquetas de yuca', 'quantity' => 1],
                            ['product' => 'Arroz meloso de mar', 'quantity' => 1],
                            ['product' => 'Limonada de hierbabuena', 'quantity' => 1],
                        ],
                        'payment' => 'individual',
                    ],
                    [
                        'alias' => 'Ana',
                        'items' => [
                            ['product' => 'Croquetas de yuca', 'quantity' => 1],
                            ['product' => 'Pollo a la brasa', 'quantity' => 1, 'notes' => 'Sin ensalada'],
                            ['product' => 'Soda de frutos rojos', 'quantity' => 1],
                        ],
                        'payment' => 'individual',
                    ],
                ],
                products: $products
            );

            $this->createClosedSession(
                tableName: 'Mesa 3',
                accountMode: TableAccountMode::Joint,
                openedAt: now()->subDay()->setTime(20, 5),
                guests: [
                    [
                        'alias' => 'Maria',
                        'items' => [
                            ['product' => 'Pasta de la casa', 'quantity' => 2],
                            ['product' => 'Flan de caramelo', 'quantity' => 2],
                            ['product' => 'Cafe frio', 'quantity' => 2],
                        ],
                        'payment' => 'full_table',
                    ],
                ],
                products: $products
            );

            $this->createClosedSession(
                tableName: 'Mesa 5',
                accountMode: TableAccountMode::Separate,
                openedAt: now()->subDays(3)->setTime(18, 45),
                guests: [
                    [
                        'alias' => 'Laura',
                        'items' => [
                            ['product' => 'Brownie tibio', 'quantity' => 1],
                            ['product' => 'Limonada de hierbabuena', 'quantity' => 1],
                        ],
                        'payment' => 'individual',
                    ],
                    [
                        'alias' => 'Carlos',
                        'items' => [
                            ['product' => 'Pollo a la brasa', 'quantity' => 1],
                            ['product' => 'Cafe frio', 'quantity' => 1],
                        ],
                        'payment' => 'individual',
                    ],
                ],
                products: $products,
                extraOrders: [
                    [
                        'alias' => 'Laura',
                        'placed_after_minutes' => 24,
                        'items' => [
                            ['product' => 'Flan de caramelo', 'quantity' => 1, 'notes' => 'Para compartir'],
                        ],
                    ],
                ]
            );

            DiningTable::query()->update(['current_status' => TableStatus::Available->value]);
        });
    }

    private function clearOperationalDemoData(): void
    {
        CartItem::query()->delete();
        Payment::query()->delete();
        OrderItem::query()->delete();
        Order::query()->delete();
        TableGuest::query()->delete();
        TableSession::query()->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $guests
     * @param  array<int, array<string, mixed>>  $extraOrders
     */
    private function createClosedSession(
        string $tableName,
        TableAccountMode $accountMode,
        mixed $openedAt,
        array $guests,
        $products,
        array $extraOrders = []
    ): void {
        $table = DiningTable::query()->where('name', $tableName)->firstOrFail();
        $confirmedAt = $openedAt->copy()->addMinutes(12);
        $closedAt = $confirmedAt->copy()->addHour();

        $session = $table->sessions()->create([
            'status' => 'closed',
            'account_mode' => $accountMode,
            'opened_at' => $openedAt,
            'confirmed_at' => $confirmedAt,
            'closed_at' => $closedAt,
            'created_at' => $openedAt,
            'updated_at' => $closedAt,
        ]);

        $createdGuests = collect($guests)->mapWithKeys(function (array $guestData) use ($session, $openedAt): array {
            $guest = $session->guests()->create([
                'alias' => $guestData['alias'],
                'status' => 'active',
                'is_ready' => true,
                'joined_at' => $openedAt->copy()->addMinutes(3),
                'ready_at' => $openedAt->copy()->addMinutes(10),
                'paid_at' => $openedAt->copy()->addMinutes(48),
            ]);

            return [$guest->alias => $guest];
        });

        $session->update(['confirmed_by_guest_id' => $createdGuests->first()->id]);

        $mainTotal = 0;

        foreach ($guests as $guestData) {
            $guest = $createdGuests->get($guestData['alias']);
            $order = $this->createOrderWithItems(
                session: $session,
                guest: $guest,
                products: $products,
                items: $guestData['items'],
                placedAt: $confirmedAt->copy()->subMinute()
            );

            $mainTotal += $order->subtotal;

            if (($guestData['payment'] ?? null) === 'individual') {
                $session->payments()->create([
                    'table_guest_id' => $guest->id,
                    'scope' => 'individual',
                    'type' => 'individual',
                    'amount' => $order->subtotal,
                    'status' => 'paid',
                    'paid_at' => $closedAt->copy()->subMinutes(10),
                    'reference' => 'DEMO-IND-'.$guest->id,
                ]);
            }
        }

        foreach ($extraOrders as $extraOrderData) {
            $guest = $createdGuests->get($extraOrderData['alias']);
            $extraOrder = $this->createOrderWithItems(
                session: $session,
                guest: $guest,
                products: $products,
                items: $extraOrderData['items'],
                placedAt: $confirmedAt->copy()->addMinutes($extraOrderData['placed_after_minutes'])
            );

            $mainTotal += $extraOrder->subtotal;

            $session->payments()->create([
                'table_guest_id' => $guest->id,
                'scope' => 'individual',
                'type' => 'individual',
                'amount' => $extraOrder->subtotal,
                'status' => 'paid',
                'paid_at' => $closedAt->copy()->subMinutes(8),
                'reference' => 'DEMO-EXTRA-'.$extraOrder->id,
            ]);
        }

        if ($accountMode === TableAccountMode::Joint) {
            $session->payments()->create([
                'table_guest_id' => $createdGuests->first()->id,
                'scope' => 'full_table',
                'type' => 'full_table',
                'amount' => $mainTotal,
                'status' => 'paid',
                'paid_at' => $closedAt->copy()->subMinutes(9),
                'reference' => 'DEMO-MESA-'.$session->id,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function createOrderWithItems(TableSession $session, TableGuest $guest, $products, array $items, mixed $placedAt): Order
    {
        $subtotal = collect($items)->sum(function (array $item) use ($products): int {
            $product = $products->get($item['product']);

            return $product->price * $item['quantity'];
        });

        $order = $session->orders()->create([
            'table_guest_id' => $guest->id,
            'status' => 'delivered',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'placed_at' => $placedAt,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        foreach ($items as $item) {
            $product = $products->get($item['product']);
            $lineTotal = $product->price * $item['quantity'];

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $item['quantity'],
                'line_total' => $lineTotal,
                'subtotal' => $lineTotal,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        return $order;
    }
}
