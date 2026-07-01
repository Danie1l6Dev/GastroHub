@extends('layouts.app', ['title' => 'Mesa '.$table->name, 'qrLayout' => true])

@section('content')
    <section
        class="mx-auto max-w-6xl px-4 pb-8 pt-4 sm:py-8"
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
        <div class="mb-4 overflow-hidden rounded-3xl bg-zinc-950 p-4 text-white shadow-xl shadow-zinc-950/10 sm:mb-6 sm:p-7">
            <h1 class="text-3xl font-semibold tracking-tight sm:text-5xl">{{ $table->name }}</h1>
        </div>

        <div data-error class="mb-4 hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"></div>

        <div data-mobile-progress class="-mx-4 mb-4 hidden overflow-x-auto px-4 lg:hidden" aria-label="Progreso del pedido">
            <div class="flex w-max min-w-full gap-2">
                <button type="button" data-module-link="alias" class="gh-step-chip gh-step-chip-active">1. Alias</button>
                <button type="button" data-module-link="menu" class="gh-step-chip">2. Platos</button>
                <button type="button" data-module-link="cart" class="gh-step-chip">3. Carrito</button>
                <button type="button" data-module-link="orders" class="gh-step-chip">4. Estado</button>
                <button type="button" data-module-link="account" class="gh-step-chip">5. Cuenta</button>
            </div>
        </div>

        <section data-joint-locked-panel class="mb-5 hidden rounded-md border border-amber-200 bg-amber-50 p-4 shadow-sm sm:p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Pago en conjunto activo</p>
            <h2 class="mt-2 text-2xl font-semibold text-amber-950">Puedes ver el menu de esta mesa</h2>
            <p class="mt-2 text-sm leading-6 text-amber-900">
                Dile a
                <span data-joint-owner class="font-semibold">la persona encargada</span>
                que pida lo que quieres.
            </p>
        </section>

        <section data-account-mode-panel class="mb-5 hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Antes de pedir</p>
            <h2 class="mt-2 text-2xl font-semibold text-zinc-950">Como van a pagar?</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-600">Elige una opcion para esta cuenta. Si regeneras el QR, la mesa vuelve a empezar.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <button type="button" data-account-mode="joint" class="min-h-28 rounded-2xl border border-zinc-200 bg-zinc-950 p-4 text-left text-white transition hover:bg-zinc-800 active:scale-[0.99]">
                    <span class="block text-base font-semibold">Pago en conjunto</span>
                    <span class="mt-1 block text-sm leading-5 text-zinc-300">Una sola persona toma el pedido de toda la mesa.</span>
                </button>
                <button type="button" data-account-mode="separate" class="min-h-28 rounded-2xl border border-zinc-200 bg-white p-4 text-left transition hover:bg-zinc-50 active:scale-[0.99]">
                    <span class="block text-base font-semibold text-zinc-950">Cuentas separadas</span>
                    <span class="mt-1 block text-sm leading-5 text-zinc-600">Cada persona tiene alias, pedido y subtotal propio.</span>
                </button>
            </div>
        </section>

        <div data-workspace class="grid min-w-0 gap-4 lg:grid-cols-[20rem_1fr] lg:gap-5">
            <aside class="min-w-0 space-y-4 lg:sticky lg:top-5 lg:self-start">
                <section id="alias" data-table-panel data-module="alias" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Paso 1</p>
                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">Tu alias</h2>
                        </div>
                        <span data-account-mode-badge class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600"></span>
                    </div>
                    <form data-alias-form class="mt-4 space-y-3">
                        <div>
                            <label class="text-sm font-medium text-zinc-800" for="guest_alias">Nombre o alias</label>
                            <input id="guest_alias" name="alias" type="text" maxlength="80" value="{{ $alias }}" placeholder="Ej. Laura" autocomplete="nickname" enterkeyhint="done" class="mt-1 min-h-12 w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3 py-2 text-base outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                            <p class="mt-2 text-xs leading-5 text-zinc-500">Solo necesitamos un nombre para asociar tu seleccion dentro de esta mesa.</p>
                        </div>
                        <button class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98]">Confirmar alias</button>
                    </form>
                    <p data-join-locked class="mt-4 hidden rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-950">
                        El pedido ya fue confirmado. No se pueden agregar mas personas a esta mesa.
                    </p>
                    <div data-current-guest class="mt-4 hidden rounded-md border border-emerald-200 bg-emerald-50 p-3">
                        <p class="text-sm text-emerald-900">Ingresaste como</p>
                        <p data-current-alias class="mt-1 text-lg font-semibold text-emerald-950"></p>
                    </div>
                </section>

                <section data-table-panel data-module="people" class="rounded-md border border-zinc-200 bg-zinc-950 p-4 text-white shadow-sm sm:p-5 lg:hidden">
                    <p class="text-sm text-zinc-300">Total de la mesa</p>
                    <p data-table-total-mobile class="mt-1 text-3xl font-semibold tabular-nums">$0</p>
                </section>

                <section id="personas" data-people-panel data-table-panel data-module="people" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
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

                <section id="cuenta" data-bill-panel data-table-panel data-module="account" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">Cuenta</p>
                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">Cuenta de la mesa</h2>
                        </div>
                        <span data-bill-status class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600">Pendiente</span>
                    </div>
                    <div data-bill-summary class="mt-4 space-y-3"></div>
                </section>

                <section data-confirm-panel data-table-panel data-module="cart" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">Pedido final</p>
                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">Confirmacion</h2>
                        </div>
                        <span data-ready-summary class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600">0/0</span>
                    </div>
                    <p data-confirm-status class="mt-3 text-sm leading-6 text-zinc-600">Agrega tu alias para empezar.</p>
                    <button type="button" data-confirm-order class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-zinc-200 disabled:text-zinc-500">
                        Confirmar pedido
                    </button>
                </section>
            </aside>

            <div class="min-w-0 space-y-4">
                <section id="menu-mesa" data-table-panel data-module="menu" class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-100 p-4 sm:p-5">
                        <div class="grid gap-4 lg:grid-cols-[1fr_16rem] lg:items-end">
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-950">Seleccionar platos</h2>
                                <p class="mt-1 text-sm leading-6 text-zinc-600">Elige una seccion y agrega tus platos. Los agotados se ven, pero no se pueden seleccionar.</p>
                            </div>
                            <label class="block">
                                <span class="sr-only">Buscar platos</span>
                                <input data-table-menu-search type="search" placeholder="Buscar plato" enterkeyhint="search" class="gh-field">
                            </label>
                        </div>
                        <p data-selection-lock class="mt-3 hidden rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-950"></p>
                    </div>
                    <div data-products class="p-4 sm:p-5"></div>
                    <div data-menu-cart-cta class="gh-module-sticky mx-4 mb-4 hidden lg:hidden">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p data-menu-cart-count class="text-xs font-semibold text-zinc-500">0 productos</p>
                                <p data-menu-cart-total class="truncate text-base font-semibold tabular-nums text-zinc-950">$0</p>
                            </div>
                            <button type="button" data-module-link="cart" class="gh-btn gh-btn-primary shrink-0 rounded-xl px-4">Revisar pedido</button>
                        </div>
                    </div>
                </section>

                <section id="detalle" data-table-panel data-module="cart" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Carrito</p>
                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">Revisar pedido</h2>
                        </div>
                        <span data-cart-current-total class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-semibold tabular-nums text-zinc-700">$0</span>
                    </div>
                    <div data-cart-breakdown class="mt-4 space-y-4"></div>
                    <div data-cart-action-bar class="gh-module-sticky mt-4 hidden lg:hidden">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-zinc-500">Subtotal actual</p>
                                <p data-cart-sticky-total class="text-lg font-semibold tabular-nums text-zinc-950">$0</p>
                            </div>
                            <button type="button" data-cart-ready-action class="gh-btn gh-btn-primary shrink-0 rounded-xl px-4">Confirmar</button>
                        </div>
                    </div>
                </section>

                <section id="mis-pedidos" data-table-panel data-module="orders" class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Estado</p>
                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">Mi pedido</h2>
                        </div>
                        <button type="button" data-module-link="menu" class="gh-btn gh-btn-secondary min-h-10 rounded-xl px-3">Volver al menu</button>
                    </div>
                    <div data-orders-breakdown class="mt-4 space-y-4"></div>
                </section>

                <section class="hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5 lg:block">
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
            const menuSearch = root.querySelector('[data-table-menu-search]');
            let currentGuestId = root.dataset.initialGuestId ? Number(root.dataset.initialGuestId) : null;
            let selectedCategoryId = null;
            let productPageByCategory = {};
            let activeModule = sessionStorage.getItem(`table:${root.dataset.stateUrl}:module`) || (currentGuestId ? 'menu' : 'alias');
            let state = null;
            let lastProductRenderKey = null;
            let openGuestOrderToken = null;
            let openBillParticipantId = null;
            let extraGuestDropdownState = {
                isOpen: false,
                selectedToken: null,
            };
            const productsPerPage = 3;

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

            const showToast = (message) => {
                const toast = document.createElement('div');
                toast.className = 'gh-toast gh-reveal';
                toast.textContent = message;
                document.body.appendChild(toast);
                window.setTimeout(() => toast.remove(), 2200);
            };

            const setActiveModule = (module, { scroll = true } = {}) => {
                const previous = activeModule;
                const previousPanel = root.querySelector(`[data-table-panel][data-module="${previous}"]:not(.hidden)`);
                if (previousPanel) {
                    sessionStorage.setItem(`table:${stateUrl}:scroll:${previous}`, String(window.scrollY));
                }

                activeModule = module;
                sessionStorage.setItem(`table:${stateUrl}:module`, module);
                updateModuleVisibility();
                if (state) updateStickyActions();

                if (scroll) {
                    const saved = Number(sessionStorage.getItem(`table:${stateUrl}:scroll:${module}`) || 0);
                    window.requestAnimationFrame(() => {
                        if (saved > 0) {
                            window.scrollTo({ top: saved, behavior: 'auto' });
                        } else {
                            root.querySelector(`[data-table-panel][data-module="${module}"]`)?.scrollIntoView({ block: 'start', behavior: 'smooth' });
                        }
                    });
                }
            };

            const updateModuleVisibility = () => {
                root.querySelectorAll('[data-table-panel]').forEach((panel) => {
                    panel.dataset.mobileHidden = panel.dataset.module === activeModule ? 'false' : 'true';
                });

                root.querySelectorAll('[data-module-link], [data-mobile-progress] .gh-step-chip').forEach((item) => {
                    const isActive = item.dataset.moduleLink === activeModule;
                    item.classList.toggle('gh-mobile-action-active', item.classList.contains('gh-mobile-action') && isActive);
                    item.classList.toggle('gh-step-chip-active', item.classList.contains('gh-step-chip') && isActive);
                    if (item.classList.contains('gh-mobile-action')) {
                        item.setAttribute('aria-current', isActive ? 'page' : 'false');
                    }
                });
            };

            const orderStatusPosition = (status) => ({
                new: 1,
                preparing: 2,
                delivered: 3,
                cancelled: 0,
            }[status] || 0);

            function selectionItemsForGuest(guest) {
                if (!guest) return [];

                if (guest.items.length || !guest.is_ready || state.order_confirmed) {
                    return guest.items;
                }

                return guest.orders.at(-1)?.items || [];
            }

            const updateStickyActions = () => {
                const current = currentGuest();
                const selectionItems = selectionItemsForGuest(current);
                const quantity = selectionItems.reduce((total, item) => total + Number(item.quantity || 0), 0);
                const hasCart = quantity > 0;
                const total = current?.subtotal_formatted || money(0);
                const menuCta = root.querySelector('[data-menu-cart-cta]');
                const cartBar = root.querySelector('[data-cart-action-bar]');
                const cartButtons = root.querySelectorAll('[data-cart-ready-action]');

                root.querySelector('[data-menu-cart-count]').textContent = `${quantity} ${quantity === 1 ? 'producto' : 'productos'}`;
                root.querySelector('[data-menu-cart-total]').textContent = total;
                root.querySelector('[data-cart-current-total]').textContent = total;
                root.querySelector('[data-cart-sticky-total]').textContent = total;
                menuCta.classList.toggle('hidden', activeModule !== 'menu' || !hasCart || !canEditCurrentSelection());
                cartBar.classList.toggle('hidden', activeModule !== 'cart' || !current || current.is_ready);

                if (cartButtons.length) {
                    const confirmedReadySelection = Boolean(state.order_confirmed && current?.is_ready);
                    const cartButtonText = confirmedReadySelection
                        ? 'Pedido confirmado'
                        : current?.is_ready
                            ? 'Editar preseleccion'
                            : state.order_confirmed
                                ? 'Enviar adicional'
                                : 'Confirmar preseleccion';
                    const cartButtonDisabled = confirmedReadySelection || !current || (!selectionItems.length && !current?.is_ready);

                    cartButtons.forEach((cartButton) => {
                        cartButton.textContent = cartButtonText;
                        cartButton.disabled = cartButtonDisabled;
                    });
                }

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

            const loadState = async ({ refreshProducts = true } = {}) => {
                try {
                    state = await request(stateUrl, { method: 'GET', headers: { 'Content-Type': 'application/json' } });
                    currentGuestId = state.current_guest_id;
                    if (isEditingCartNote()) {
                        setError('');

                        return;
                    }

                    render({ refreshProducts });
                    setError('');
                } catch (error) {
                    setError('No se pudo actualizar la mesa.');
                }
            };

            const render = ({ refreshProducts = true } = {}) => {
                if (!state) return;

                const needsAccountMode = state.requires_account_mode;
                const isJointMode = state.account_mode === 'joint';
                const isJointOrderLocked = Boolean(state.joint_order_locked);
                root.querySelector('[data-joint-locked-panel]').classList.toggle('hidden', !isJointOrderLocked);
                root.querySelector('[data-joint-owner]').textContent = state.joint_order_owner_alias || 'la persona encargada';
                root.querySelector('[data-account-mode-panel]').classList.toggle('hidden', !needsAccountMode || isJointOrderLocked);
                root.querySelector('[data-workspace]').classList.toggle('hidden', needsAccountMode);
                root.querySelector('[data-mobile-progress]').classList.toggle('hidden', needsAccountMode);

                if (needsAccountMode) return;

                const currentGuest = state.guests.find((guest) => guest.id === currentGuestId);
                if (!currentGuest && !state.order_confirmed && !isJointOrderLocked && activeModule !== 'alias') {
                    setActiveModule('alias', { scroll: false });
                }
                root.querySelector('[data-alias-form]').classList.toggle('hidden', state.order_confirmed || isJointOrderLocked);
                root.querySelector('[data-join-locked]').classList.toggle('hidden', !state.order_confirmed || isJointOrderLocked);
                root.querySelector('[data-current-guest]').classList.toggle('hidden', !currentGuest || isJointOrderLocked);
                root.querySelector('[data-current-alias]').textContent = currentGuest?.alias || '';
                root.querySelector('[data-people-panel]').classList.toggle('hidden', isJointMode);
                root.querySelector('[data-account-mode-badge]').textContent = state.account_mode_label || '';
                root.querySelector('[data-guest-count]').textContent = state.guests.length;
                root.querySelector('[data-confirm-panel]').classList.toggle('hidden', isJointOrderLocked);
                root.querySelectorAll('[data-table-total], [data-table-total-mobile]').forEach((node) => {
                    node.textContent = state.total_formatted || money(state.total);
                });

                const shouldRenderProducts = refreshProducts || activeModule !== 'menu' || productRenderKey() !== lastProductRenderKey;

                renderGuests();
                if (shouldRenderProducts) {
                    renderProducts();
                }
                renderBreakdown();
                renderConfirmation();
                renderBill();
                updateStickyActions();
                updateModuleVisibility();
            };

            const renderGuests = () => {
                const target = root.querySelector('[data-guests]');
                target.innerHTML = state.guests.length
                    ? state.guests.map((guest) => `
                        <div class="rounded-2xl border p-3 ${guest.id === currentGuestId ? 'border-emerald-200 bg-emerald-50' : 'border-zinc-200 bg-white'}">
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
                                <p class="text-xs text-zinc-500">${guest.items.length} en carrito - ${guest.orders.length} pedidos${guest.id === state.coordinator_guest_id ? ' - Encargado' : ''}</p>
                                <button
                                    type="button"
                                    data-select-guest="${guest.guest_token}"
                                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border px-3 py-2 text-xs font-semibold transition active:scale-[0.98] sm:w-auto ${guest.id === currentGuestId ? 'border-emerald-200 bg-white text-emerald-950' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:bg-zinc-100'}"
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
                lastProductRenderKey = productRenderKey();
                const lockedMessage = state.joint_order_locked
                    ? `Dile a ${escapeHtml(state.joint_order_owner_alias || 'la persona encargada')} que pida lo que quieres. Puedes revisar el menu mientras tanto.`
                    : state.order_confirmed
                    ? activeGuest?.is_ready
                        ? `Pedido confirmado por ${escapeHtml(state.confirmed_by_alias || 'la mesa')}. Toca "Pedir algo extra" para abrir otro carrito.`
                        : ''
                    : activeGuest?.is_ready
                        ? 'Tu preseleccion esta confirmada. Puedes editarla antes de que el encargado confirme el pedido final.'
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
                const query = menuSearch?.value.trim().toLowerCase() || '';
                const products = (selectedCategory?.products || []).filter((product) => {
                    if (!query) return true;

                    return `${product.name} ${product.description || ''}`.toLowerCase().includes(query);
                });
                const totalPages = Math.max(1, Math.ceil(products.length / productsPerPage));
                const currentPage = Math.min(Math.max(Number(productPageByCategory[selectedCategoryId] || 1), 1), totalPages);
                productPageByCategory[selectedCategoryId] = currentPage;
                const pageStart = (currentPage - 1) * productsPerPage;
                const visibleProducts = products.slice(pageStart, pageStart + productsPerPage);
                const pageSummary = products.length > productsPerPage
                    ? `${pageStart + 1}-${Math.min(pageStart + productsPerPage, products.length)} de ${products.length} platos`
                    : `${products.length} platos en esta seccion`;

                target.innerHTML = `
                    <div class="sticky top-0 z-20 -mx-4 border-b border-zinc-100 bg-white/95 px-4 pb-3 pt-1 backdrop-blur-xl sm:mx-0 sm:px-0 lg:static lg:bg-transparent lg:backdrop-blur-0">
                        <div class="relative" data-category-select data-open="false">
                            <button
                                type="button"
                                data-category-select-toggle
                                class="flex min-h-12 w-full items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-left text-sm font-semibold text-zinc-950 shadow-sm shadow-zinc-950/[0.03] transition hover:bg-zinc-50 active:scale-[0.99]"
                                aria-expanded="false"
                            >
                                <span class="min-w-0">
                                    <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Seccion</span>
                                    <span class="block truncate">${escapeHtml(selectedCategory.name)}</span>
                                </span>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-lg leading-none text-zinc-700" aria-hidden="true">v</span>
                            </button>
                            <div class="gh-category-select-panel absolute left-0 right-0 top-full z-40 mt-2 rounded-2xl border border-zinc-200 bg-white p-2 shadow-xl shadow-zinc-950/10" role="listbox" aria-label="Secciones del menu">
                            ${state.categories.map((category) => `
                                <button
                                    type="button"
                                    data-category="${category.id}"
                                    class="flex min-h-11 w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-left text-sm font-semibold transition active:scale-[0.98] ${category.id === selectedCategoryId ? 'bg-zinc-950 text-white' : 'text-zinc-700 hover:bg-zinc-50'}"
                                    role="option"
                                    aria-selected="${category.id === selectedCategoryId ? 'true' : 'false'}"
                                >
                                    <span class="min-w-0 truncate">${escapeHtml(category.name)}</span>
                                    <span class="shrink-0 rounded-full bg-white/15 px-2 py-0.5 text-xs opacity-80">${category.products.length}</span>
                                </button>
                            `).join('')}
                            </div>
                        </div>
                    </div>

                    <section class="pt-5">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-950">${escapeHtml(selectedCategory.name)}</h3>
                                <p class="mt-1 text-sm text-zinc-500">${pageSummary}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            ${visibleProducts.length ? visibleProducts.map((product) => productCard(product)).join('') : '<p class="rounded-2xl bg-zinc-50 p-4 text-sm text-zinc-500 md:col-span-2">No hay platos que coincidan en esta seccion.</p>'}
                        </div>
                        ${products.length > productsPerPage ? `
                            <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-2">
                                <button type="button" data-products-page="${currentPage - 1}" class="gh-btn gh-btn-secondary min-h-10 rounded-xl px-3" ${currentPage <= 1 ? 'disabled' : ''}>
                                    Anterior
                                </button>
                                <p class="text-sm font-semibold tabular-nums text-zinc-600">Pagina ${currentPage} de ${totalPages}</p>
                                <button type="button" data-products-page="${currentPage + 1}" class="gh-btn gh-btn-primary min-h-10 rounded-xl px-3" ${currentPage >= totalPages ? 'disabled' : ''}>
                                    Siguiente
                                </button>
                            </div>
                        ` : ''}
                    </section>
                `;
            };

            const productCard = (product) => {
                const selected = currentGuestProductQuantity(product.id);
                const currentItem = currentGuestProductItem(product.id);
                const description = product.description ? `<p class="mt-1 line-clamp-2 text-sm leading-5 text-zinc-600">${escapeHtml(product.description)}</p>` : '';
                const imageUrl = escapeHtml(product.image_url || '');
                const noteControl = selected > 0 && canEditCurrentSelection()
                    ? `<label class="mt-3 block">
                            <span class="sr-only">Nota para ${escapeHtml(product.name)}</span>
                            <input data-product-note="${product.id}" maxlength="160" value="${escapeHtml(currentItem?.notes || '')}" placeholder="Nota opcional para cocina" enterkeyhint="done" class="min-h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-base outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100 sm:text-sm">
                        </label>`
                    : '';

                return `
                    <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-zinc-950/[0.08] ${product.is_available ? '' : 'opacity-75'}">
                        <div class="flex gap-3 p-3">
                            <img src="${imageUrl}" alt="${escapeHtml(product.name)}" loading="lazy" class="pointer-events-none h-24 w-24 shrink-0 rounded-xl object-cover outline outline-1 -outline-offset-1 outline-black/10 sm:h-28 sm:w-28">
                            <div class="flex min-w-0 flex-1 flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-semibold leading-5 text-zinc-950">${escapeHtml(product.name)}</h4>
                                        ${description}
                                    </div>
                                    ${product.is_available ? '' : '<span class="shrink-0 rounded-full bg-zinc-200 px-2 py-1 text-[11px] font-semibold text-zinc-700">Agotado</span>'}
                                </div>
                                <div class="mt-auto flex flex-col gap-3 pt-3 sm:flex-row sm:items-end sm:justify-between">
                                    <p class="text-sm font-semibold tabular-nums text-zinc-950">${product.price_formatted}</p>
                                    ${product.is_available
                                        ? `<div class="relative z-10 flex w-full items-center justify-between gap-1 rounded-xl border border-zinc-200 bg-zinc-50 p-1 sm:w-auto">
                                            <button aria-label="Quitar ${escapeHtml(product.name)}" data-delta="-1" data-product="${product.id}" class="relative z-10 flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-lg font-semibold text-zinc-800 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-35" ${!canEditCurrentSelection() || selected === 0 ? 'disabled' : ''}>-</button>
                                            <span class="pointer-events-none min-w-8 text-center text-sm font-semibold tabular-nums text-zinc-950">${selected}</span>
                                            <button aria-label="Agregar ${escapeHtml(product.name)}" data-delta="1" data-product="${product.id}" class="relative z-10 flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-950 text-lg font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-35" ${!canEditCurrentSelection() ? 'disabled' : ''}>+</button>
                                        </div>`
                                        : '<span class="rounded-md bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">No disponible</span>'
                                    }
                                </div>
                                ${noteControl}
                            </div>
                        </div>
                    </article>
                `;
            };

            const renderBreakdown = () => {
                const target = root.querySelector('[data-breakdown]');
                const cartTarget = root.querySelector('[data-cart-breakdown]');
                const ordersTarget = root.querySelector('[data-orders-breakdown]');
                const guestCard = (guest) => `
                        <section class="rounded-2xl border border-zinc-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="min-w-0 truncate font-semibold text-zinc-950">${escapeHtml(guest.display_alias || guest.alias)}</h3>
                                <p class="font-semibold tabular-nums">${guest.subtotal_formatted}</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Carrito</p>
                            ${guest.id === currentGuestId && guest.items.length && !guest.is_ready ? '<button type="button" data-clear-cart class="min-h-10 rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Vaciar</button>' : ''}
                            </div>
                            <div class="mt-2 divide-y divide-zinc-100">
                                ${guest.items.length ? guest.items.map((item) => `
                                    <div class="py-2 text-sm">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="min-w-0">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                            <p class="font-medium tabular-nums">${item.subtotal_formatted}</p>
                                        </div>
                                        ${guest.id === currentGuestId && !guest.is_ready ? `<input data-cart-note="${item.product_id}" maxlength="160" value="${escapeHtml(item.notes || '')}" placeholder="Nota para este plato" enterkeyhint="done" class="mt-2 min-h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-base outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100 sm:text-sm">` : (item.notes ? `<p class="mt-1 text-xs text-zinc-500">Nota: ${escapeHtml(item.notes)}</p>` : '')}
                                    </div>
                                `).join('') : '<p class="py-2 text-sm text-zinc-500">Sin platos seleccionados.</p>'}
                            </div>
                            <div class="mt-4 border-t border-zinc-100 pt-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Pedidos enviados</p>
                                <div class="mt-2 space-y-3">
                                    ${guest.orders.length ? guest.orders.map((order) => {
                                        const status = orderStatusMeta(order.status, order.status_label);

                                        return `
                                            <div class="rounded-2xl border border-zinc-200 bg-white p-3">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Pedido #${order.id}</p>
                                                            <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold ${status.classes}">${escapeHtml(status.label)}</span>
                                                        </div>
                                                        <p class="mt-2 text-xs leading-5 text-zinc-500">${escapeHtml(status.hint)}</p>
                                                    </div>
                                                    <p class="text-base font-semibold tabular-nums text-zinc-950">${order.subtotal_formatted}</p>
                                                </div>
                                                <div class="mt-3 divide-y divide-zinc-100 rounded-xl bg-zinc-50 px-3">
                                                    ${order.items.map((item) => `
                                                        <div class="py-2 text-sm">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <p class="min-w-0 font-medium text-zinc-800">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                                                <p class="shrink-0 text-xs font-semibold tabular-nums text-zinc-500">${item.subtotal_formatted}</p>
                                                            </div>
                                                            ${item.notes ? `<p class="mt-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs text-amber-900">Nota: ${escapeHtml(item.notes)}</p>` : ''}
                                                        </div>
                                                    `).join('')}
                                                </div>
                                            </div>
                                        `;
                                    }).join('') : '<p class="text-sm text-zinc-500">Aun no ha enviado pedidos.</p>'}
                                </div>
                            </div>
                            <p class="mt-3 text-xs font-semibold ${guest.is_ready ? 'text-emerald-700' : 'text-amber-700'}">${guest.is_ready ? 'Preseleccion confirmada' : 'Preseleccion pendiente'}</p>
                        </section>
                    `;

                const cartCard = (guest) => {
                    const selectionItems = selectionItemsForGuest(guest);
                    const isCompact = guest.is_ready && !state.order_confirmed;

                    return `
                        <section class="rounded-2xl border border-zinc-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-zinc-950">${escapeHtml(guest.display_alias || guest.alias)}</h3>
                                    <p class="mt-1 text-xs text-zinc-500">${guest.is_ready ? (state.order_confirmed ? 'Pedido final confirmado' : 'Preseleccion confirmada. Puedes editarla antes del pedido final.') : 'Puedes editar cantidades y notas.'}</p>
                                </div>
                                <p class="shrink-0 font-semibold tabular-nums">${guest.subtotal_formatted}</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">${isCompact ? 'Resumen compacto' : 'Productos'}</p>
                                ${guest.items.length && !guest.is_ready ? '<button type="button" data-clear-cart class="min-h-10 rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Vaciar</button>' : ''}
                            </div>
                            <div class="mt-2 divide-y divide-zinc-100">
                                ${selectionItems.length ? selectionItems.map((item) => `
                                    <div class="py-3 text-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="font-medium text-zinc-900">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                                ${!guest.is_ready ? `
                                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                                        <div class="inline-flex min-h-11 items-center rounded-xl border border-zinc-200 bg-zinc-50 p-1">
                                                            <button type="button" aria-label="Quitar una unidad de ${escapeHtml(item.name)}" data-product="${item.product_id}" data-delta="-1" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-lg font-semibold text-zinc-800 shadow-sm transition hover:bg-zinc-100 active:scale-[0.97]">-</button>
                                                            <span class="min-w-10 px-2 text-center text-sm font-semibold tabular-nums text-zinc-950">${item.quantity}</span>
                                                            <button type="button" aria-label="Agregar una unidad de ${escapeHtml(item.name)}" data-product="${item.product_id}" data-delta="1" class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-950 text-lg font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.97]">+</button>
                                                        </div>
                                                        <button type="button" data-product="${item.product_id}" data-delta="-${item.quantity}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100 active:scale-[0.98]">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                ` : ''}
                                            </div>
                                            <p class="shrink-0 font-semibold tabular-nums text-zinc-950">${item.subtotal_formatted}</p>
                                        </div>
                                        ${!guest.is_ready ? `<input data-cart-note="${item.product_id}" maxlength="160" value="${escapeHtml(item.notes || '')}" placeholder="Nota para este plato" enterkeyhint="done" class="mt-2 min-h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-base outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100 sm:text-sm">` : ''}
                                    </div>
                                `).join('') : '<p class="py-3 text-sm text-zinc-500">Tu carrito esta vacio. Vuelve al menu para agregar platos.</p>'}
                            </div>
                            ${selectionItems.length && !guest.is_ready ? `
                                <button type="button" data-cart-ready-action class="mt-4 hidden min-h-12 w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-zinc-200 disabled:text-zinc-500 lg:inline-flex">
                                    Confirmar preseleccion
                                </button>
                            ` : ''}
                        </section>
                    `;
                };

                const orderCard = (guest, order) => {
                    const status = orderStatusMeta(order.status, order.status_label);

                    return `
                        <details class="rounded-2xl border border-zinc-200 bg-white p-3" ${order.status !== 'delivered' ? 'open' : ''}>
                            <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Pedido #${order.id}</p>
                                            <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold ${status.classes}">${escapeHtml(status.label)}</span>
                                        </div>
                                        <p class="mt-2 text-xs leading-5 text-zinc-500">${escapeHtml(guest.display_alias || guest.alias)} - ${escapeHtml(status.hint)}</p>
                                    </div>
                                    <p class="shrink-0 text-base font-semibold tabular-nums text-zinc-950">${order.subtotal_formatted}</p>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-1 rounded-xl bg-zinc-50 p-1 text-[11px] font-semibold text-zinc-500">
                                    ${['new', 'preparing', 'delivered'].map((step) => `
                                        <span class="rounded-lg px-2 py-1 text-center ${orderStatusPosition(order.status) >= orderStatusPosition(step) ? 'bg-white text-emerald-700 shadow-sm' : ''}">
                                            ${step === 'new' ? 'Recibido' : step === 'preparing' ? 'Cocina' : 'Entregado'}
                                        </span>
                                    `).join('')}
                                </div>
                            </summary>
                            <div class="mt-3 divide-y divide-zinc-100 rounded-xl bg-zinc-50 px-3">
                                ${order.items.map((item) => `
                                    <div class="py-2 text-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="min-w-0 font-medium text-zinc-800">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                            <p class="shrink-0 text-xs font-semibold tabular-nums text-zinc-500">${item.subtotal_formatted}</p>
                                        </div>
                                        ${item.notes ? `<p class="mt-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs text-amber-900">Nota: ${escapeHtml(item.notes)}</p>` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </details>
                    `;
                };

                const cartSummaryCard = (guest) => `
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-950">${escapeHtml(guest.display_alias || guest.alias)}</p>
                                <p class="mt-1 text-xs font-semibold ${guest.is_ready ? 'text-emerald-700' : 'text-amber-700'}">${guest.is_ready ? 'Preseleccion confirmada' : 'Todavia eligiendo'}</p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold tabular-nums text-zinc-950">${guest.subtotal_formatted}</p>
                        </div>
                        <div class="mt-2 space-y-1 text-xs text-zinc-600">
                            ${guest.items.length ? guest.items.map((item) => `
                                <p>${escapeHtml(item.name)} x${item.quantity}${item.notes ? ` - ${escapeHtml(item.notes)}` : ''}</p>
                            `).join('') : '<p>Sin platos seleccionados.</p>'}
                        </div>
                        <button
                            type="button"
                            data-select-guest="${guest.guest_token}"
                            data-after-select="cart"
                            class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-800 transition hover:bg-zinc-50 active:scale-[0.98]"
                        >
                            ${state.order_confirmed ? (guest.id === currentGuestId ? 'Viendo pedido' : 'Ver pedido') : (guest.id === currentGuestId ? 'Seguir editando' : 'Editar preseleccion')}
                        </button>
                    </div>
                `;

                const current = currentGuest();
                const readyCount = state.guests.filter((guest) => guest.is_ready).length;
                const totalGuests = state.guests.length;
                const otherGuests = state.guests.filter((guest) => guest.id !== currentGuestId);
                const canOfferAddGuest = !state.order_confirmed && state.account_mode !== 'joint';
                const showReadyActions = !state.order_confirmed && current?.is_ready;
                const cartMessage = state.order_confirmed
                    ? 'Este pedido ya fue confirmado. Puedes revisar el estado o pedir algo adicional para alguien de la mesa.'
                    : state.can_confirm_order
                        ? 'Todos estan listos. El encargado ya puede confirmar el pedido de la mesa.'
                        : totalGuests
                            ? `Espera que todos elijan lo que quieren. Van ${readyCount}/${totalGuests} listos.`
                            : 'Agrega un alias para empezar a pedir.';

                cartTarget.innerHTML = `
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm leading-6 text-emerald-950">
                        ${escapeHtml(cartMessage)}
                    </div>
                    ${current
                        ? cartCard(current)
                        : '<p class="rounded-2xl bg-zinc-50 p-4 text-sm text-zinc-500">Ingresa tu alias para crear tu carrito.</p>'}
                    ${showReadyActions ? `
                        <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-sm font-semibold text-emerald-950">Preseleccion confirmada.</p>
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                <button type="button" data-cart-ready-action class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-zinc-200 disabled:text-zinc-500">
                                    Editar preseleccion
                                </button>
                                ${canOfferAddGuest ? `
                                    <button type="button" data-release-guest class="inline-flex min-h-12 w-full items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-100 active:scale-[0.98]">
                                        Agregar otra persona
                                    </button>
                                ` : ''}
                            </div>
                        </section>
                    ` : ''}
                    ${otherGuests.length ? `
                        <section>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold text-zinc-950">Lo que han elegido los demas</h3>
                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">${otherGuests.length}</span>
                            </div>
                            <div class="space-y-3">
                                ${otherGuests.map(cartSummaryCard).join('')}
                            </div>
                        </section>
                    ` : ''}
                `;

                const guestOrdersCard = (guest) => {
                    const hasOrders = guest.orders.length > 0;
                    const statusList = hasOrders
                        ? guest.orders.map((order) => orderStatusMeta(order.status, order.status_label))
                        : [];
                    const hasActiveOrder = guest.orders.some((order) => !['delivered', 'cancelled'].includes(order.status));
                    const label = hasOrders
                        ? statusList.at(-1)?.label || 'Enviado'
                        : guest.is_ready
                            ? 'Preseleccion confirmada'
                            : 'Sin pedido';
                    const total = guest.orders.reduce((sum, order) => sum + Number(order.subtotal || 0), 0);
                    const isOpen = openGuestOrderToken === guest.guest_token;

                    return `
                        <details data-guest-order-details data-guest-order-token="${guest.guest_token}" class="gh-guest-order-details rounded-2xl border border-zinc-200 bg-white p-3" ${isOpen ? 'open' : ''}>
                            <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-zinc-950">${escapeHtml(guest.display_alias || guest.alias)}</p>
                                        <p class="mt-1 text-xs text-zinc-500">${hasOrders ? `${guest.orders.length} pedido${guest.orders.length === 1 ? '' : 's'} enviado${guest.orders.length === 1 ? '' : 's'}` : 'Aun no tiene pedidos enviados.'}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold ${hasActiveOrder ? 'border-sky-200 bg-sky-50 text-sky-900' : 'border-emerald-200 bg-emerald-50 text-emerald-900'}">${escapeHtml(label)}</span>
                                        <p class="mt-2 text-sm font-semibold tabular-nums text-zinc-950">${money(total)}</p>
                                    </div>
                                </div>
                                <span class="mt-3 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-800 transition hover:bg-white active:scale-[0.98]">
                                    <span data-detail-label>${isOpen ? 'Ocultar detalles' : 'Ver detalles'}</span>
                                    <span class="gh-guest-order-caret text-base leading-none text-zinc-500" aria-hidden="true">v</span>
                                </span>
                            </summary>
                            <div class="gh-guest-order-content mt-3 space-y-3">
                                ${hasOrders ? guest.orders.map((order) => {
                                    const status = orderStatusMeta(order.status, order.status_label);

                                    return `
                                        <section class="rounded-2xl bg-zinc-50 p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Pedido #${order.id}</p>
                                                        ${order.is_additional ? '<span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-900">Adicional</span>' : '<span class="rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-zinc-600">Pedido inicial</span>'}
                                                        <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold ${status.classes}">${escapeHtml(status.label)}</span>
                                                    </div>
                                                    <p class="mt-2 text-xs leading-5 text-zinc-500">${escapeHtml(status.hint)}</p>
                                                </div>
                                                <p class="shrink-0 text-sm font-semibold tabular-nums text-zinc-950">${order.subtotal_formatted}</p>
                                            </div>
                                            <div class="mt-3 divide-y divide-zinc-100 rounded-xl bg-white px-3">
                                                ${order.items.map((item) => `
                                                    <div class="py-2 text-sm">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <p class="min-w-0 font-medium text-zinc-800">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                                            <p class="shrink-0 text-xs font-semibold tabular-nums text-zinc-500">${item.subtotal_formatted}</p>
                                                        </div>
                                                        ${item.notes ? `<p class="mt-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs text-amber-900">Nota: ${escapeHtml(item.notes)}</p>` : ''}
                                                    </div>
                                                `).join('')}
                                            </div>
                                        </section>
                                    `;
                                }).join('') : '<p class="rounded-xl bg-zinc-50 p-3 text-sm text-zinc-500">Cuando se confirme un pedido, aparecera aqui.</p>'}
                            </div>
                        </details>
                    `;
                };

                const guestsWithOrders = state.guests.filter((guest) => guest.orders.length > 0);
                const selectedExtraGuest = guestsWithOrders.find((guest) => guest.guest_token === extraGuestDropdownState.selectedToken)
                    || guestsWithOrders.find((guest) => guest.id === currentGuestId)
                    || guestsWithOrders[0]
                    || null;
                const isExtraGuestDropdownOpen = Boolean(extraGuestDropdownState.isOpen && selectedExtraGuest);

                if (selectedExtraGuest) {
                    extraGuestDropdownState.selectedToken = selectedExtraGuest.guest_token;
                } else {
                    extraGuestDropdownState = { isOpen: false, selectedToken: null };
                }

                ordersTarget.innerHTML = state.guests.length
                    ? `
                        <div class="space-y-3">
                            ${state.guests.map(guestOrdersCard).join('')}
                        </div>
                        ${state.order_confirmed ? `
                            <section class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                                <h3 class="text-sm font-semibold text-zinc-950">Pedir algo adicional</h3>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">Elige a que persona se le asociara este nuevo pedido.</p>
                                <div class="mt-3 grid gap-2 sm:grid-cols-[1fr_auto]">
                                    <div class="relative" data-extra-guest-select data-open="${isExtraGuestDropdownOpen ? 'true' : 'false'}" data-selected-guest="${selectedExtraGuest?.guest_token || ''}">
                                        <button
                                            type="button"
                                            data-extra-guest-select-toggle
                                            class="flex min-h-12 w-full items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-left text-sm font-semibold text-zinc-950 shadow-sm shadow-zinc-950/[0.03] transition hover:bg-zinc-50 active:scale-[0.99] disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-400"
                                            aria-expanded="${isExtraGuestDropdownOpen ? 'true' : 'false'}"
                                            ${guestsWithOrders.length ? '' : 'disabled'}
                                        >
                                            <span class="min-w-0">
                                                <span class="block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Persona</span>
                                                <span data-extra-guest-selected-label class="block truncate">${selectedExtraGuest ? escapeHtml(selectedExtraGuest.display_alias || selectedExtraGuest.alias) : 'No hay personas con pedidos enviados'}</span>
                                            </span>
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-lg leading-none text-zinc-700" aria-hidden="true">v</span>
                                        </button>
                                        <div class="gh-category-select-panel absolute left-0 right-0 top-full z-40 mt-2 rounded-2xl border border-zinc-200 bg-white p-2 shadow-xl shadow-zinc-950/10" role="listbox" aria-label="Personas con pedidos enviados">
                                            ${guestsWithOrders.map((guest) => `
                                                <button
                                                    type="button"
                                                    data-extra-guest-option="${guest.guest_token}"
                                                    data-extra-guest-label="${escapeHtml(guest.display_alias || guest.alias)}"
                                                    class="flex min-h-11 w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-left text-sm font-semibold transition active:scale-[0.98] ${selectedExtraGuest?.guest_token === guest.guest_token ? 'bg-zinc-950 text-white' : 'text-zinc-700 hover:bg-zinc-50'}"
                                                    role="option"
                                                    aria-selected="${selectedExtraGuest?.guest_token === guest.guest_token ? 'true' : 'false'}"
                                                >
                                                    <span class="truncate">${escapeHtml(guest.display_alias || guest.alias)}</span>
                                                    <span class="text-xs ${selectedExtraGuest?.guest_token === guest.guest_token ? 'text-zinc-300' : 'text-zinc-400'}">${guest.orders.length} ${guest.orders.length === 1 ? 'pedido' : 'pedidos'}</span>
                                                </button>
                                            `).join('')}
                                        </div>
                                    </div>
                                    <button type="button" data-extra-for-selected class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-zinc-200 disabled:text-zinc-500 sm:w-auto" ${guestsWithOrders.length ? '' : 'disabled'}>
                                        Pedir adicional
                                    </button>
                                </div>
                            </section>
                        ` : ''}
                    `
                    : '<p class="rounded-2xl bg-zinc-50 p-4 text-sm text-zinc-500">Aun no hay personas en esta mesa.</p>';

                target.innerHTML = state.guests.length
                    ? state.guests.map(guestCard).join('')
                    : '<p class="text-sm text-zinc-500">Cuando alguien agregue su alias, aparecera aqui.</p>';
            };

            const renderBill = () => {
                const bill = state.bill;
                const panel = root.querySelector('[data-bill-panel]');
                const summary = root.querySelector('[data-bill-summary]');
                const status = root.querySelector('[data-bill-status]');
                const paymentVisible = bill && bill.total > 0 && (bill.payment_ready || bill.is_paid);
                const isJointMode = state.account_mode === 'joint';

                panel.classList.toggle('hidden', !paymentVisible);

                if (!paymentVisible) {
                    return;
                }

                status.textContent = bill.is_paid ? 'Cerrada' : 'Por cobrar';
                status.className = `rounded-md px-2 py-1 text-xs font-semibold ${bill.is_paid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`;

                summary.innerHTML = `
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm leading-6 text-amber-950">
                        ${bill.is_paid
                            ? 'La mesa ya fue cerrada por el restaurante.'
                            : 'Este es el resumen para pagar en efectivo o transferencia. El restaurante confirma el pago y cierra la mesa desde el panel.'}
                    </div>
                    <div class="grid gap-2 rounded-2xl bg-zinc-50 p-3 text-center sm:grid-cols-2">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-500">Total mesa</p>
                            <p class="mt-1 text-sm font-semibold tabular-nums text-zinc-950">${bill.total_formatted}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-500">Pendiente</p>
                            <p class="mt-1 text-sm font-semibold tabular-nums text-amber-700">${bill.balance_formatted}</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        ${bill.participants.map((participant) => {
                            const participantIsOpen = openBillParticipantId === participant.id;
                            const participantAmount = isJointMode ? participant.consumed_formatted : participant.balance_formatted;

                            return `
                                <details data-bill-participant-details data-bill-participant-id="${participant.id}" class="gh-guest-order-details rounded-2xl border border-zinc-200 p-3" ${participantIsOpen ? 'open' : ''}>
                                    <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-zinc-950">${escapeHtml(participant.alias)}</p>
                                                <p class="mt-1 text-xs text-zinc-500">Consumo ${participant.consumed_formatted}</p>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <p class="text-sm font-semibold tabular-nums text-zinc-950">${participantAmount}</p>
                                                <p class="mt-1 text-xs ${participant.balance === 0 ? 'text-emerald-700' : 'text-amber-700'}">${isJointMode ? 'Cuenta conjunta' : (participant.balance === 0 ? 'Cerrado' : 'Debe pagar')}</p>
                                            </div>
                                        </div>
                                        <span class="mt-3 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-800 transition hover:bg-white active:scale-[0.98]">
                                            <span data-detail-label>${participantIsOpen ? 'Ocultar detalles' : 'Ver detalles'}</span>
                                            <span class="gh-guest-order-caret text-base leading-none text-zinc-500" aria-hidden="true">v</span>
                                        </span>
                                    </summary>
                                    <div class="gh-guest-order-content mt-3 space-y-3">
                                        ${participant.orders.length ? participant.orders.map((order) => `
                                            <section class="rounded-2xl bg-zinc-50 p-3">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Pedido #${order.id}</p>
                                                        <p class="mt-1 text-xs text-zinc-500">${escapeHtml(order.status_label)}</p>
                                                    </div>
                                                    <p class="text-sm font-semibold tabular-nums text-zinc-950">${order.subtotal_formatted}</p>
                                                </div>
                                                <div class="mt-3 divide-y divide-zinc-200/70 rounded-xl bg-white px-3">
                                                    ${order.items.map((item) => `
                                                        <div class="py-2 text-sm">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <p class="min-w-0 font-medium text-zinc-800">${escapeHtml(item.name)} <span class="text-zinc-500">x${item.quantity}</span></p>
                                                                <p class="shrink-0 text-xs font-semibold tabular-nums text-zinc-500">${item.subtotal_formatted}</p>
                                                            </div>
                                                            ${item.notes ? `<p class="mt-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs text-amber-900">Nota: ${escapeHtml(item.notes)}</p>` : ''}
                                                        </div>
                                                    `).join('')}
                                                </div>
                                            </section>
                                        `).join('') : '<p class="rounded-xl bg-zinc-50 p-3 text-sm text-zinc-500">Esta persona no tiene pedidos facturables.</p>'}
                                    </div>
                                </details>
                            `;
                        }).join('')}
                    </div>
                    ${bill.payments.length ? `
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Confirmado por el restaurante</p>
                            <div class="mt-2 space-y-1">
                                ${bill.payments.map((payment) => `
                                    <div class="flex items-center justify-between gap-3 text-xs text-emerald-950">
                                        <span>${escapeHtml(payment.type_label)}${payment.guest_alias ? ` - ${escapeHtml(payment.guest_alias)}` : ''}</span>
                                        <span class="font-semibold tabular-nums">${payment.amount_formatted}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                `;
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

                return Boolean(guest && !guest.is_ready && state.session_status === 'open' && !state.bill?.is_paid);
            };

            const currentGuestProductQuantity = (productId) => {
                const guest = state.guests.find((item) => item.id === currentGuestId);
                if (!guest) return 0;
                const item = guest.items.find((entry) => entry.product_id === productId);
                return item ? item.quantity : 0;
            };

            const currentGuestProductItem = (productId) => {
                const guest = state.guests.find((item) => item.id === currentGuestId);

                return guest?.items.find((entry) => entry.product_id === productId) || null;
            };

            const productRenderKey = () => {
                const guest = currentGuest();

                return JSON.stringify({
                    activeModule,
                    selectedCategoryId,
                    search: menuSearch?.value || '',
                    page: productPageByCategory[selectedCategoryId] || 1,
                    orderConfirmed: state.order_confirmed,
                    jointLocked: state.joint_order_locked,
                    sessionStatus: state.session_status,
                    billPaid: Boolean(state.bill?.is_paid),
                    guest: guest
                        ? {
                            id: guest.id,
                            ready: guest.is_ready,
                            items: guest.items.map((item) => [item.product_id, item.quantity, item.notes || '']),
                        }
                        : null,
                    categories: state.categories.map((category) => [
                        category.id,
                        category.products.map((product) => [product.id, product.is_available, product.image_url]),
                    ]),
                });
            };

            const isEditingCartNote = () => document.activeElement?.matches('[data-cart-note], [data-product-note]');

            const orderStatusMeta = (status, label) => ({
                new: {
                    label: label || 'Nuevo',
                    hint: 'Recibido por el restaurante.',
                    classes: 'border-amber-200 bg-amber-50 text-amber-900',
                },
                preparing: {
                    label: label || 'Preparando',
                    hint: 'La cocina ya esta trabajando en este pedido.',
                    classes: 'border-sky-200 bg-sky-50 text-sky-900',
                },
                delivered: {
                    label: label || 'Entregado',
                    hint: 'Este pedido ya fue entregado en la mesa.',
                    classes: 'border-emerald-200 bg-emerald-50 text-emerald-900',
                },
                cancelled: {
                    label: label || 'Cancelado',
                    hint: 'Este pedido fue cancelado y no suma a la cuenta.',
                    classes: 'border-zinc-200 bg-zinc-100 text-zinc-600',
                },
            }[status] || {
                label: label || status,
                hint: 'Estado actualizado por el restaurante.',
                classes: 'border-zinc-200 bg-zinc-100 text-zinc-700',
            });

            root.querySelector('[data-alias-form]').addEventListener('submit', async (event) => {
                event.preventDefault();
                const alias = new FormData(event.currentTarget).get('alias');

                try {
                    state = await request(joinUrl, {
                        method: 'POST',
                        body: JSON.stringify({ alias }),
                    });
                    currentGuestId = state.current_guest_id;
                    root.querySelector('#guest_alias').value = state.guests.find((guest) => guest.id === currentGuestId)?.alias || alias;
                    render();
                    setActiveModule('menu', { scroll: false });
                    setError('');
                } catch (error) {
                    setError('Escribe un alias valido para continuar.');
                }
            });

            root.addEventListener('click', async (event) => {
                const guestOrderSummary = event.target.closest('[data-guest-order-details] summary');
                if (guestOrderSummary) {
                    event.preventDefault();
                    const details = guestOrderSummary.closest('[data-guest-order-details]');
                    const wasOpen = details.open;
                    const label = details.querySelector('[data-detail-label]');

                    root.querySelectorAll('[data-guest-order-details]').forEach((item) => {
                        if (item === details) return;

                        item.classList.remove('gh-guest-order-details-animate', 'gh-guest-order-details-closing');
                        item.removeAttribute('open');
                        item.querySelector('[data-detail-label]')?.replaceChildren(document.createTextNode('Ver detalles'));
                    });

                    details.classList.remove('gh-guest-order-details-animate', 'gh-guest-order-details-closing');

                    if (wasOpen) {
                        openGuestOrderToken = null;
                        label?.replaceChildren(document.createTextNode('Ver detalles'));
                        details.classList.add('gh-guest-order-details-closing');
                        const closeDelay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 180;
                        window.setTimeout(() => {
                            details.removeAttribute('open');
                            details.classList.remove('gh-guest-order-details-closing');
                        }, closeDelay);
                    } else {
                        openGuestOrderToken = details.dataset.guestOrderToken;
                        details.setAttribute('open', '');
                        label?.replaceChildren(document.createTextNode('Ocultar detalles'));
                        window.requestAnimationFrame(() => {
                            details.classList.add('gh-guest-order-details-animate');
                        });
                    }

                    return;
                }

                const billParticipantSummary = event.target.closest('[data-bill-participant-details] summary');
                if (billParticipantSummary) {
                    event.preventDefault();
                    const details = billParticipantSummary.closest('[data-bill-participant-details]');
                    const wasOpen = details.open;
                    const label = details.querySelector('[data-detail-label]');

                    root.querySelectorAll('[data-bill-participant-details]').forEach((item) => {
                        if (item === details) return;

                        item.classList.remove('gh-guest-order-details-animate', 'gh-guest-order-details-closing');
                        item.removeAttribute('open');
                        item.querySelector('[data-detail-label]')?.replaceChildren(document.createTextNode('Ver detalles'));
                    });

                    details.classList.remove('gh-guest-order-details-animate', 'gh-guest-order-details-closing');

                    if (wasOpen) {
                        openBillParticipantId = null;
                        label?.replaceChildren(document.createTextNode('Ver detalles'));
                        details.classList.add('gh-guest-order-details-closing');
                        const closeDelay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 180;
                        window.setTimeout(() => {
                            details.removeAttribute('open');
                            details.classList.remove('gh-guest-order-details-closing');
                        }, closeDelay);
                    } else {
                        openBillParticipantId = Number(details.dataset.billParticipantId);
                        details.setAttribute('open', '');
                        label?.replaceChildren(document.createTextNode('Ocultar detalles'));
                        window.requestAnimationFrame(() => {
                            details.classList.add('gh-guest-order-details-animate');
                        });
                    }

                    return;
                }

                const moduleButton = event.target.closest('[data-module-link]');
                if (moduleButton) {
                    if (state && !currentGuest() && !state.joint_order_locked && moduleButton.dataset.moduleLink !== 'alias') {
                        setActiveModule('alias');
                        return;
                    }

                    setActiveModule(moduleButton.dataset.moduleLink);
                    return;
                }

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
                        setActiveModule('alias', { scroll: false });
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
                        root.querySelector('#guest_alias').value = '';
                        render();
                        setActiveModule('alias', { scroll: false });
                        setError('');
                    } catch (error) {
                        setError('No se pudo preparar el dispositivo para otra persona.');
                    } finally {
                        releaseButton.disabled = false;
                    }

                    return;
                }

                const cartPrimaryButton = event.target.closest('[data-cart-ready-action]');
                if (cartPrimaryButton && !cartPrimaryButton.disabled) {
                    const guest = currentGuest();
                    if (!guest) return;
                    if (state.order_confirmed && guest.is_ready) {
                        setError('Este pedido ya fue confirmado. Para pedir mas, usa la opcion de pedir algo adicional.');

                        return;
                    }

                    const willMarkReady = !guest.is_ready;
                    if (willMarkReady && selectionItemsForGuest(guest).length === 0) {
                        setError('Agrega al menos un producto antes de confirmar tu preseleccion.');

                        return;
                    }

                    try {
                        cartPrimaryButton.disabled = true;
                        const isSendingAdditional = Boolean(state.order_confirmed && willMarkReady);
                        state = await request(readyUrl, {
                            method: 'POST',
                            body: JSON.stringify({ is_ready: willMarkReady }),
                        });
                        currentGuestId = state.current_guest_id;
                        render();
                        if (willMarkReady) {
                            setActiveModule(isSendingAdditional ? 'orders' : 'cart');
                            showToast(isSendingAdditional ? 'Pedido adicional enviado al restaurante.' : 'Preseleccion confirmada.');
                        } else {
                            setActiveModule('cart');
                            showToast('Puedes editar tu preseleccion.');
                        }
                        setError('');
                    } catch (error) {
                        setError('No se pudo actualizar el estado de tu seleccion.');
                    } finally {
                        cartPrimaryButton.disabled = false;
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
                        setActiveModule('orders');
                        showToast('Pedido enviado al restaurante.');
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
                        root.querySelector('#guest_alias').value = state.guests.find((guest) => guest.id === currentGuestId)?.alias || '';
                        render();
                        if (selectGuestButton.dataset.afterSelect) {
                            setActiveModule(selectGuestButton.dataset.afterSelect);
                        }
                        setError('');
                    } catch (error) {
                        setError('No se pudo traer el pedido de esa persona.');
                    } finally {
                        selectGuestButton.disabled = false;
                    }

                    return;
                }

                const extraOrderButton = event.target.closest('[data-extra-for-selected]');
                if (extraOrderButton) {
                    const extraGuestSelect = root.querySelector('[data-extra-guest-select]');
                    const guestToken = extraGuestSelect?.dataset.selectedGuest;
                    if (!guestToken) {
                        setError('Elige una persona para asociar el pedido adicional.');

                        return;
                    }

                    try {
                        extraOrderButton.disabled = true;
                        if (extraGuestSelect) {
                            extraGuestSelect.querySelector('[data-extra-guest-select-toggle]')?.setAttribute('disabled', 'disabled');
                        }

                        state = await request(selectGuestUrl.replace('__guest__', guestToken), { method: 'POST' });
                        currentGuestId = state.current_guest_id;
                        let selectedGuest = currentGuest();
                        root.querySelector('#guest_alias').value = selectedGuest?.alias || '';

                        if (selectedGuest?.is_ready) {
                            state = await request(readyUrl, {
                                method: 'POST',
                                body: JSON.stringify({ is_ready: false }),
                            });
                            currentGuestId = state.current_guest_id;
                            selectedGuest = currentGuest();
                        }

                        extraGuestDropdownState.isOpen = false;
                        render();
                        setActiveModule('menu');
                        showToast(`Nuevo adicional para ${selectedGuest?.alias || 'esta persona'}.`);
                        setError('');
                    } catch (error) {
                        setError('No se pudo iniciar el pedido adicional.');
                    } finally {
                        extraOrderButton.disabled = false;
                        if (extraGuestSelect) {
                            extraGuestSelect.querySelector('[data-extra-guest-select-toggle]')?.removeAttribute('disabled');
                        }
                    }

                    return;
                }

                const clearCartButton = event.target.closest('[data-clear-cart]');
                if (clearCartButton) {
                    if (!window.confirm('Vaciar tu carrito actual?')) {
                        return;
                    }

                    try {
                        clearCartButton.disabled = true;
                        state = await request(clearCartUrl, { method: 'POST' });
                        currentGuestId = state.current_guest_id;
                        render();
                        showToast('Carrito vaciado.');
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
                    productPageByCategory[selectedCategoryId] = 1;
                    renderProducts();
                    root.querySelector('[data-category-select]')?.setAttribute('data-open', 'false');
                    root.querySelector('[data-category-select-toggle]')?.setAttribute('aria-expanded', 'false');
                    return;
                }

                const productsPageButton = event.target.closest('[data-products-page]');
                if (productsPageButton && !productsPageButton.disabled) {
                    productPageByCategory[selectedCategoryId] = Number(productsPageButton.dataset.productsPage);
                    renderProducts();
                    root.querySelector('[data-products]')?.scrollIntoView({ block: 'start', behavior: 'smooth' });
                    return;
                }

                const categorySelectToggle = event.target.closest('[data-category-select-toggle]');
                if (categorySelectToggle) {
                    const select = categorySelectToggle.closest('[data-category-select]');
                    const isOpen = select?.dataset.open === 'true';
                    select?.setAttribute('data-open', isOpen ? 'false' : 'true');
                    categorySelectToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                    return;
                }

                const extraGuestSelectToggle = event.target.closest('[data-extra-guest-select-toggle]');
                if (extraGuestSelectToggle && !extraGuestSelectToggle.disabled) {
                    const select = extraGuestSelectToggle.closest('[data-extra-guest-select]');
                    const isOpen = select?.dataset.open === 'true';
                    const nextOpen = !isOpen;
                    extraGuestDropdownState.isOpen = nextOpen;
                    extraGuestDropdownState.selectedToken = select?.dataset.selectedGuest || extraGuestDropdownState.selectedToken;
                    select?.setAttribute('data-open', nextOpen ? 'true' : 'false');
                    extraGuestSelectToggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
                    return;
                }

                const extraGuestOption = event.target.closest('[data-extra-guest-option]');
                if (extraGuestOption) {
                    const select = extraGuestOption.closest('[data-extra-guest-select]');
                    const selectedLabel = select?.querySelector('[data-extra-guest-selected-label]');
                    extraGuestDropdownState = {
                        isOpen: false,
                        selectedToken: extraGuestOption.dataset.extraGuestOption,
                    };
                    select.dataset.selectedGuest = extraGuestOption.dataset.extraGuestOption;
                    if (selectedLabel) {
                        selectedLabel.textContent = extraGuestOption.dataset.extraGuestLabel || 'Persona seleccionada';
                    }

                    select.querySelectorAll('[data-extra-guest-option]').forEach((option) => {
                        const isSelected = option === extraGuestOption;
                        option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                        option.classList.toggle('bg-zinc-950', isSelected);
                        option.classList.toggle('text-white', isSelected);
                        option.classList.toggle('text-zinc-700', !isSelected);
                        option.classList.toggle('hover:bg-zinc-50', !isSelected);
                        option.querySelector('span:last-child')?.classList.toggle('text-zinc-300', isSelected);
                        option.querySelector('span:last-child')?.classList.toggle('text-zinc-400', !isSelected);
                    });

                    select.setAttribute('data-open', 'false');
                    select.querySelector('[data-extra-guest-select-toggle]')?.setAttribute('aria-expanded', 'false');
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
                    showToast(Number(button.dataset.delta) > 0 ? 'Producto agregado.' : 'Producto actualizado.');
                    setError('');
                } catch (error) {
                    setError('Primero ingresa tu alias o revisa si el producto esta disponible.');
                }
            });

            document.addEventListener('click', (event) => {
                if (!root.contains(event.target)) return;
                if (event.target.closest('[data-category-select]')) return;
                if (event.target.closest('[data-extra-guest-select]')) return;

                root.querySelector('[data-category-select]')?.setAttribute('data-open', 'false');
                root.querySelector('[data-category-select-toggle]')?.setAttribute('aria-expanded', 'false');
                extraGuestDropdownState.isOpen = false;
                root.querySelector('[data-extra-guest-select]')?.setAttribute('data-open', 'false');
                root.querySelector('[data-extra-guest-select-toggle]')?.setAttribute('aria-expanded', 'false');
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;

                root.querySelector('[data-category-select]')?.setAttribute('data-open', 'false');
                root.querySelector('[data-category-select-toggle]')?.setAttribute('aria-expanded', 'false');
                extraGuestDropdownState.isOpen = false;
                root.querySelector('[data-extra-guest-select]')?.setAttribute('data-open', 'false');
                root.querySelector('[data-extra-guest-select-toggle]')?.setAttribute('aria-expanded', 'false');
            });

            root.addEventListener('change', async (event) => {
                const noteInput = event.target.closest('[data-cart-note], [data-product-note]');
                if (!noteInput) return;

                try {
                    noteInput.disabled = true;
                    state = await request(itemsUrl, {
                        method: 'POST',
                        body: JSON.stringify({
                            product_id: Number(noteInput.dataset.cartNote || noteInput.dataset.productNote),
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

            menuSearch?.addEventListener('input', () => {
                if (selectedCategoryId) {
                    productPageByCategory[selectedCategoryId] = 1;
                }

                renderProducts();
            });

            loadState();
            setInterval(() => loadState({ refreshProducts: false }), 3000);
        })();
    </script>
@endsection
