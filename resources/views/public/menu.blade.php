@extends('layouts.app', ['title' => 'Menu | '.($restaurant->name ?? 'GastroHub')])

@section('content')
    @php
        $primaryColor = $restaurant?->safePrimaryColor() ?? '#059669';
    @endphp

    <section class="mx-auto max-w-6xl px-4 py-8 sm:py-10" data-menu-page>
        <div class="rounded-3xl bg-zinc-950 p-6 text-white shadow-xl shadow-zinc-950/10 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_22rem] lg:items-end">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300">Menu digital</p>
                    <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">{{ $restaurant->name ?? 'GastroHub' }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-zinc-300">Explora platos disponibles, agotados y favoritos antes de pedir desde una mesa QR.</p>
                </div>
                <label class="block">
                    <span class="text-sm font-medium text-zinc-200">Buscar producto</span>
                    <input data-menu-search type="search" placeholder="Ej. limonada, arroz, brownie" class="mt-2 min-h-12 w-full rounded-2xl border border-white/10 bg-white/10 px-4 text-sm text-white placeholder:text-zinc-400 outline-none transition focus:border-emerald-300 focus:bg-white/15">
                </label>
            </div>
        </div>

        @if ($categories->isNotEmpty())
            <div class="sticky top-[4.25rem] z-30 mt-5" data-menu-category-select data-open="false">
                <div class="relative">
                    <button type="button" data-menu-category-toggle class="flex min-h-12 w-full items-center justify-between gap-3 rounded-2xl border border-zinc-200/80 bg-white/90 px-4 py-3 text-left text-sm font-semibold text-zinc-950 shadow-sm shadow-zinc-950/[0.04] backdrop-blur-xl transition hover:bg-white active:scale-[0.99]" aria-expanded="false">
                        <span class="min-w-0">
                            <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Seccion</span>
                            <span data-menu-category-label class="block truncate">{{ $categories->first()->name }}</span>
                        </span>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-lg leading-none text-zinc-700" aria-hidden="true">v</span>
                    </button>

                    <nav class="gh-category-select-panel absolute left-0 right-0 top-full z-40 mt-2 rounded-2xl border border-zinc-200 bg-white p-2 shadow-xl shadow-zinc-950/10" aria-label="Categorias del menu">
                        @foreach ($categories as $category)
                            <a href="#{{ $category->slug }}" data-menu-category-option data-category-label="{{ $category->name }}" class="flex min-h-11 items-center justify-between gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 focus:bg-zinc-950 focus:text-white">
                                <span class="min-w-0 truncate">{{ $category->name }}</span>
                                <span class="shrink-0 rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-500">{{ $category->visibleProducts->count() }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        @endif

        <div class="mt-8 space-y-12">
            @forelse ($categories as $category)
                <section id="{{ $category->slug }}" class="scroll-mt-32" data-menu-category>
                    <div class="flex flex-col gap-2 border-b border-zinc-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-3xl font-semibold tracking-tight">{{ $category->name }}</h2>
                            @if ($category->description)
                                <p class="mt-1 text-sm leading-6 text-zinc-600">{{ $category->description }}</p>
                            @endif
                        </div>
                        <x-badge tone="neutral">{{ $category->visibleProducts->count() }} productos</x-badge>
                    </div>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        @forelse ($category->visibleProducts as $product)
                            <article class="group overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-950/[0.04] transition duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-950/[0.08]" data-menu-product data-search="{{ mb_strtolower($product->name.' '.$product->description) }}">
                                <div class="grid sm:grid-cols-[11rem_1fr]">
                                    <div class="relative overflow-hidden">
                                        <img loading="lazy" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-[4/3] h-full w-full object-cover transition duration-300 group-hover:scale-105 sm:aspect-auto">
                                        @unless ($product->is_available)
                                            <div class="absolute inset-0 grid place-items-center bg-zinc-950/55">
                                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-zinc-950">Agotado</span>
                                            </div>
                                        @endunless
                                    </div>
                                    <div class="flex min-w-0 flex-col p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @if ($product->is_featured)
                                                        <x-badge tone="warning">Destacado</x-badge>
                                                    @endif
                                                    @unless ($product->is_available)
                                                        <x-badge>Agotado</x-badge>
                                                    @endunless
                                                </div>
                                                <h3 class="mt-2 text-lg font-semibold text-zinc-950">{{ $product->name }}</h3>
                                            </div>
                                            <p class="shrink-0 text-lg gh-price">{{ $product->formattedPrice() }}</p>
                                        </div>
                                        <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>
                                        <div class="mt-4 flex items-center justify-between gap-3">
                                            <span class="text-xs font-medium uppercase tracking-[0.14em]" style="color: {{ $primaryColor }};">{{ $category->name }}</span>
                                            <span class="gh-btn {{ $product->is_available ? 'gh-btn-secondary' : 'border border-zinc-200 bg-zinc-100 text-zinc-400' }} min-h-10 px-3">
                                                {{ $product->is_available ? 'Disponible' : 'No disponible' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-empty-state title="Sin productos en esta categoria" description="Agrega productos desde el panel administrativo." class="md:col-span-2" />
                        @endforelse
                    </div>
                </section>
            @empty
                <x-empty-state title="Aun no hay categorias activas" description="Activa categorias desde el panel para mostrar el menu." />
            @endforelse
        </div>

        <x-empty-state data-menu-empty class="mt-8 hidden" title="No encontramos productos" description="Prueba con otro nombre o borra la busqueda." />
    </section>

    <script>
        (() => {
            const root = document.querySelector('[data-menu-page]');
            if (!root) return;

            const search = root.querySelector('[data-menu-search]');
            const products = [...root.querySelectorAll('[data-menu-product]')];
            const categories = [...root.querySelectorAll('[data-menu-category]')];
            const empty = root.querySelector('[data-menu-empty]');
            const categorySelect = root.querySelector('[data-menu-category-select]');
            const categoryToggle = root.querySelector('[data-menu-category-toggle]');
            const categoryLabel = root.querySelector('[data-menu-category-label]');

            const closeCategorySelect = () => {
                categorySelect?.setAttribute('data-open', 'false');
                categoryToggle?.setAttribute('aria-expanded', 'false');
            };

            categoryToggle?.addEventListener('click', () => {
                const isOpen = categorySelect?.dataset.open === 'true';
                categorySelect?.setAttribute('data-open', isOpen ? 'false' : 'true');
                categoryToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });

            root.querySelectorAll('[data-menu-category-option]').forEach((option) => {
                option.addEventListener('click', () => {
                    categoryLabel.textContent = option.dataset.categoryLabel || option.textContent.trim();
                    closeCategorySelect();
                });
            });

            document.addEventListener('click', (event) => {
                if (!categorySelect || categorySelect.contains(event.target)) return;

                closeCategorySelect();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeCategorySelect();
                }
            });

            search?.addEventListener('input', () => {
                const query = search.value.trim().toLowerCase();
                let visible = 0;

                products.forEach((product) => {
                    const matches = product.dataset.search.includes(query);
                    product.classList.toggle('hidden', !matches);
                    if (matches) visible++;
                });

                categories.forEach((category) => {
                    category.classList.toggle('hidden', category.querySelectorAll('[data-menu-product]:not(.hidden)').length === 0);
                });

                empty?.classList.toggle('hidden', visible > 0 || query === '');
            });
        })();
    </script>
@endsection
