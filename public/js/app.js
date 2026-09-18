(() => {
    // Livewire navigation can encounter this asset again after visiting a guest page.
    if (window.homeplannerUI) return;
    window.homeplannerUI = true;

    let idCounter = 0;
    let activeDialog = null;
    let focusBeforeDialog = null;
    let inertElements = [];
    let lockedScrollY = 0;

    const isSwedish = () => document.documentElement.lang.toLowerCase().startsWith('sv');

    const actionLabel = (button) => {
        const action = button.getAttribute('wire:click') || '';
        const labels = isSwedish()
            ? { delete: 'Ta bort', remove: 'Ta bort', incrementQuantity: 'Öka antal', decrementQuantity: 'Minska antal' }
            : { delete: 'Delete', remove: 'Remove', incrementQuantity: 'Increase quantity', decrementQuantity: 'Decrease quantity' };

        if (action.startsWith('toggleItem')) {
            return button.getAttribute('aria-pressed') === 'true'
                ? (isSwedish() ? 'Markera som ej klar' : 'Mark incomplete')
                : (isSwedish() ? 'Markera som klar' : 'Mark complete');
        }
        const match = Object.keys(labels).find((name) => action.startsWith(name));
        return match ? labels[match] : '';
    };

    const enhanceLabels = (root) => {
        root.querySelectorAll?.('label:not([for])').forEach((label) => {
            if (label.querySelector('input, select, textarea')) return;
            const container = label.parentElement;
            const control = container?.querySelector('input:not([type="hidden"]), select, textarea');
            if (!control) return;
            if (!control.id) control.id = `field-${++idCounter}`;
            label.htmlFor = control.id;
        });
    };

    const enhanceButtons = (root) => {
        root.querySelectorAll?.('button').forEach((button) => {
            if ((button.getAttribute('wire:click') || '').startsWith('toggleItem')) {
                button.setAttribute('aria-label', actionLabel(button));
            }
            const hasName = button.textContent.trim() || button.getAttribute('aria-label');
            if (!hasName) {
                const label = button.title || actionLabel(button);
                if (label) button.setAttribute('aria-label', label);
            }

            if (button.textContent.trim() === '×' && button.closest('.modal-content')) {
                button.type = 'button';
                if (!button.classList.contains('modal-close-button')) button.classList.add('modal-close-button');
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
        const dialog = [...document.querySelectorAll('.modal-content, .mobile-nav-sheet')].find(isVisible);

        if (!dialog) {
            if (activeDialog) {
                activeDialog = null;
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('--locked-scroll-y');
                inertElements.forEach((element) => { element.inert = false; });
                inertElements = [];
                window.scrollTo(0, lockedScrollY);
                if (focusBeforeDialog?.isConnected) focusBeforeDialog.focus?.({ preventScroll: true });
                focusBeforeDialog = null;
            }
            return;
        }

        const heading = [...dialog.querySelectorAll('h1, h2, h3')].find(isVisible);
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        if (heading) {
            if (!heading.id) heading.id = `dialog-title-${++idCounter}`;
            dialog.setAttribute('aria-labelledby', heading.id);
        }

        if (activeDialog === dialog) return;

        if (!activeDialog) {
            focusBeforeDialog = document.activeElement;
            lockedScrollY = window.scrollY;
            document.body.style.setProperty('--locked-scroll-y', `${-lockedScrollY}px`);
        }
        inertElements.forEach((element) => { element.inert = false; });
        const background = dialog.classList.contains('mobile-nav-sheet')
            ? '#main-content, .mobile-tabbar, .sidebar-desktop-shell, .main-fab'
            : '.layout-container, .main-fab';
        inertElements = [...document.querySelectorAll(background)].filter((element) => !element.inert && !element.contains(dialog));
        inertElements.forEach((element) => { element.inert = true; });
        activeDialog = dialog;
        document.body.classList.add('modal-open');
        dialog.tabIndex = -1;
        requestAnimationFrame(() => {
            // Keep the software keyboard closed until the user chooses to type.
            const target = window.matchMedia('(pointer: coarse)').matches
                ? dialog : dialog.querySelector('[autofocus], [data-dialog-autofocus]') || focusableElements(dialog)[0] || dialog;
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
                isVisible(button) && (button.classList.contains('modal-close-button') || button.classList.contains('mobile-nav-sheet-close') || ['cancel', 'avbryt'].includes(button.textContent.trim().toLowerCase()))
            );
            if (close) {
                event.preventDefault();
                event.stopPropagation();
                close.click();
            }
            return;
        }

        if (event.key !== 'Tab') return;
        const focusable = focusableElements(activeDialog);
        if (!focusable.length) { event.preventDefault(); activeDialog.focus(); return; }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && (document.activeElement === first || !focusable.includes(document.activeElement))) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && (document.activeElement === last || !activeDialog.contains(document.activeElement))) {
            event.preventDefault();
            first.focus();
        }
    });

    let enhancementPending = false;
    let contentChanged = false;
    const observer = new MutationObserver((mutations) => {
        contentChanged ||= mutations.some((mutation) => mutation.type === 'childList');
        if (enhancementPending) return;
        enhancementPending = true;
        requestAnimationFrame(() => {
            enhancementPending = false;
            if (contentChanged) {
                contentChanged = false;
                enhance();
            } else {
                // Alpine animations and chart hovers only need visibility checks.
                enhanceDialogs();
            }
        });
    });

    // The visual viewport follows the software keyboard on iOS as well as Android.
    const updateViewport = () => {
        const viewport = window.visualViewport;
        document.documentElement.style.setProperty('--visible-height', `${viewport?.height || window.innerHeight}px`);
        document.documentElement.style.setProperty('--visible-top', `${viewport?.offsetTop || 0}px`);
        const editing = document.activeElement?.matches('input, textarea, select');
        document.body.classList.toggle('keyboard-open', !!editing && window.innerHeight - (viewport?.height || window.innerHeight) > 150);
        enhanceDialogs();
    };

    const start = () => {
        enhance();
        observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class'] });
        updateViewport();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }

    document.addEventListener('livewire:navigated', () => enhance());
    window.addEventListener('resize', updateViewport);
    window.visualViewport?.addEventListener('resize', updateViewport);
    window.visualViewport?.addEventListener('scroll', updateViewport);
    document.addEventListener('focusout', () => requestAnimationFrame(updateViewport));
})();
