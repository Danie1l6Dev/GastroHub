@extends('layouts.admin', ['title' => $product->exists ? 'Editar producto' : 'Nuevo producto'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Catalogo</p>
            <h1 class="gh-page-title">{{ $product->exists ? 'Editar producto' : 'Nuevo producto' }}</h1>
            <p class="gh-page-copy">Mantén el menu claro, visual y listo para pedir desde la mesa.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="gh-btn gh-btn-secondary">Volver</a>
    </div>

    @if ($errors->any())
        <x-alert type="error" class="mt-6">Revisa los campos marcados antes de guardar.</x-alert>
    @endif

    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem]">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <section class="gh-panel space-y-5">
            <div>
                <label class="gh-label" for="category_id">Categoria</label>
                <select id="category_id" name="category_id" class="gh-field" required>
                    <option value="">Selecciona una categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="gh-error">{{ $message }}</p> @enderror
            </div>

            <x-form-input label="Nombre" name="name" :value="$product->name" required placeholder="Ej. Arroz meloso de mar" />
            <x-form-textarea label="Descripcion" name="description" :value="$product->description" rows="4" help="Describe sabor, ingredientes y preparacion en una frase corta." />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-form-input label="Precio en pesos" name="price" type="number" min="100" step="100" :value="$product->price ?? 0" required />
                <x-form-input label="Orden" name="sort_order" type="number" min="0" :value="$product->sort_order ?? $product->position ?? 0" required />
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex min-h-14 items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-medium">
                    <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available ?? true)) class="rounded border-zinc-300 text-emerald-600">
                    Disponible para pedir
                </label>

                <label class="flex min-h-14 items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-medium">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false)) class="rounded border-zinc-300 text-amber-600">
                    Mostrar como destacado
                </label>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-button>Guardar producto</x-button>
                <a href="{{ route('admin.products.index') }}" class="gh-btn gh-btn-secondary">Cancelar</a>
            </div>
        </section>

        <aside class="gh-panel self-start">
            <p class="text-sm font-semibold">Imagen del producto</p>
            <p class="mt-1 text-xs leading-5 text-zinc-500">Usa una imagen horizontal, bien iluminada y de buena calidad.</p>
            <label for="image" class="mt-4 block cursor-pointer rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-3 transition hover:bg-zinc-100">
                <img data-image-preview src="{{ $product->exists ? $product->imageUrl() : asset('images/restaurant-hero.png') }}" alt="Vista previa del producto" class="aspect-[4/3] w-full rounded-xl object-cover">
                <span class="mt-3 block text-center text-sm font-semibold text-zinc-700">Seleccionar imagen</span>
            </label>
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" data-image-input>
            @error('image') <p class="gh-error">{{ $message }}</p> @enderror
        </aside>
    </form>

    <script>
        (() => {
            const input = document.querySelector('[data-image-input]');
            const preview = document.querySelector('[data-image-preview]');
            input?.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file || !preview) return;
                preview.src = URL.createObjectURL(file);
            });
        })();
    </script>
@endsection
