<?php

namespace App\Services;

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

    public function join(DiningTable $table, string $alias, ?int $guestId = null): TableGuest
    {
        return DB::transaction(function () use ($table, $alias, $guestId): TableGuest {
            $session = $this->openSessionFor($table);

            $guest = $guestId
                ? TableGuest::whereKey($guestId)->where('table_session_id', $session->id)->first()
                : null;

            if ($guest) {
                $guest->update(['alias' => $alias, 'status' => 'active']);

                return $guest;
            }

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

            abort_unless($guest->table_session_id === $session->id, 403);

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

    public function state(DiningTable $table, ?int $guestId = null): array
    {
        $session = TableSession::with(['guests.orders.items'])
            ->where('dining_table_id', $table->id)
            ->where('status', 'open')
            ->first();

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

        return [
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
            ],
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
}
