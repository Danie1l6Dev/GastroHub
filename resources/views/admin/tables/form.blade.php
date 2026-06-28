@extends('layouts.admin', ['title' => $table->exists ? 'Editar mesa' : 'Nueva mesa'])

@section('content')
    <h1 class="text-3xl font-semibold">{{ $table->exists ? 'Editar mesa' : 'Nueva mesa' }}</h1>

    <form method="POST" action="{{ $table->exists ? route('admin.tables.update', $table) : route('admin.tables.store') }}" class="mt-6 max-w-2xl space-y-5 rounded-md border border-zinc-200 bg-white p-5">
        @csrf
        @if ($table->exists)
            @method('PUT')
        @endif

        <x-form-input label="Nombre visible" name="name" :value="$table->name" required />
        <x-form-input label="Codigo interno" name="code" :value="$table->code" required />

        <div>
            <label class="text-sm font-medium" for="capacity">Capacidad</label>
            <input id="capacity" name="capacity" type="number" min="1" max="30" value="{{ old('capacity', $table->capacity) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2">
            @error('capacity') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium" for="current_status">Estado actual</label>
            <select id="current_status" name="current_status" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('current_status', $table->current_status?->value ?? 'available') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @error('current_status') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        @if ($table->exists)
            <div>
                <p class="text-sm font-medium">URL QR</p>
                <p class="mt-1 rounded-md bg-zinc-100 p-3 text-xs text-zinc-700">{{ $table->qrUrl() }}</p>
            </div>
        @endif

        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $table->is_active ?? true)) class="rounded border-zinc-300">
            Activa
        </label>

        <div class="flex gap-2">
            <x-button>Guardar</x-button>
            <a href="{{ route('admin.tables.index') }}" class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Cancelar</a>
        </div>
    </form>
@endsection
