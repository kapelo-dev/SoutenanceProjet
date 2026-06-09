const DEFAULTS = {
    position: 'top-end',
    appearance: 'light',
    size: 'md',
    duration: 5000,
};

const STORAGE_KEY = 'app_pending_toast';

function normalizeMessage(message) {
    if (message == null) return '';
    return String(message).replace(/\s+/g, ' ').trim();
}

function show(variant, message, options = {}) {
    const msg = normalizeMessage(message);
    if (!msg) return;

    const opts = {
        message: msg,
        variant: variant || 'info',
        position: options.position || DEFAULTS.position,
        appearance: options.appearance || DEFAULTS.appearance,
        size: options.size || DEFAULTS.size,
        duration: options.duration ?? DEFAULTS.duration,
        dismiss: options.dismiss !== false,
    };

    if (typeof KTToast !== 'undefined' && typeof KTToast.show === 'function') {
        KTToast.show(opts);
        return;
    }

    const el = document.createElement('div');
    el.className = `fixed top-4 end-4 z-[10000] max-w-sm rounded-lg border px-4 py-3 text-sm shadow-lg ${
        variant === 'error' || variant === 'destructive'
            ? 'border-red-200 bg-red-50 text-red-800'
            : variant === 'warning'
              ? 'border-amber-200 bg-amber-50 text-amber-900'
              : 'border-green-200 bg-green-50 text-green-800'
    }`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), opts.duration);
}

export const AppToast = {
    show(message, variant = 'info', options = {}) {
        show(variant, message, options);
    },
    success(message, options = {}) {
        show('success', message, options);
    },
    error(message, options = {}) {
        show('error', message, options);
    },
    warning(message, options = {}) {
        show('warning', message, options);
    },
    info(message, options = {}) {
        show('info', message, options);
    },
    reload(message, variant = 'success', options = {}) {
        sessionStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({ variant, message, options })
        );
        window.location.reload();
    },
};

function flushPendingToast() {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (!raw) return false;

    try {
        const { variant, message, options } = JSON.parse(raw);
        sessionStorage.removeItem(STORAGE_KEY);
        show(variant || 'success', message, options || {});
        return true;
    } catch {
        sessionStorage.removeItem(STORAGE_KEY);
        return false;
    }
}

export function initFlashMessages() {
    if (flushPendingToast()) return;

    const el = document.getElementById('app-flash-messages');
    if (!el) return;

    try {
        const data = JSON.parse(el.textContent || '{}');
        if (data.success) AppToast.success(data.success);
        else if (data.error) AppToast.error(data.error);
        else if (data.status) AppToast.info(data.status);
    } catch {
        // ignore invalid JSON
    }

    el.remove();
}

window.AppToast = AppToast;

document.addEventListener('DOMContentLoaded', initFlashMessages);
