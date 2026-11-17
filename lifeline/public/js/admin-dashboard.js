// Admin dashboard charts (M2.6). Numbers from GET /api/v1/admin/stats (login session, DEC-015),
// the same Statistics class that fills the cards. Chart.js is served from public/vendor (DEC-013).
(function () {
    const app = document.getElementById('admin-dashboard');
    if (!app || !window.Chart) return;

    const status = app.querySelector('[data-chart-status]');
    const RED = '#d32f2f';
    const STATUS_LABELS = {
        open: 'Looking for donors', donor_found: 'Donor found', fulfilled: 'Fulfilled', cancelled: 'Cancelled', expired: 'Expired',
    };
    const STATUS_COLORS = ['#ffc107', '#0dcaf0', '#198754', '#6c757d', '#adb5bd'];

    function canvas(name) {
        return app.querySelector('[data-chart="' + name + '"]');
    }

    function draw(stats) {
        const statusKeys = Object.keys(stats.requests_by_status);
        new Chart(canvas('status'), {
            type: 'doughnut',
            data: {
                labels: statusKeys.map((key) => STATUS_LABELS[key] || key),
                datasets: [{ data: statusKeys.map((key) => stats.requests_by_status[key]), backgroundColor: STATUS_COLORS }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });

        new Chart(canvas('blood'), {
            type: 'bar',
            data: {
                labels: Object.keys(stats.donors_by_blood_type),
                datasets: [{ label: 'Donors', data: Object.values(stats.donors_by_blood_type), backgroundColor: RED }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });

        new Chart(canvas('daily'), {
            type: 'line',
            data: {
                labels: stats.requests_last_14_days.map((day) => day.date.slice(5)),
                datasets: [{
                    label: 'Requests', data: stats.requests_last_14_days.map((day) => day.count),
                    borderColor: RED, backgroundColor: 'rgba(211, 47, 47, 0.15)', fill: true, tension: 0.3,
                }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
    }

    fetch(app.dataset.api, { headers: { Accept: 'application/json' } })
        .then((response) => (response.ok ? response.json() : Promise.reject(response)))
        .then((body) => draw(body.data))
        .catch(() => { status.textContent = 'Could not load the charts. Refresh the page to try again.'; });
})();
