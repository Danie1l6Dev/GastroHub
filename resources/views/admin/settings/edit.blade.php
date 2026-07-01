@extends('layouts.admin', ['title' => 'Configuracion'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Configuracion</p>
            <h1 class="gh-page-title">Identidad publica</h1>
            <p class="gh-page-copy">Personaliza como se ve el restaurante en la pagina publica y en la experiencia QR.</p>
        </div>
        <a href="{{ route('home') }}" class="gh-btn gh-btn-secondary">Ver pagina publica</a>
    </div>

    @if ($errors->any())
        <x-alert type="error" class="mt-6">Revisa los campos marcados antes de guardar.</x-alert>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 xl:grid-cols-[1fr_24rem]">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            <section class="gh-panel space-y-5">
                <div>
                    <p class="text-lg font-semibold">Informacion general</p>
                    <p class="mt-1 text-sm text-zinc-500">Nombre, descripcion y estado visible para clientes.</p>
                </div>
                <x-form-input label="Nombre" name="name" :value="$setting->name" required />
                <x-form-textarea label="Descripcion" name="description" :value="$setting->description" rows="5" />
                <label class="flex min-h-14 items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-medium">
                    <input type="checkbox" name="is_open" value="1" @checked(old('is_open', $setting->is_open)) class="rounded border-zinc-300 text-brand-red">
                    Restaurante abierto
                </label>
            </section>

            <section class="gh-panel space-y-5">
                <div>
                    <p class="text-lg font-semibold">Contacto y horarios</p>
                    <p class="mt-1 text-sm text-zinc-500">Datos que aparecen en la home y ayudan a cerrar la visita.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form-input label="Direccion" name="address" :value="$setting->address" />
                    <x-form-input label="Telefono" name="phone" :value="$setting->phone" />
                </div>
                <x-form-input label="Horarios" name="opening_hours" :value="$setting->opening_hours" />
                <x-form-input label="Instagram" name="instagram_url" type="url" :value="$setting->instagram_url" placeholder="https://instagram.com/tu_restaurante" />
            </section>

            <section class="gh-panel space-y-5">
                <div>
                    <p class="text-lg font-semibold">Identidad visual</p>
                    <p class="mt-1 text-sm text-zinc-500">Colores e imagenes usados en la pagina publica.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form-input label="Color principal" name="primary_color" type="color" :value="$setting->safePrimaryColor()" required />
                    <x-form-input label="Color secundario" name="secondary_color" type="color" :value="$setting->safeSecondaryColor()" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="gh-label" for="logo">Logo</label>
                        <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="gh-field bg-white">
                        @error('logo') <p class="gh-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="gh-label" for="cover_image">Imagen de portada</label>
                        <input id="cover_image" name="cover_image" type="file" accept="image/png,image/jpeg,image/webp" class="gh-field bg-white">
                        @error('cover_image') <p class="gh-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button>Guardar configuracion</x-button>
                    <a href="{{ route('admin.dashboard') }}" class="gh-btn gh-btn-secondary">Cancelar</a>
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="gh-panel">
                <p class="text-sm font-semibold">Vista previa</p>
                <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white">
                    <img src="{{ $setting->coverImageUrl() }}" alt="Portada actual" class="aspect-video w-full object-cover">
                    <div class="p-4" style="--brand-primary: {{ $setting->safePrimaryColor() }}; --brand-secondary: {{ $setting->safeSecondaryColor() }};">
                        @if ($setting->logoUrl())
                            <img src="{{ $setting->logoUrl() }}" alt="Logo actual" class="mb-3 h-12 w-12 rounded-2xl object-cover">
                        @endif
                        <p class="text-lg font-semibold">{{ $setting->name }}</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $setting->description }}</p>
                        <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: var(--brand-primary);">
                            {{ $setting->is_open ? 'Abierto' : 'Cerrado' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="gh-panel">
                <p class="text-sm font-semibold">Paleta</p>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-brand-red p-4 text-sm font-semibold text-white">Rojo principal</div>
                    <div class="rounded-2xl bg-brand-orange p-4 text-sm font-semibold text-brand-ink">Naranja acento</div>
                    <div class="rounded-2xl bg-brand-brown p-4 text-sm font-semibold text-white">Marron apoyo</div>
                    <div class="rounded-2xl bg-brand-ink p-4 text-sm font-semibold text-white">Negro contraste</div>
                </div>
            </section>
        </aside>
    </form>
@endsection
