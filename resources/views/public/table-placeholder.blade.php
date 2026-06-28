@extends('layouts.app', ['title' => 'Mesa '.$table->name])

@section('content')
    <section
        class="mx-auto max-w-6xl px-4 py-5 sm:py-8"
        data-table-app
        data-state-url="{{ route('tables.state', $table->qr_token) }}"
        data-join-url="{{ route('tables.join.store', $table->qr_token) }}"
        data-release-url="{{ route('tables.guest.release', $table->qr_token) }}"
        data-select-guest-url="{{ route('tables.guests.select', [$table->qr_token, '__guest__']) }}"
        data-items-url="{{ route('tables.items', $table->qr_token) }}"
        data-initial-alias="{{ $alias }}"
        data-initial-guest-id="{{ $guestId }}"
    >
        <div class="mb-5 flex flex-col gap-4 sm:mb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Mesa compartida</p>
                <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">{{ $table->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600">Todos ven quienes estan en la mesa, que pidio cada persona y el total general.</p>
            </div>
            <a href="{{ route('menu') }}" class="inline-flex min-h-11 items-center justify-center rounded-md border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50">Ver menu completo</a>
        </div>

        <div data-error class="mb-4 hidden rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"></div>

        <div class="grid gap-4 lg:grid-cols-[20rem_1fr] lg:gap-5">
            <aside class="space-y-4 lg:sticky lg:top-5 lg:self-start">
                <section class="rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <h2 class="text-lg font-semibold text-zinc-950">Tu alias</h2>
                    <form data-alias-form class="mt-4 space-y-3">
                        <div>
                            <label class="text-sm font-medium text-zinc-800" for="alias">Nombre o alias</label>
                            <input id="alias" name="alias" type="text" maxlength="80" value="{{ $alias }}" placeholder="Ej. Laura" class="mt-1 min-h-11 w-full rounded-md border border-zinc-300 bg-zinc-50 px-3 py-2 text-base outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100 sm:text-sm">
                        </div>
                        <button class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98] sm:w-auto">Continuar</button>
                    </form>
                    <div data-current-guest class="mt-4 hidden rounded-md border border-emerald-200 bg-emerald-50 p-3">
                        <p class="text-sm text-emerald-900">Ingresaste como</p>
                        <p data-current-alias class="mt-1 text-lg font-semibold text-emerald-950"></p>
                        <button type="button" data-release-guest class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-md border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-100 active:scale-[0.98]">
                            Listo, agregar otra persona
                        </button>
                    </div>
                </section>

                <section class="rounded-md border border-zinc-200 bg-zinc-950 p-4 text-white shadow-sm sm:p-5 lg:hidden">
                    <p class="text-sm text-zinc-300">Total de la mesa</p>
                    <p data-table-total-mobile class="mt-1 text-3xl font-semibold tabular-nums">$0</p>
                </section>

                <section class="rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-zinc-950">Personas</h2>
                        <span data-guest-count class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600">0</span>
                    </div>
                    <div data-guests class="mt-4 space-y-3"></div>
                </section>

                <section class="hidden rounded-md border border-zinc-200 bg-zinc-950 p-5 text-white shadow-sm lg:block">
                    <p class="text-sm text-zinc-300">Total de la mesa</p>
                    <p data-table-total class="mt-2 text-3xl font-semibold tabular-nums">$0</p>
                </section>
            </aside>

            <div class="space-y-4">
                <section class="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-100 p-4 sm:p-5">
                        <h2 class="text-xl font-semibold text-zinc-950">Seleccionar platos</h2>
                        <p class="mt-1 text-sm leading-6 text-zinc-600">Elige una seccion y agrega tus platos. Los agotados se ven, pero no se pueden seleccionar.</p>
                    </div>
                    <div data-products class="p-4 sm:p-5"></div>
                </section>

                <section class="rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <h2 class="text-lg font-semibold text-zinc-950">Detalle compartido</h2>
                    <div data-breakdown class="mt-4 space-y-4"></div>
                </section>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const root = document.querySelector('[data-table-app]');
            if (!root) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const stateUrl = root.dataset.stateUrl;
            const joinUrl = root.dataset.joinUrl;
            const releaseUrl = root.dataset.releaseUrl;
            const selectGuestUrl = root.dataset.selectGuestUrl;
            const itemsUrl = root.dataset.itemsUrl;
            let currentGuestId = root.dataset.initialGuestId ? Number(root.dataset.initialGuestId) : null;
            let selectedCategoryId = null;
            let state = null;

            const money = (value) => new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                maximumFractionDigits: 0,
            }).format(value);

            const setError = (message = '') => {
                const box = root.querySelector('[data-error]');
                box.textContent = message;
                box.classList.toggle('hidden', !message);
            };

            const escapeHtml = (value = '') => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const request = async (url, options = {}) => {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        ...(options.headers || {}),
                    },
                    credentials: 'same-origin',
                    ...options,
                });

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text || 'No se pudo completar la accion.');
                }

                return response.json();
            };

            const loadState = async () => {
                try {
                    state = await request(stateUrl, { method: 'GET', headers: { 'Content-Type': 'application/json' } });
                    currentGuestId = state.current_guest_id;
                    render();
                    setError('');
                } catch (error) {
                    setError('No se pudo actualizar la mesa.');
                }
            };

            const render = () => {
                if (!state) return;

                const currentGuest = state.guests.find((guest) => guest.id === currentGuestId);
                root.querySelector('[data-current-guest]').classList.toggle('hidden', !currentGuest);
                root.querySelector('[data-current-alias]').textContent = currentGuest?.alias || '';
                root.querySelector('[data-guest-count]').textContent = state.guests.length;
                root.querySelectorAll('[data-table-total], [data-table-total-mobile]').forEach((node) => {
                    node.textContent = state.total_formatted || money(state.total);
                });

                renderGuests();
                renderProducts();
                renderBreakdown();
            };

            const renderGuests = () => {
                const target = root.querySelector('[data-guests]');
                target.innerHTML = state.guests.length
                    ? state.guests.map((guest) => `
                        <div class="rounded-md border p-3 ${guest.id === currentGuestId ? 'border-emerald-200 bg-emerald-50' : 'border-zinc-200 bg-white'}">
                            <div class="flex items-center justify-between gap-3">
                                <p class="min-w-0 truncate font-semibold text-zinc-950">${escapeHtml(guest.alias)}</p>
                                <p class="text-sm font-semibold tabular-nums">${guest.subtotal_formatted}</p>
                            </div>
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-zinc-500">${guest.items.length} productos seleccionados</p>
                                <button
                                    type="button"
                                    data-select-guest="${guest.id}"
                                    class="inline-flex min-h-9 items-center justify-center rounded-md border px-3 py-1.5 text-xs font-semibold transition active:scale-[0.98] ${guest.id === currentGuestId ? 'border-emerald-200 bg-white text-emerald-950' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:bg-zinc-100'}"
                                >
                                    ${guest.id === currentGuestId ? 'Editando' : 'Editar pedido'}
                                </button>
                            </div>
                        </div>
                    `).join('')
                    : '<p class="text-sm text-zinc-500">Aun no hay personas en esta mesa.</p>';
            };

            const renderProducts = () => {
                const target = root.querySelector('[data-products]');
                if (!state.categories.length) {
                    target.innerHTML = '<p class="rounded-md bg-zinc-50 p-4 text-sm text-zinc-500">Todavia no hay secciones del menu disponibles.</p>';
                    return;
                }

                const selectedCategoryExists = state.categories.some((category) => category.id === selectedCategoryId);
                if (!selectedCategoryId || !selectedCategoryExists) {
                    selectedCategoryId = state.categories[0].id;
                }

                const selectedCategory = state.categories.find((category) => category.id === selectedCategoryId);
                const products = selectedCategory?.products || [];

                target.innerHTML = `
                    <div class="overflow-x-auto border-b border-zinc-100 pb-4">
                        <div class="flex min-w-max gap-2" role="tablist" aria-label="Secciones del menu">
                            ${state.categories.map((category) => `
                                <button
                                    type="button"
                                    data-category="${category.id}"
                                    class="min-h-11 rounded-md border px-4 py-2 text-sm font-semibold transition active:scale-[0.98] ${category.id === selectedCategoryId ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50'}"
                                    role="tab"
                                    aria-selected="${category.id === selectedCategoryId ? 'true' : 'false'}"
                                >
                                    ${escapeHtml(category.name)}
                                    <span class="ml-1 text-xs opacity-70">${category.products.length}</span>
                                </button>
                            `).join('')}
                        </div>
                    </div>

                    <section class="pt-5">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-950">${escapeHtml(selectedCategory.name)}</h3>
                                <p class="mt-1 text-sm text-zinc-500">${products.length} platos en esta seccion</p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            ${products.length ? products.map((product) => productCard(product)).join('') : '<p class="rounded-md bg-zinc-50 p-4 text-sm text-zinc-500 md:col-span-2">Esta seccion aun no tiene productos.</p>'}
                        </div>
                    </section>
                `;
            };

            const productCard = (product) => {
                const selected = currentGuestProductQuantity(product.id);
                const description = product.description ? `<p class="mt-1 line-clamp-2 text-sm leading-5 text-zinc-600">${escapeHtml(product.description)}</p>` : '';
                const imageUrl = escapeHtml(product.image_url || '');

                return `
                    <article class="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm ${product.is_available ? '' : 'opacity-75'}">
                        <div class="flex flex-col gap-3 p-3 sm:flex-row">
                            <img src="${imageUrl}" alt="${escapeHtml(product.name)}" loading="lazy" class="pointer-events-none h-40 w-full shrink-0 rounded-md object-cover outline outline-1 -outline-offset-1 outline-black/10 sm:h-28 sm:w-28">
                            <div class="flex min-w-0 flex-1 flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-semibold leading-5 text-zinc-950">${escapeHtml(product.name)}</h4>
                                        ${description}
                                    </div>
                                    ${product.is_available ? '' : '<span class="shrink-0 rounded-md bg-zinc-200 px-2 py-1 text-[11px] font-semibold text-zinc-700">Agotado</span>'}
                                </div>
                                <div class="mt-auto flex flex-col gap-3 pt-3 sm:flex-row sm:items-end sm:justify-between">
                                    <p class="text-sm font-semibold tabular-nums text-zinc-950">${product.price_formatted}</p>
                                    ${product.is_available
                                        ? `<div class="relative z-10 flex w-full items-center justify-between gap-1 rounded-md border border-zinc-200 bg-zinc-50 p-1 sm:w-auto">
                                            <button aria-label="Quitar ${escapeHtml(product.name)}" data-delta="-1" data-product="${product.id}" class="relative z-10 flex h-11 w-11 items-center justify-center rounded-md border border-zinc-200 bg-white text-lg font-semibold text-zinc-800 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-35" ${!currentGuestId || selected === 0 ? 'disabled' : ''}>-</button>
                                            <span class="pointer-events-none min-w-8 text-center text-sm font-semibold tabular-nums text-zinc-950">${selected}</span>
                                            <button aria-label="Agregar ${escapeHtml(product.name)}" data-delta="1" data-product="${product.id}" class="relative z-10 flex h-11 w-11 items-center justify-center rounded-md bg-zinc-950 text-lg font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-35" ${!currentGuestId ? 'disabled' : ''}>+</button>
                                        </div>`
                                        : '<span class="rounded-md bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">No disponible</span>'
                                    }
                                </div>
                            </div>
                        </div>
                    </article>
                `;
            };

            const renderBreakdown = () => {
                const target = root.querySelector('[data-breakdown]');
                target.innerHTML = state.guests.length
                    ? state.guests.map((guest) => `
                        <section class="rounded-md border border-zinc-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="min-w-0 truncate font-semibold text-zinc-950">${escapeHtml(guest.alias)}</h3>
                                <p class="font-semibold tabular-nums">${guest.subtotal_formatted}</p>
                            </div>
                            <div class="mt-3 divide-y divide-zinc-100">
                                ${guest.items.length ? guest.items.map((item) => `
                                    <div class="flex items-center justify-between gap-3 py-2 text-sm">
                                        <p class="min-w-0">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                        <p class="font-medium tabular-nums">${item.subtotal_formatted}</p>
                                    </div>
                                `).join('') : '<p class="py-2 text-sm text-zinc-500">Sin platos seleccionados.</p>'}
                            </div>
                        </section>
                    `).join('')
                    : '<p class="text-sm text-zinc-500">Cuando alguien agregue su alias, aparecera aqui.</p>';
            };

            const currentGuestProductQuantity = (productId) => {
                const guest = state.guests.find((item) => item.id === currentGuestId);
                if (!guest) return 0;
                const item = guest.items.find((entry) => entry.product_id === productId);
                return item ? item.quantity : 0;
            };

            root.querySelector('[data-alias-form]').addEventListener('submit', async (event) => {
                event.preventDefault();
                const alias = new FormData(event.currentTarget).get('alias');

                try {
                    state = await request(joinUrl, {
                        method: 'POST',
                        body: JSON.stringify({ alias }),
                    });
                    currentGuestId = state.current_guest_id;
                    root.querySelector('#alias').value = state.guests.find((guest) => guest.id === currentGuestId)?.alias || alias;
                    render();
                    setError('');
                } catch (error) {
                    setError('Escribe un alias valido para continuar.');
                }
            });

            root.addEventListener('click', async (event) => {
                const releaseButton = event.target.closest('[data-release-guest]');
                if (releaseButton) {
                    try {
                        releaseButton.disabled = true;
                        state = await request(releaseUrl, { method: 'POST' });
                        currentGuestId = null;
                        root.querySelector('#alias').value = '';
                        render();
                        setError('');
                    } catch (error) {
                        setError('No se pudo preparar el dispositivo para otra persona.');
                    } finally {
                        releaseButton.disabled = false;
                    }

                    return;
                }

                const selectGuestButton = event.target.closest('[data-select-guest]');
                if (selectGuestButton) {
                    try {
                        selectGuestButton.disabled = true;
                        const guestId = Number(selectGuestButton.dataset.selectGuest);
                        state = await request(selectGuestUrl.replace('__guest__', guestId), { method: 'POST' });
                        currentGuestId = state.current_guest_id;
                        root.querySelector('#alias').value = state.guests.find((guest) => guest.id === currentGuestId)?.alias || '';
                        render();
                        setError('');
                    } catch (error) {
                        setError('No se pudo traer el pedido de esa persona.');
                    } finally {
                        selectGuestButton.disabled = false;
                    }

                    return;
                }

                const categoryButton = event.target.closest('[data-category]');
                if (categoryButton) {
                    selectedCategoryId = Number(categoryButton.dataset.category);
                    renderProducts();
                    return;
                }

                const button = event.target.closest('[data-product][data-delta]');
                if (!button || button.disabled) return;

                try {
                    button.disabled = true;
                    state = await request(itemsUrl, {
                        method: 'POST',
                        body: JSON.stringify({
                            product_id: Number(button.dataset.product),
                            delta: Number(button.dataset.delta),
                        }),
                    });
                    currentGuestId = state.current_guest_id;
                    render();
                    setError('');
                } catch (error) {
                    setError('Primero ingresa tu alias o revisa si el producto esta disponible.');
                }
            });

            loadState();
            setInterval(loadState, 3000);
        })();
    </script>
@endsection
