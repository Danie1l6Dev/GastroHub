<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiningTableRequest;
use App\Models\DiningTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(): View
    {
        return view('admin.tables.index', [
            'tables' => DiningTable::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tables.form', ['table' => new DiningTable(['is_active' => true])]);
    }

    public function store(DiningTableRequest $request): RedirectResponse
    {
        DiningTable::create($request->validated());

        return redirect()->route('admin.tables.index')->with('status', 'Mesa creada.');
    }

    public function edit(DiningTable $table): View
    {
        return view('admin.tables.form', ['table' => $table]);
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
}
