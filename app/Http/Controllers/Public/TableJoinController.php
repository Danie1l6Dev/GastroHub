<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableAliasRequest;
use App\Http\Requests\TableItemRequest;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\TableGuest;
use App\Services\TableSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TableJoinController extends Controller
{
    public function __construct(private readonly TableSessionService $tableSessionService) {}

    public function __invoke(string $qrToken, Request $request): View|Response
    {
        $table = DiningTable::where('qr_token', $qrToken)->first();

        if (! $table) {
            return response()->view('public.table-error', [
                'restaurant' => RestaurantSetting::first(),
                'message' => 'El codigo QR no es valido o ya fue reemplazado.',
            ], 404);
        }

        if (! $table->is_active) {
            return response()->view('public.table-error', [
                'restaurant' => RestaurantSetting::first(),
                'message' => 'Esta mesa no esta disponible en este momento. Por favor solicita ayuda al restaurante.',
            ], 403);
        }

        return view('public.table-placeholder', [
            'table' => $table,
            'restaurant' => RestaurantSetting::first(),
            'alias' => session($this->aliasSessionKey($table)),
            'guestId' => session($this->guestSessionKey($table)),
        ]);
    }

    public function join(TableAliasRequest $request, string $qrToken): RedirectResponse|Response|JsonResponse
    {
        $table = DiningTable::where('qr_token', $qrToken)->first();

        if (! $table) {
            return response()->view('public.table-error', [
                'restaurant' => RestaurantSetting::first(),
                'message' => 'El codigo QR no es valido o ya fue reemplazado.',
            ], 404);
        }

        if (! $table->is_active) {
            return response()->view('public.table-error', [
                'restaurant' => RestaurantSetting::first(),
                'message' => 'Esta mesa no esta disponible en este momento. Por favor solicita ayuda al restaurante.',
            ], 403);
        }

        $guest = $this->tableSessionService->join(
            table: $table,
            alias: $request->validated('alias'),
            guestId: $request->session()->get($this->guestSessionKey($table))
        );

        $request->session()->put($this->aliasSessionKey($table), $guest->alias);
        $request->session()->put($this->guestSessionKey($table), $guest->id);

        if ($request->expectsJson()) {
            return response()->json($this->tableSessionService->state($table, $guest->id));
        }

        return redirect()
            ->route('tables.join', $table->qr_token)
            ->with('status', 'Listo, ya estas identificado en esta mesa.');
    }

    public function state(Request $request, string $qrToken): JsonResponse|Response
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        return response()->json($this->tableSessionService->state(
            table: $table,
            guestId: $request->session()->get($this->guestSessionKey($table))
        ));
    }

    public function release(Request $request, string $qrToken): JsonResponse|RedirectResponse|Response
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        $request->session()->forget([
            $this->aliasSessionKey($table),
            $this->guestSessionKey($table),
        ]);

        if ($request->expectsJson()) {
            return response()->json($this->tableSessionService->state($table));
        }

        return redirect()
            ->route('tables.join', $table->qr_token)
            ->with('status', 'Listo, ahora puedes registrar otra persona desde este dispositivo.');
    }

    public function selectGuest(Request $request, string $qrToken, TableGuest $guest): JsonResponse|RedirectResponse|Response
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        abort_unless(
            $guest->tableSession()
                ->where('dining_table_id', $table->id)
                ->where('status', 'open')
                ->exists(),
            404
        );

        $request->session()->put($this->aliasSessionKey($table), $guest->alias);
        $request->session()->put($this->guestSessionKey($table), $guest->id);

        if ($request->expectsJson()) {
            return response()->json($this->tableSessionService->state($table, $guest->id));
        }

        return redirect()
            ->route('tables.join', $table->qr_token)
            ->with('status', 'Ahora estas editando el pedido de '.$guest->alias.'.');
    }

    public function item(TableItemRequest $request, string $qrToken): JsonResponse|Response
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        $guest = TableGuest::whereKey($request->session()->get($this->guestSessionKey($table)))->first();

        abort_unless($guest, 403, 'Primero escribe tu nombre o alias.');

        $this->tableSessionService->changeItem(
            table: $table,
            guest: $guest,
            product: Product::findOrFail($request->integer('product_id')),
            delta: $request->integer('delta')
        );

        return response()->json($this->tableSessionService->state($table, $guest->id));
    }

    private function aliasSessionKey(DiningTable $table): string
    {
        return 'tables.'.$table->id.'.alias';
    }

    private function guestSessionKey(DiningTable $table): string
    {
        return 'tables.'.$table->id.'.guest_id';
    }

    private function activeTableResponse(string $qrToken): DiningTable|Response
    {
        $table = DiningTable::where('qr_token', $qrToken)->first();

        if (! $table) {
            return response()->view('public.table-error', [
                'restaurant' => RestaurantSetting::first(),
                'message' => 'El codigo QR no es valido o ya fue reemplazado.',
            ], 404);
        }

        if (! $table->is_active) {
            return response()->view('public.table-error', [
                'restaurant' => RestaurantSetting::first(),
                'message' => 'Esta mesa no esta disponible en este momento. Por favor solicita ayuda al restaurante.',
            ], 403);
        }

        return $table;
    }
}
