@extends('layouts.admin', ['title' => $category->exists ? 'Editar categoria' : 'Nueva categoria'])

@section('content')
    <h1 class="text-3xl font-semibold">{{ $category->exists ? 'Editar categoria' : 'Nueva categoria' }}</h1>

    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="mt-6 max-w-2xl space-y-5 rounded-md border border-zinc-200 bg-white p-5">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif

        <x-form-input label="Nombre" name="name" :value="$category->name" required />

        <div>
            <label class="text-sm font-medium" for="description">Descripcion</label>
            <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2">{{ old('description', $category->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <x-form-input label="Orden" name="sort_order" type="number" min="0" :value="$category->sort_order ?? $category->position ?? 0" required />

        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="rounded border-zinc-300">
            Activa
        </label>

        <div class="flex gap-2">
            <x-button>Guardar</x-button>
            <a href="{{ route('admin.categories.index') }}" class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Cancelar</a>
        </div>
    </form>
@endsection
