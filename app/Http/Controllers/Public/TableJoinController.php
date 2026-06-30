<?php

namespace App\Http\Controllers\Public;

use App\Enums\TableAccountMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableAccountModeRequest;
use App\Http\Requests\TableAliasRequest;
use App\Http\Requests\TableItemRequest;
use App\Http\Requests\TableReadyRequest;
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
        $request->session()->put($this->participationSessionKey($table), true);
        $this->rememberOwnedGuest($request, $table, $guest);
        $this->rememberCoordinatorGuest($request, $table, $guest);

        if ($request->expectsJson()) {
            return response()->json($this->tableSessionService->state(
                table: $table,
                guestId: $guest->id,
                coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
            ));
        }

        return redirect()
            ->route('tables.join', $table->qr_token)
            ->with('status', 'Listo, ya estas identificado en esta mesa.');
    }

    public function accountMode(TableAccountModeRequest $request, string $qrToken): RedirectResponse|Response|JsonResponse
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        $session = $this->tableSessionService->chooseAccountMode(
            table: $table,
            accountMode: $request->enum('account_mode', TableAccountMode::class)
        );

        if ($request->expectsJson()) {
            return response()->json($this->tableSessionService->state(
                table: $table,
                coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
            ));
        }

        return redirect()
            ->route('tables.join', $table->qr_token)
            ->with('status', 'Modo de cuenta seleccionado: '.$session->account_mode->label().'.');
    }

    public function state(Request $request, string $qrToken): JsonResponse|Response
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        return response()->json($this->tableSessionService->state(
            table: $table,
            guestId: $request->session()->get($this->guestSessionKey($table)),
            coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
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
            return response()->json($this->tableSessionService->state(
                table: $table,
                coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
            ));
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
            return response()->json($this->tableSessionService->state(
                table: $table,
                guestId: $guest->id,
                coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
            ));
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

        return response()->json($this->tableSessionService->state(
            table: $table,
            guestId: $guest->id,
            coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
        ));
    }

    public function ready(TableReadyRequest $request, string $qrToken): JsonResponse|Response
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        $guest = TableGuest::whereKey($request->session()->get($this->guestSessionKey($table)))->first();

        abort_unless($guest, 403, 'Primero escribe tu nombre o alias.');

        $this->tableSessionService->setGuestReady(
            table: $table,
            guest: $guest,
            isReady: $request->boolean('is_ready')
        );

        return response()->json($this->tableSessionService->state(
            table: $table,
            guestId: $guest->id,
            coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
        ));
    }

    public function confirm(string $qrToken, Request $request): JsonResponse|Response
    {
        $table = $this->activeTableResponse($qrToken);

        if (! $table instanceof DiningTable) {
            return $table;
        }

        $guest = TableGuest::whereKey($this->coordinatorGuestIdForRequest($request, $table))->first();

        abort_unless($guest, 403, 'Primero escribe tu nombre o alias.');

        $this->tableSessionService->confirmOrder($table, $guest);

        return response()->json($this->tableSessionService->state(
            table: $table,
            guestId: $request->session()->get($this->guestSessionKey($table)),
            coordinatorGuestId: $this->coordinatorGuestIdForRequest($request, $table)
        ));
    }

    private function aliasSessionKey(DiningTable $table): string
    {
        return 'tables.'.$table->id.'.alias';
    }

    private function guestSessionKey(DiningTable $table): string
    {
        return 'tables.'.$table->id.'.guest_id';
    }

    private function coordinatorSessionKey(DiningTable $table): string
    {
        return 'tables.'.$table->id.'.coordinator_guest_id';
    }

    private function ownedGuestsSessionKey(DiningTable $table): string
    {
        return 'tables.'.$table->id.'.owned_guest_ids';
    }

    private function participationSessionKey(DiningTable $table): string
    {
        return 'tables.'.$table->id.'.has_participated';
    }

    private function rememberOwnedGuest(Request $request, DiningTable $table, TableGuest $guest): void
    {
        $ownedGuestIds = $request->session()->get($this->ownedGuestsSessionKey($table), []);

        if (! in_array($guest->id, $ownedGuestIds, true)) {
            $ownedGuestIds[] = $guest->id;
        }

        $request->session()->put($this->ownedGuestsSessionKey($table), $ownedGuestIds);
    }

    private function rememberCoordinatorGuest(Request $request, DiningTable $table, TableGuest $guest): void
    {
        if ($request->session()->has($this->coordinatorSessionKey($table))) {
            return;
        }

        $firstGuestId = $guest->tableSession
            ->guests()
            ->orderBy('id')
            ->value('id');

        if ($firstGuestId === $guest->id) {
            $request->session()->put($this->coordinatorSessionKey($table), $guest->id);
        }
    }

    private function coordinatorGuestIdForRequest(Request $request, DiningTable $table): ?int
    {
        $coordinatorGuestId = $request->session()->get($this->coordinatorSessionKey($table));

        if ($coordinatorGuestId) {
            $storedCoordinatorGuestId = $this->validStoredCoordinatorGuestId($request, $table, (int) $coordinatorGuestId);

            if ($storedCoordinatorGuestId) {
                return $storedCoordinatorGuestId;
            }
        }

        $guestId = $request->session()->get($this->guestSessionKey($table));

        if (! $guestId) {
            return $this->recoverCoordinatorGuestIdFromParticipation($request, $table);
        }

        $guest = TableGuest::with('tableSession.guests')
            ->whereKey($guestId)
            ->first();

        if (! $guest || $guest->tableSession?->dining_table_id !== $table->id || $guest->tableSession->status !== 'open') {
            return null;
        }

        $firstGuestId = $guest->tableSession->guests->sortBy('id')->first()?->id;

        $ownedGuestIds = $request->session()->get($this->ownedGuestsSessionKey($table), []);

        if ($firstGuestId === $guest->id) {
            if ($ownedGuestIds !== [] && ! in_array($firstGuestId, $ownedGuestIds, true)) {
                return null;
            }

            $request->session()->put($this->coordinatorSessionKey($table), $guest->id);

            return $guest->id;
        }

        if (! in_array($firstGuestId, $ownedGuestIds, true)) {
            return null;
        }

        $request->session()->put($this->coordinatorSessionKey($table), $firstGuestId);

        return $firstGuestId;
    }

    private function validStoredCoordinatorGuestId(Request $request, DiningTable $table, int $coordinatorGuestId): ?int
    {
        $guest = TableGuest::with('tableSession.guests')
            ->whereKey($coordinatorGuestId)
            ->first();

        $firstGuestId = $guest?->tableSession?->guests->sortBy('id')->first()?->id;

        if (
            ! $guest
            || $guest->tableSession?->dining_table_id !== $table->id
            || $guest->tableSession->status !== 'open'
            || $firstGuestId !== $guest->id
        ) {
            $request->session()->forget($this->coordinatorSessionKey($table));

            return null;
        }

        return $guest->id;
    }

    private function recoverCoordinatorGuestIdFromParticipation(Request $request, DiningTable $table): ?int
    {
        $session = $table->sessions()
            ->with('guests')
            ->where('status', 'open')
            ->first();

        $firstGuestId = $session?->guests->sortBy('id')->first()?->id;

        if (! $firstGuestId) {
            return null;
        }

        $ownedGuestIds = $request->session()->get($this->ownedGuestsSessionKey($table), []);

        if (! in_array($firstGuestId, $ownedGuestIds, true)) {
            return null;
        }

        $request->session()->put($this->coordinatorSessionKey($table), $firstGuestId);

        return $firstGuestId;
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
