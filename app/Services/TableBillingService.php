<?php

namespace App\Services;

use App\Enums\TableAccountMode;
use App\Enums\TableStatus;
use App\Models\DiningTable;
use App\Models\Payment;
use App\Models\TableGuest;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TableBillingService
{
    public function summary(TableSession $session): array
    {
        $session->loadMissing(['guests.orders.items', 'guests.payments', 'payments.tableGuest', 'diningTable']);

        $billableOrders = $session->guests
            ->flatMap(fn (TableGuest $guest) => $guest->orders)
            ->where('status', '!=', 'cancelled')
            ->values();
        $paymentReady = (bool) $session->confirmed_at
            && $billableOrders->isNotEmpty()
            && $billableOrders->every(fn ($order): bool => $order->status === 'delivered');
        $fullTablePaid = (int) $session->payments
            ->where('type', 'full_table')
            ->where('status', 'paid')
            ->sum('amount');

        $participants = $session->guests
            ->sortBy('id')
            ->values()
            ->map(function (TableGuest $guest) use ($fullTablePaid): array {
                $orders = $guest->orders
                    ->where('status', '!=', 'cancelled')
                    ->values();
                $consumed = (int) $orders->sum(fn ($order): int => (int) ($order->subtotal ?: $order->total));
                $individualPaid = (int) $guest->payments
                    ->where('type', 'individual')
                    ->where('status', 'paid')
                    ->sum('amount');
                $paid = $fullTablePaid > 0 ? $consumed : min($individualPaid, $consumed);

                return [
                    'id' => $guest->id,
                    'alias' => $guest->alias,
                    'orders' => $orders->map(fn ($order): array => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'status_label' => $this->orderStatusLabel($order->status),
                        'subtotal' => (int) ($order->subtotal ?: $order->total),
                        'subtotal_formatted' => $this->money((int) ($order->subtotal ?: $order->total)),
                        'items' => $order->items->map(fn ($item): array => [
                            'name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'subtotal' => (int) ($item->line_total ?: $item->subtotal),
                            'subtotal_formatted' => $this->money((int) ($item->line_total ?: $item->subtotal)),
                            'notes' => $item->notes,
                        ])->values(),
                    ])->values(),
                    'consumed' => $consumed,
                    'consumed_formatted' => $this->money($consumed),
                    'paid' => $paid,
                    'paid_formatted' => $this->money($paid),
                    'balance' => max($consumed - $paid, 0),
                    'balance_formatted' => $this->money(max($consumed - $paid, 0)),
                    'can_pay_individual' => false,
                ];
            });

        $total = (int) $participants->sum('consumed');
        $individualPaid = (int) $session->payments
            ->where('type', 'individual')
            ->where('status', 'paid')
            ->sum('amount');
        $totalPaid = min($total, $individualPaid + $fullTablePaid);
        $balance = max($total - $totalPaid, 0);

        return [
            'participants' => $participants,
            'total' => $total,
            'total_formatted' => $this->money($total),
            'paid' => $totalPaid,
            'paid_formatted' => $this->money($totalPaid),
            'balance' => $balance,
            'balance_formatted' => $this->money($balance),
            'is_paid' => $total > 0 && $balance === 0,
            'payment_ready' => $paymentReady,
            'payment_ready_message' => $this->paymentReadyMessage($session, $billableOrders->isNotEmpty(), $paymentReady),
            'can_pay_full_table' => false,
            'payments' => $session->payments
                ->where('status', 'paid')
                ->sortByDesc('paid_at')
                ->values()
                ->map(fn (Payment $payment): array => [
                    'id' => $payment->id,
                    'type' => $payment->type,
                    'type_label' => $payment->type === 'full_table' ? 'Mesa completa' : 'Individual',
                    'amount' => $payment->amount,
                    'amount_formatted' => $this->money($payment->amount),
                    'reference' => $payment->reference,
                    'paid_at' => $payment->paid_at?->toIso8601String(),
                    'guest_alias' => $payment->tableGuest?->alias,
                ]),
        ];
    }

    public function payIndividual(DiningTable $table, TableGuest $guest): array
    {
        return DB::transaction(function () use ($table, $guest): array {
            $session = $this->lockActiveSession($table);

            abort_unless($guest->table_session_id === $session->id, 403);
            abort_if($session->status === 'closed', 422, 'Esta mesa ya fue cerrada.');
            abort_if($session->account_mode === TableAccountMode::Joint, 422, 'Esta mesa usa pago en conjunto.');

            $summary = $this->summary($session->fresh(['guests.orders.items', 'payments.tableGuest', 'diningTable']));
            $participant = $summary['participants']->firstWhere('id', $guest->id);
            $amount = (int) ($participant['balance'] ?? 0);

            abort_unless($summary['payment_ready'], 422, $summary['payment_ready_message']);
            abort_unless($amount > 0, 422, 'Esta persona no tiene saldo pendiente.');

            $session->payments()->create([
                'table_guest_id' => $guest->id,
                'scope' => 'individual',
                'type' => 'individual',
                'amount' => $amount,
                'status' => 'paid',
                'paid_at' => now(),
                'reference' => $this->reference('IND'),
            ]);

            $guest->forceFill(['paid_at' => now()])->save();
            $this->markPaymentPendingWhenPaid($session);

            return $this->summary($session->fresh(['guests.orders.items', 'payments.tableGuest', 'diningTable']));
        });
    }

    public function payFullTable(DiningTable $table, ?TableGuest $guest): array
    {
        return DB::transaction(function () use ($table, $guest): array {
            $session = $this->lockActiveSession($table);

            abort_if($session->status === 'closed', 422, 'Esta mesa ya fue cerrada.');
            abort_if($guest && $guest->table_session_id !== $session->id, 403);

            $summary = $this->summary($session->fresh(['guests.orders.items', 'payments.tableGuest', 'diningTable']));
            $amount = (int) $summary['balance'];

            abort_unless($summary['payment_ready'], 422, $summary['payment_ready_message']);
            abort_unless($amount > 0, 422, 'La mesa no tiene saldo pendiente.');

            $session->payments()->create([
                'table_guest_id' => $guest?->id,
                'scope' => 'full_table',
                'type' => 'full_table',
                'amount' => $amount,
                'status' => 'paid',
                'paid_at' => now(),
                'reference' => $this->reference('MESA'),
            ]);

            $session->guests()->update(['paid_at' => now()]);
            $this->markPaymentPendingWhenPaid($session);

            return $this->summary($session->fresh(['guests.orders.items', 'payments.tableGuest', 'diningTable']));
        });
    }

    public function closePaidSession(DiningTable $table): void
    {
        DB::transaction(function () use ($table): void {
            $session = $table->sessions()
                ->whereIn('status', ['open', 'payment_pending'])
                ->lockForUpdate()
                ->first();

            abort_unless($session, 404, 'No hay una sesion activa para cerrar.');

            $summary = $this->summary($session->fresh(['guests.orders.items', 'payments.tableGuest', 'diningTable']));

            abort_unless(
                $summary['is_paid'] || $summary['payment_ready'],
                422,
                $summary['payment_ready_message']
            );

            if (! $summary['is_paid'] && (int) $summary['balance'] > 0) {
                $session->payments()->create([
                    'table_guest_id' => null,
                    'scope' => 'full_table',
                    'type' => 'full_table',
                    'amount' => (int) $summary['balance'],
                    'status' => 'paid',
                    'paid_at' => now(),
                    'reference' => $this->reference('ADMIN'),
                ]);

                $session->guests()->update(['paid_at' => now()]);
            }

            $session->forceFill([
                'status' => 'closed',
                'closed_at' => now(),
            ])->save();

            $table->forceFill(['current_status' => TableStatus::Available])->save();
        });
    }

    private function lockActiveSession(DiningTable $table): TableSession
    {
        $session = $table->sessions()
            ->whereIn('status', ['open', 'payment_pending'])
            ->lockForUpdate()
            ->first();

        abort_unless($session, 403, 'Esta sesion de mesa ya no esta abierta.');

        return $session;
    }

    private function markPaymentPendingWhenPaid(TableSession $session): void
    {
        $summary = $this->summary($session->fresh(['guests.orders.items', 'payments.tableGuest', 'diningTable']));

        if (! $summary['is_paid']) {
            return;
        }

        $session->forceFill(['status' => 'payment_pending'])->save();
        $session->diningTable?->forceFill(['current_status' => TableStatus::PaymentPending])->save();
    }

    private function reference(string $prefix): string
    {
        return $prefix.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
    }

    private function paymentReadyMessage(TableSession $session, bool $hasBillableOrders, bool $paymentReady): string
    {
        if ($paymentReady) {
            return 'La cuenta ya esta lista para cobrar.';
        }

        if (! $session->confirmed_at) {
            return 'Confirma el pedido final para preparar la cuenta.';
        }

        if (! $hasBillableOrders) {
            return 'Todavia no hay pedidos para cobrar.';
        }

        return 'La cuenta se puede cerrar cuando todos los pedidos esten entregados.';
    }

    private function money(int $value): string
    {
        return '$'.number_format($value, 0, ',', '.');
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
}
