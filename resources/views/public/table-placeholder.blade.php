@extends('layouts.app', ['title' => 'Mesa '.$table->name])

@section('content')
    <section
        class="mx-auto max-w-6xl px-4 py-5 sm:py-8"
        data-table-app
        data-state-url="{{ route('tables.state', $table->qr_token) }}"
        data-account-mode-url="{{ route('tables.account-mode', $table->qr_token) }}"
        data-join-url="{{ route('tables.join.store', $table->qr_token) }}"
        data-release-url="{{ route('tables.guest.release', $table->qr_token) }}"
        data-select-guest-url="{{ route('tables.guests.select', [$table->qr_token, '__guest__']) }}"
        data-items-url="{{ route('tables.items', $table->qr_token) }}"
        data-clear-cart-url="{{ route('tables.cart.clear', $table->qr_token) }}"
        data-ready-url="{{ route('tables.ready', $table->qr_token) }}"
        data-confirm-url="{{ route('tables.confirm', $table->qr_token) }}"
        data-initial-alias="{{ $alias }}"
        data-initial-guest-id="{{ $guestId }}"
        data-initial-guest-token="{{ $guestToken }}"
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

        <section data-joint-locked-panel class="mb-5 hidden rounded-md border border-amber-200 bg-amber-50 p-4 shadow-sm sm:p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Pago en conjunto activo</p>
            <h2 class="mt-2 text-2xl font-semibold text-amber-950">Puedes ver el menu de esta mesa</h2>
            <p class="mt-2 text-sm leading-6 text-amber-900">
                Dile a
                <span data-joint-owner class="font-semibold">la persona encargada</span>
                que pida lo que quieres.
            </p>
        </section>

        <section data-account-mode-panel class="mb-5 hidden rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Antes de pedir</p>
            <h2 class="mt-2 text-2xl font-semibold text-zinc-950">Como van a pagar?</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-600">Elige una opcion para esta cuenta. Si regeneras el QR, la mesa vuelve a empezar.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <button type="button" data-account-mode="joint" class="rounded-md border border-zinc-200 bg-zinc-950 p-4 text-left text-white transition hover:bg-zinc-800 active:scale-[0.99]">
                    <span class="block text-base font-semibold">Pago en conjunto</span>
                    <span class="mt-1 block text-sm leading-5 text-zinc-300">Una sola persona toma el pedido de toda la mesa.</span>
                </button>
                <button type="button" data-account-mode="separate" class="rounded-md border border-zinc-200 bg-white p-4 text-left transition hover:bg-zinc-50 active:scale-[0.99]">
                    <span class="block text-base font-semibold text-zinc-950">Cuentas separadas</span>
                    <span class="mt-1 block text-sm leading-5 text-zinc-600">Cada persona tiene alias, pedido y subtotal propio.</span>
                </button>
            </div>
        </section>

        <div data-workspace class="grid gap-4 lg:grid-cols-[20rem_1fr] lg:gap-5">
            <aside class="space-y-4 lg:sticky lg:top-5 lg:self-start">
                <section class="rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-zinc-950">Tu alias</h2>
                        <span data-account-mode-badge class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600"></span>
                    </div>
                    <form data-alias-form class="mt-4 space-y-3">
                        <div>
                            <label class="text-sm font-medium text-zinc-800" for="alias">Nombre o alias</label>
                            <input id="alias" name="alias" type="text" maxlength="80" value="{{ $alias }}" placeholder="Ej. Laura" class="mt-1 min-h-11 w-full rounded-md border border-zinc-300 bg-zinc-50 px-3 py-2 text-base outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100 sm:text-sm">
                        </div>
                        <button class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98] sm:w-auto">Continuar</button>
                    </form>
                    <p data-join-locked class="mt-4 hidden rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-950">
                        El pedido ya fue confirmado. No se pueden agregar mas personas a esta mesa.
                    </p>
                    <div data-current-guest class="mt-4 hidden rounded-md border border-emerald-200 bg-emerald-50 p-3">
                        <p class="text-sm text-emerald-900">Ingresaste como</p>
                        <p data-current-alias class="mt-1 text-lg font-semibold text-emerald-950"></p>
                        <button type="button" data-ready-toggle class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-md bg-emerald-950 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-900 active:scale-[0.98]">
                            Mi seleccion esta lista
                        </button>
                        <button type="button" data-release-guest class="mt-2 inline-flex min-h-10 w-full items-center justify-center rounded-md border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-100 active:scale-[0.98]">
                            Agregar otra sin marcar lista
                        </button>
                    </div>
                </section>

                <section class="rounded-md border border-zinc-200 bg-zinc-950 p-4 text-white shadow-sm sm:p-5 lg:hidden">
                    <p class="text-sm text-zinc-300">Total de la mesa</p>
                    <p data-table-total-mobile class="mt-1 text-3xl font-semibold tabular-nums">$0</p>
                </section>

                <section data-people-panel class="rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
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

                <section data-confirm-panel class="rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">Pedido final</p>
                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">Confirmacion</h2>
                        </div>
                        <span data-ready-summary class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600">0/0</span>
                    </div>
                    <p data-confirm-status class="mt-3 text-sm leading-6 text-zinc-600">Agrega tu alias para empezar.</p>
                    <button type="button" data-confirm-order class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-zinc-200 disabled:text-zinc-500">
                        Confirmar todo el pedido
                    </button>
                </section>
            </aside>

            <div class="space-y-4">
                <section class="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-100 p-4 sm:p-5">
                        <h2 class="text-xl font-semibold text-zinc-950">Seleccionar platos</h2>
                        <p class="mt-1 text-sm leading-6 text-zinc-600">Elige una seccion y agrega tus platos. Los agotados se ven, pero no se pueden seleccionar.</p>
                        <p data-selection-lock class="mt-3 hidden rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-950"></p>
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
            const accountModeUrl = root.dataset.accountModeUrl;
            const joinUrl = root.dataset.joinUrl;
            const releaseUrl = root.dataset.releaseUrl;
            const selectGuestUrl = root.dataset.selectGuestUrl;
            const itemsUrl = root.dataset.itemsUrl;
            const clearCartUrl = root.dataset.clearCartUrl;
            const readyUrl = root.dataset.readyUrl;
            const confirmUrl = root.dataset.confirmUrl;
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

                const needsAccountMode = state.requires_account_mode;
                const isJointMode = state.account_mode === 'joint';
                const isJointOrderLocked = Boolean(state.joint_order_locked);
                root.querySelector('[data-joint-locked-panel]').classList.toggle('hidden', !isJointOrderLocked);
                root.querySelector('[data-joint-owner]').textContent = state.joint_order_owner_alias || 'la persona encargada';
                root.querySelector('[data-account-mode-panel]').classList.toggle('hidden', !needsAccountMode || isJointOrderLocked);
                root.querySelector('[data-workspace]').classList.toggle('hidden', needsAccountMode);

                if (needsAccountMode) return;

                const currentGuest = state.guests.find((guest) => guest.id === currentGuestId);
                root.querySelector('[data-alias-form]').classList.toggle('hidden', state.order_confirmed || isJointOrderLocked);
                root.querySelector('[data-join-locked]').classList.toggle('hidden', !state.order_confirmed || isJointOrderLocked);
                root.querySelector('[data-current-guest]').classList.toggle('hidden', !currentGuest || isJointOrderLocked);
                root.querySelector('[data-current-alias]').textContent = currentGuest?.alias || '';
                root.querySelector('[data-release-guest]').hidden = isJointMode || state.order_confirmed || !currentGuest || currentGuest.is_ready;
                root.querySelector('[data-ready-toggle]').hidden = !currentGuest;
                root.querySelector('[data-ready-toggle]').textContent = currentGuest?.is_ready
                    ? state.order_confirmed
                        ? 'Pedir algo extra'
                        : 'Editar mi seleccion'
                    : isJointMode
                        ? 'Enviar mi pedido'
                        : 'Enviar mi pedido y agregar otra persona';
                root.querySelector('[data-people-panel]').classList.toggle('hidden', isJointMode);
                root.querySelector('[data-account-mode-badge]').textContent = state.account_mode_label || '';
                root.querySelector('[data-guest-count]').textContent = state.guests.length;
                root.querySelector('[data-confirm-panel]').classList.toggle('hidden', isJointOrderLocked);
                root.querySelectorAll('[data-table-total], [data-table-total-mobile]').forEach((node) => {
                    node.textContent = state.total_formatted || money(state.total);
                });

                renderGuests();
                renderProducts();
                renderBreakdown();
                renderConfirmation();
            };

            const renderGuests = () => {
                const target = root.querySelector('[data-guests]');
                target.innerHTML = state.guests.length
                    ? state.guests.map((guest) => `
                        <div class="rounded-md border p-3 ${guest.id === currentGuestId ? 'border-emerald-200 bg-emerald-50' : 'border-zinc-200 bg-white'}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-zinc-950">${escapeHtml(guest.display_alias || guest.alias)}</p>
                                    ${guest.is_alias_duplicate ? '<p class="mt-1 text-[11px] font-semibold text-amber-700">Alias repetido</p>' : ''}
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold tabular-nums">${guest.subtotal_formatted}</p>
                                    <p class="mt-1 text-[11px] font-semibold ${guest.is_ready ? 'text-emerald-700' : 'text-amber-700'}">${guest.is_ready ? 'Listo' : 'Pendiente'}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-zinc-500">${guest.items.length} en carrito · ${guest.orders.length} pedidos${guest.id === state.coordinator_guest_id ? ' · Encargado' : ''}</p>
                                <button
                                    type="button"
                                    data-select-guest="${guest.guest_token}"
                                    class="inline-flex min-h-9 items-center justify-center rounded-md border px-3 py-1.5 text-xs font-semibold transition active:scale-[0.98] ${guest.id === currentGuestId ? 'border-emerald-200 bg-white text-emerald-950' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:bg-zinc-100'}"
                                >
                                    ${guest.id === currentGuestId ? 'Seleccionado' : 'Seleccionar'}
                                </button>
                            </div>
                        </div>
                    `).join('')
                    : '<p class="text-sm text-zinc-500">Aun no hay personas en esta mesa.</p>';
            };

            const renderProducts = () => {
                const target = root.querySelector('[data-products]');
                const activeGuest = currentGuest();
                const selectionLock = root.querySelector('[data-selection-lock]');
                const lockedMessage = state.joint_order_locked
                    ? `Dile a ${escapeHtml(state.joint_order_owner_alias || 'la persona encargada')} que pida lo que quieres. Puedes revisar el menu mientras tanto.`
                    : state.order_confirmed
                    ? activeGuest?.is_ready
                        ? `Pedido confirmado por ${escapeHtml(state.confirmed_by_alias || 'la mesa')}. Toca "Pedir algo extra" para abrir otro carrito.`
                        : ''
                    : activeGuest?.is_ready
                        ? 'Tu pedido fue enviado. Toca "Editar mi seleccion" si quieres abrir otro carrito.'
                        : '';
                selectionLock.innerHTML = lockedMessage;
                selectionLock.classList.toggle('hidden', !lockedMessage);

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
                                            <button aria-label="Quitar ${escapeHtml(product.name)}" data-delta="-1" data-product="${product.id}" class="relative z-10 flex h-11 w-11 items-center justify-center rounded-md border border-zinc-200 bg-white text-lg font-semibold text-zinc-800 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-35" ${!canEditCurrentSelection() || selected === 0 ? 'disabled' : ''}>-</button>
                                            <span class="pointer-events-none min-w-8 text-center text-sm font-semibold tabular-nums text-zinc-950">${selected}</span>
                                            <button aria-label="Agregar ${escapeHtml(product.name)}" data-delta="1" data-product="${product.id}" class="relative z-10 flex h-11 w-11 items-center justify-center rounded-md bg-zinc-950 text-lg font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-35" ${!canEditCurrentSelection() ? 'disabled' : ''}>+</button>
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
                                <h3 class="min-w-0 truncate font-semibold text-zinc-950">${escapeHtml(guest.display_alias || guest.alias)}</h3>
                                <p class="font-semibold tabular-nums">${guest.subtotal_formatted}</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Carrito</p>
                            ${guest.id === currentGuestId && guest.items.length && !guest.is_ready ? '<button type="button" data-clear-cart class="rounded-md border border-zinc-200 px-2 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Vaciar</button>' : ''}
                            </div>
                            <div class="mt-2 divide-y divide-zinc-100">
                                ${guest.items.length ? guest.items.map((item) => `
                                    <div class="py-2 text-sm">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="min-w-0">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                            <p class="font-medium tabular-nums">${item.subtotal_formatted}</p>
                                        </div>
                                        ${guest.id === currentGuestId && !guest.is_ready ? `<input data-cart-note="${item.product_id}" maxlength="160" value="${escapeHtml(item.notes || '')}" placeholder="Nota para este plato" class="mt-2 min-h-9 w-full rounded-md border border-zinc-200 bg-zinc-50 px-2 py-1 text-xs outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100">` : (item.notes ? `<p class="mt-1 text-xs text-zinc-500">Nota: ${escapeHtml(item.notes)}</p>` : '')}
                                    </div>
                                `).join('') : '<p class="py-2 text-sm text-zinc-500">Sin platos seleccionados.</p>'}
                            </div>
                            <div class="mt-4 border-t border-zinc-100 pt-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Pedidos enviados</p>
                                <div class="mt-2 space-y-3">
                                    ${guest.orders.length ? guest.orders.map((order) => `
                                        <div class="rounded-md bg-zinc-50 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-xs font-semibold text-zinc-600">Pedido #${order.id} · ${escapeHtml(order.status_label)}</p>
                                                <p class="text-sm font-semibold tabular-nums">${order.subtotal_formatted}</p>
                                            </div>
                                            <div class="mt-2 space-y-1">
                                                ${order.items.map((item) => `
                                                    <div class="text-xs text-zinc-600">
                                                        ${escapeHtml(item.name)} x${item.quantity}${item.notes ? ` · ${escapeHtml(item.notes)}` : ''}
                                                    </div>
                                                `).join('')}
                                            </div>
                                        </div>
                                    `).join('') : '<p class="text-sm text-zinc-500">Aun no ha enviado pedidos.</p>'}
                                </div>
                            </div>
                            <p class="mt-3 text-xs font-semibold ${guest.is_ready ? 'text-emerald-700' : 'text-amber-700'}">${guest.is_ready ? 'Seleccion lista' : 'Esperando confirmacion de seleccion'}</p>
                        </section>
                    `).join('')
                    : '<p class="text-sm text-zinc-500">Cuando alguien agregue su alias, aparecera aqui.</p>';
            };

            const renderConfirmation = () => {
                const readyCount = state.guests.filter((guest) => guest.is_ready).length;
                const totalGuests = state.guests.length;
                const current = currentGuest();
                const button = root.querySelector('[data-confirm-order]');
                const status = root.querySelector('[data-confirm-status]');

                root.querySelector('[data-ready-summary]').textContent = `${readyCount}/${totalGuests}`;
                button.hidden = Boolean(state.order_confirmed);
                button.disabled = !state.can_confirm_order;

                if (state.order_confirmed) {
                    status.textContent = `Pedido confirmado por ${state.confirmed_by_alias || 'la mesa'}.`;
                } else if (!totalGuests) {
                    status.textContent = 'Agrega tu alias para empezar.';
                } else if (!state.device_can_confirm_order && !current) {
                    status.textContent = `${state.coordinator_alias || 'La primera persona'} confirmara el pedido cuando todos esten listos.`;
                } else if (!state.device_can_confirm_order) {
                    status.textContent = `${state.coordinator_alias || 'La primera persona'} es quien puede confirmar el pedido final.`;
                } else if (!state.has_items) {
                    status.textContent = 'Agrega al menos un plato antes de confirmar.';
                } else if (!state.all_guests_ready) {
                    status.textContent = 'Espera a que todas las personas marquen su seleccion como lista.';
                } else {
                    status.textContent = 'Todo esta listo. Puedes enviar el pedido final al restaurante.';
                }
            };

            const currentGuest = () => state.guests.find((guest) => guest.id === currentGuestId);

            const canEditCurrentSelection = () => {
                const guest = currentGuest();

                return Boolean(guest && !guest.is_ready);
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
                const accountModeButton = event.target.closest('[data-account-mode]');
                if (accountModeButton) {
                    try {
                        accountModeButton.disabled = true;
                        state = await request(accountModeUrl, {
                            method: 'POST',
                            body: JSON.stringify({ account_mode: accountModeButton.dataset.accountMode }),
                        });
                        currentGuestId = state.current_guest_id;
                        render();
                        setError('');
                    } catch (error) {
                        setError('No se pudo guardar la forma de pago de esta mesa.');
                    } finally {
                        accountModeButton.disabled = false;
                    }

                    return;
                }

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

                const readyButton = event.target.closest('[data-ready-toggle]');
                if (readyButton) {
                    const guest = currentGuest();
                    if (!guest) return;

                    try {
                        readyButton.disabled = true;
                        state = await request(readyUrl, {
                            method: 'POST',
                            body: JSON.stringify({ is_ready: !guest.is_ready }),
                        });
                        currentGuestId = state.current_guest_id;
                        render();
                        setError('');
                    } catch (error) {
                        setError('No se pudo actualizar el estado de tu seleccion.');
                    } finally {
                        readyButton.disabled = false;
                    }

                    return;
                }

                const confirmButton = event.target.closest('[data-confirm-order]');
                if (confirmButton && !confirmButton.disabled) {
                    try {
                        confirmButton.disabled = true;
                        state = await request(confirmUrl, { method: 'POST' });
                        currentGuestId = state.current_guest_id;
                        render();
                        setError('');
                    } catch (error) {
                        setError('Todavia no se puede confirmar el pedido final.');
                    } finally {
                        confirmButton.disabled = false;
                    }

                    return;
                }

                const selectGuestButton = event.target.closest('[data-select-guest]');
                if (selectGuestButton) {
                    try {
                        selectGuestButton.disabled = true;
                        const guestToken = selectGuestButton.dataset.selectGuest;
                        state = await request(selectGuestUrl.replace('__guest__', guestToken), { method: 'POST' });
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

                const clearCartButton = event.target.closest('[data-clear-cart]');
                if (clearCartButton) {
                    try {
                        clearCartButton.disabled = true;
                        state = await request(clearCartUrl, { method: 'POST' });
                        currentGuestId = state.current_guest_id;
                        render();
                        setError('');
                    } catch (error) {
                        setError('No se pudo vaciar el carrito.');
                    } finally {
                        clearCartButton.disabled = false;
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

            root.addEventListener('change', async (event) => {
                const noteInput = event.target.closest('[data-cart-note]');
                if (!noteInput) return;

                try {
                    noteInput.disabled = true;
                    state = await request(itemsUrl, {
                        method: 'POST',
                        body: JSON.stringify({
                            product_id: Number(noteInput.dataset.cartNote),
                            delta: 0,
                            notes: noteInput.value,
                        }),
                    });
                    currentGuestId = state.current_guest_id;
                    render();
                    setError('');
                } catch (error) {
                    setError('No se pudo guardar la nota del producto.');
                } finally {
                    noteInput.disabled = false;
                }
            });

            loadState();
            setInterval(loadState, 3000);
        })();
    </script>
@endsection
