// Small helpers used on every page (no build step).
(function () {
    // Installable PWA (DEC-001): the service worker shows an offline page when there is no internet.
    // sw.js sits next to the js/ folder, so this works wherever the app is served from.
    if ('serviceWorker' in navigator && document.currentScript) {
        navigator.serviceWorker.register(new URL('../sw.js', document.currentScript.src)).catch(() => {});
    }

    // Live unread badge on the Alerts tab: checks once a minute while the page is visible.
    const badge = document.querySelector('[data-unread-badge][data-poll]');
    const badgeText = document.querySelector('[data-unread-text]');
    if (badge) {
        const refresh = () => {
            if (document.hidden) return;
            fetch(badge.dataset.poll, { headers: { Accept: 'application/json' } })
                .then((response) => (response.ok ? response.json() : Promise.reject(response)))
                .then((body) => {
                    const count = body.unread_count;
                    badge.textContent = count;
                    badge.hidden = count === 0;
                    if (badgeText) badgeText.textContent = count > 0 ? ', ' + count + ' unread' : '';
                })
                .catch(() => {});
        };
        setInterval(refresh, 60000);
        document.addEventListener('visibilitychange', refresh);
    }

    // <form data-submit-once>: a double click or a second tap sends the form only once.
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-submit-once') || event.defaultPrevented) return;

        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = true;
        });
    });

    // <button data-toggle-password="field-id">: show or hide the password the user typed.
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-toggle-password]');
        if (!button) return;
        const field = document.getElementById(button.dataset.togglePassword);
        if (!field) return;
        const show = field.type === 'password';
        field.type = show ? 'text' : 'password';
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        button.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Coming back with the browser's Back button shows the cached page: make the form usable again.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;
        document.querySelectorAll('form[data-submit-once]').forEach((form) => {
            delete form.dataset.submitting;
            form.removeAttribute('aria-busy');
            form.querySelectorAll('button[type="submit"]').forEach((button) => {
                button.disabled = false;
            });
        });
    });
})();
