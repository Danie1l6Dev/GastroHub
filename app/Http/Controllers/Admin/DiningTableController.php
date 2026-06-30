<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiningTableRequest;
use App\Models\DiningTable;
use App\Services\TableBillingService;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(TableBillingService $billingService): View
    {
        $tables = DiningTable::with(['sessions' => function ($query): void {
            $query->whereIn('status', ['open', 'payment_pending'])
                ->with(['guests.orders.items', 'payments.tableGuest', 'diningTable']);
        }])->orderBy('name')->get();

        return view('admin.tables.index', [
            'tables' => $tables,
            'billingSummaries' => $tables
                ->mapWithKeys(function (DiningTable $table) use ($billingService): array {
                    $session = $table->sessions->first();

                    return [$table->id => $session ? $billingService->summary($session) : null];
                }),
        ]);
    }

    public function create(): View
    {
        return view('admin.tables.form', [
            'table' => new DiningTable(['is_active' => true, 'current_status' => TableStatus::Available]),
            'statuses' => TableStatus::cases(),
        ]);
    }

    public function store(DiningTableRequest $request): RedirectResponse
    {
        DiningTable::create($request->validated());

        return redirect()->route('admin.tables.index')->with('status', 'Mesa creada.');
    }

    public function edit(DiningTable $table): View
    {
        return view('admin.tables.form', [
            'table' => $table,
            'statuses' => TableStatus::cases(),
        ]);
    }

    public function update(DiningTableRequest $request, DiningTable $table): RedirectResponse
    {
        $table->update($request->validated());

        return redirect()->route('admin.tables.index')->with('status', 'Mesa actualizada.');
    }

    public function destroy(DiningTable $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('admin.tables.index')->with('status', 'Mesa eliminada.');
    }

    public function regenerateToken(DiningTable $table): RedirectResponse
    {
        $table->regenerateQrToken();

        return redirect()->route('admin.tables.index')->with('status', 'Token QR regenerado.');
    }

    public function closeSession(DiningTable $table, TableBillingService $billingService): RedirectResponse
    {
        $billingService->closePaidSession($table);

        return redirect()->route('admin.tables.index')->with('status', 'Mesa cerrada y liberada.');
    }

    public function downloadQr(DiningTable $table): Response
    {
        $result = $this->qrResult($table);

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Content-Disposition' => 'attachment; filename="'.$table->code.'-qr.svg"',
        ]);
    }

    public function printQr(DiningTable $table): View
    {
        return view('admin.tables.print', [
            'table' => $table,
            'qrSvg' => $this->qrResult($table)->getString(),
        ]);
    }

    private function qrResult(DiningTable $table): ResultInterface
    {
        $writer = new SvgWriter;
        $qrCode = new QrCode(
            data: $table->qrUrl(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 320,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return $writer->write($qrCode);
    }
}
