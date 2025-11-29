import './bootstrap';

import Alpine from 'alpinejs';
import './pages/checkout';
import './pages/order-payment';
import './pages/profile-addresses';

window.Alpine = Alpine;

Alpine.start();

const quantityLockSelector = '[data-quantity-lock]';
const allowedKeys = new Set([
    'Tab',
    'Shift',
    'Enter',
    'Escape',
    'ArrowUp',
    'ArrowDown',
    'ArrowLeft',
    'ArrowRight',
    'Home',
    'End',
]);

function lockQuantityInput(input) {
    if (!input || input.dataset.quantityLockBound === 'true') {
        return;
    }

    function blockKey(event) {
        if (allowedKeys.has(event.key) || event.metaKey || event.ctrlKey || event.altKey) {
            return;
        }
        event.preventDefault();
    }

    const blockUserInput = (event) => event.preventDefault();

    input.addEventListener('keydown', blockKey);
    input.addEventListener('paste', blockUserInput);
    input.addEventListener('drop', blockUserInput);

    input.dataset.quantityLockBound = 'true';
}

function initQuantityLocks() {
    if (typeof document === 'undefined') {
        return;
    }
    document.querySelectorAll(quantityLockSelector).forEach(lockQuantityInput);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuantityLocks);
    } else {
        initQuantityLocks();
    }

    document.addEventListener('turbo:load', initQuantityLocks);
}
