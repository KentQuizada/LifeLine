// Small helpers used on every page (no build step).
(function () {
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
