document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            const message = element.getAttribute('data-confirm') || 'Confirmas esta accion?';

            if (!window.confirm(message)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.getAttribute('data-password-toggle'));
            if (!target) return;

            const nextType = target.type === 'password' ? 'text' : 'password';
            target.type = nextType;
            button.textContent = nextType === 'password' ? 'Mostrar' : 'Ocultar';
        });
    });

    const flash = document.querySelector('[data-flash-message]');
    if (flash) {
        const toast = document.createElement('div');
        toast.className = 'gh-toast gh-reveal';
        toast.setAttribute('role', 'status');
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">OK</span>
                <div class="min-w-0 flex-1">${flash.textContent}</div>
                <button type="button" class="rounded-md px-2 text-emerald-900 hover:bg-emerald-100" aria-label="Cerrar notificacion">x</button>
            </div>
        `;

        document.body.appendChild(toast);
        const close = () => toast.remove();
        toast.querySelector('button')?.addEventListener('click', close);
        window.setTimeout(close, 4200);
    }
});
