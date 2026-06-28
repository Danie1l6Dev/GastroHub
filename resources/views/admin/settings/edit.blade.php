@extends('layouts.admin', ['title' => 'Configuracion del restaurante'])

@section('content')
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Configuracion</p>
            <h1 class="mt-2 text-3xl font-semibold">Identidad publica</h1>
            <p class="mt-2 text-sm text-zinc-600">Estos datos alimentan la pagina principal del restaurante.</p>
        </div>
        <a href="{{ route('home') }}" class="rounded-md border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Ver pagina publica</a>
    </div>

    @if ($errors->any())
        <x-alert type="error" class="mt-6">
            Revisa los campos marcados antes de guardar.
        </x-alert>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-[1fr_20rem]">
        @csrf
        @method('PUT')

        <section class="space-y-5 rounded-md border border-zinc-200 bg-white p-5">
            <x-form-input label="Nombre" name="name" :value="$setting->name" required />

            <div>
                <label class="text-sm font-medium" for="description">Descripcion</label>
                <textarea id="description" name="description" rows="5" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ old('description', $setting->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-form-input label="Direccion" name="address" :value="$setting->address" />
                <x-form-input label="Telefono" name="phone" :value="$setting->phone" />
            </div>

            <x-form-input label="Horarios" name="opening_hours" :value="$setting->opening_hours" />
            <x-form-input label="Instagram" name="instagram_url" type="url" :value="$setting->instagram_url" placeholder="https://instagram.com/tu_restaurante" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-form-input label="Color principal" name="primary_color" type="color" :value="$setting->safePrimaryColor()" required />
                <x-form-input label="Color secundario" name="secondary_color" type="color" :value="$setting->safeSecondaryColor()" required />
            </div>

            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="is_open" value="1" @checked(old('is_open', $setting->is_open)) class="rounded border-zinc-300">
                Restaurante abierto
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium" for="logo">Logo</label>
                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                    @error('logo') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium" for="cover_image">Imagen de portada</label>
                    <input id="cover_image" name="cover_image" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                    @error('cover_image') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-button>Guardar configuracion</x-button>
                <a href="{{ route('admin.dashboard') }}" class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Cancelar</a>
            </div>
        </section>

        <aside class="space-y-4">
            <div class="rounded-md border border-zinc-200 bg-white p-5">
                <p class="text-sm font-semibold">Vista previa</p>
                <div class="mt-4 overflow-hidden rounded-md border border-zinc-200">
                    <img src="{{ $setting->coverImageUrl() }}" alt="Portada actual" class="aspect-video w-full object-cover">
                    <div class="p-4" style="--brand-primary: {{ $setting->safePrimaryColor() }}; --brand-secondary: {{ $setting->safeSecondaryColor() }};">
                        @if ($setting->logoUrl())
                            <img src="{{ $setting->logoUrl() }}" alt="Logo actual" class="mb-3 h-12 w-12 rounded-md object-cover">
                        @endif
                        <p class="text-lg font-semibold">{{ $setting->name }}</p>
                        <p class="mt-2 text-sm text-zinc-600">{{ $setting->description }}</p>
                        <span class="mt-3 inline-flex rounded-md px-3 py-1 text-xs font-semibold text-white" style="background-color: var(--brand-primary);">
                            {{ $setting->is_open ? 'Abierto' : 'Cerrado' }}
                        </span>
                    </div>
                </div>
            </div>
        </aside>
    </form>
@endsection
