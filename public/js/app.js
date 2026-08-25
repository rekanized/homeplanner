(() => {
    let idCounter = 0;
    let activeDialog = null;
    let focusBeforeDialog = null;

    const isSwedish = () => document.documentElement.lang.toLowerCase().startsWith('sv');

    const actionLabel = (button) => {
        const action = button.getAttribute('wire:click') || '';
        const labels = isSwedish()
            ? { delete: 'Ta bort', remove: 'Ta bort', incrementQuantity: 'Öka antal', decrementQuantity: 'Minska antal' }
            : { delete: 'Delete', remove: 'Remove', incrementQuantity: 'Increase quantity', decrementQuantity: 'Decrease quantity' };

        const match = Object.keys(labels).find((name) => action.startsWith(name));
        return match ? labels[match] : '';
    };

    const enhanceLabels = (root) => {
        root.querySelectorAll?.('label:not([for])').forEach((label) => {
            if (label.querySelector('input, select, textarea')) return;
            const container = label.parentElement;
            const control = container?.querySelector('input:not([type="hidden"]), select, textarea');
            if (!control || control.id) return;

            control.id = `field-${++idCounter}`;
            label.htmlFor = control.id;
        });
    };

    const enhanceButtons = (root) => {
        root.querySelectorAll?.('button').forEach((button) => {
            const hasName = button.textContent.trim() || button.getAttribute('aria-label');
            if (!hasName) {
                const label = button.title || actionLabel(button);
                if (label) button.setAttribute('aria-label', label);
            }

            if (button.textContent.trim() === '×') {
                button.type = 'button';
                button.classList.add('modal-close-button');
                button.setAttribute('aria-label', isSwedish() ? 'Stäng dialogrutan' : 'Close dialog');
            }
        });
    };

    const isVisible = (element) => {
        const style = getComputedStyle(element);
        return style.display !== 'none' && style.visibility !== 'hidden' && element.getClientRects().length > 0;
    };

    const focusableElements = (dialog) => [...dialog.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )].filter(isVisible);

    const enhanceDialogs = () => {
        const dialog = [...document.querySelectorAll('.modal-content')].find(isVisible);

        if (!dialog) {
            if (activeDialog) {
                activeDialog = null;
                document.body.classList.remove('modal-open');
                focusBeforeDialog?.focus?.({ preventScroll: true });
                focusBeforeDialog = null;
            }
            return;
        }

        const heading = dialog.querySelector('h1, h2, h3');
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        if (heading) {
            if (!heading.id) heading.id = `dialog-title-${++idCounter}`;
            dialog.setAttribute('aria-labelledby', heading.id);
        }

        if (activeDialog === dialog) return;

        focusBeforeDialog = document.activeElement;
        activeDialog = dialog;
        document.body.classList.add('modal-open');
        requestAnimationFrame(() => {
            const target = dialog.querySelector('[autofocus]') || focusableElements(dialog)[0];
            target?.focus?.({ preventScroll: true });
        });
    };

    const enhance = (root = document) => {
        enhanceLabels(root);
        enhanceButtons(root);
        enhanceDialogs();
    };

    document.addEventListener('keydown', (event) => {
        if (!activeDialog) return;

        if (event.key === 'Escape') {
            const close = [...activeDialog.querySelectorAll('button')].find((button) =>
                button.classList.contains('modal-close-button') || button.textContent.trim().toLowerCase() === 'cancel'
            );
            close?.click();
            return;
        }

        if (event.key !== 'Tab') return;
        const focusable = focusableElements(activeDialog);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    const observer = new MutationObserver((mutations) => {
        if (mutations.some((mutation) => mutation.addedNodes.length || mutation.removedNodes.length)) {
            enhance();
        }
    });

    const start = () => {
        enhance();
        observer.observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }

    document.addEventListener('livewire:navigated', () => enhance());
})();
