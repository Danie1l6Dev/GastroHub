@extends('layouts.admin', ['title' => $category->exists ? 'Editar categoria' : 'Nueva categoria'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Menu</p>
            <h1 class="gh-page-title">{{ $category->exists ? 'Editar categoria' : 'Nueva categoria' }}</h1>
            <p class="gh-page-copy">Define nombre, descripcion y orden de aparicion en el menu.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="gh-btn gh-btn-secondary">Volver</a>
    </div>

    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="gh-panel mt-6 max-w-2xl space-y-5">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif

        <x-form-input label="Nombre" name="name" :value="$category->name" required placeholder="Ej. Fuertes" />
        <x-form-textarea label="Descripcion" name="description" :value="$category->description" rows="3" help="Texto corto que ayuda al cliente a entender esta seccion." />
        <x-form-input label="Orden" name="sort_order" type="number" min="0" :value="$category->sort_order ?? $category->position ?? 0" required />

        <label class="flex min-h-14 items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-medium">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="rounded border-zinc-300 text-brand-red">
            Categoria visible en el menu publico
        </label>

        <div class="flex flex-wrap gap-2">
            <x-button>Guardar categoria</x-button>
            <a href="{{ route('admin.categories.index') }}" class="gh-btn gh-btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection
