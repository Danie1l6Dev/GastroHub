@extends('layouts.admin', ['title' => $table->exists ? 'Editar mesa' : 'Nueva mesa'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Salon</p>
            <h1 class="gh-page-title">{{ $table->exists ? 'Editar mesa' : 'Nueva mesa' }}</h1>
            <p class="gh-page-copy">Configura el nombre visible, codigo interno y estado de una mesa QR.</p>
        </div>
        <a href="{{ route('admin.tables.index') }}" class="gh-btn gh-btn-secondary">Volver</a>
    </div>

    <form method="POST" action="{{ $table->exists ? route('admin.tables.update', $table) : route('admin.tables.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem]">
        @csrf
        @if ($table->exists)
            @method('PUT')
        @endif

        <section class="gh-panel space-y-5">
            <x-form-input label="Nombre visible" name="name" :value="$table->name" required placeholder="Ej. Mesa terraza 1" />
            <x-form-input label="Codigo interno" name="code" :value="$table->code" required placeholder="Ej. T01" />
            <x-form-input label="Capacidad" name="capacity" type="number" min="1" max="30" :value="$table->capacity" />

            <div>
                <label class="gh-label" for="current_status">Estado actual</label>
                <select id="current_status" name="current_status" class="gh-field" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('current_status', $table->current_status?->value ?? 'available') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('current_status') <p class="gh-error">{{ $message }}</p> @enderror
            </div>

            <label class="flex min-h-14 items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-medium">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $table->is_active ?? true)) class="rounded border-zinc-300 text-brand-red">
                Mesa activa para recibir clientes
            </label>

            <div class="flex flex-wrap gap-2">
                <x-button>Guardar mesa</x-button>
                <a href="{{ route('admin.tables.index') }}" class="gh-btn gh-btn-secondary">Cancelar</a>
            </div>
        </section>

        <aside class="gh-panel self-start">
            <p class="text-sm font-semibold">Acceso QR</p>
            @if ($table->exists)
                <p class="mt-2 text-sm leading-6 text-zinc-600">Este enlace se comparte con clientes para entrar a la mesa.</p>
                <p class="mt-4 break-all rounded-2xl bg-zinc-100 p-3 text-xs text-zinc-700">{{ $table->qrUrl() }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.tables.qr.download', $table) }}" class="gh-btn gh-btn-secondary min-h-10 px-3">Descargar QR</a>
                    <a href="{{ route('admin.tables.qr.print', $table) }}" target="_blank" rel="noopener noreferrer" class="gh-btn gh-btn-secondary min-h-10 px-3">Imprimir</a>
                </div>
            @else
                <p class="mt-2 text-sm leading-6 text-zinc-600">El token QR se genera automaticamente al crear la mesa.</p>
            @endif
        </aside>
    </form>
@endsection
