// LifeLine service worker (installable PWA, DEC-001). Kept small on purpose:
// - pages and /api/v1 always come from the network (never cached: they hold personal data);
// - when a page cannot load offline, the cached offline.html is shown instead;
// - the app's own CSS, scripts, icons and fonts still come from the network first; a copy is kept as a
//   backup for when the connection drops.
const CACHE = 'lifeline-v2';
const OFFLINE_URL = new URL('offline.html', self.location).href;
const PRECACHE = [
    'offline.html',
    'css/lifeline.css',
    'vendor/bootstrap/bootstrap.min.css',
    'vendor/bootstrap-icons/bootstrap-icons.min.css',
    'icons/icon-192.png',
].map((path) => new URL(path, self.location).href);

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    // Remove caches from older versions of this file.
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // Static files only: same-origin css, js, vendor, icons. Everything else goes straight to the network.
    if (/^\/(css|js|vendor|icons)\//.test(url.pathname)) {
        event.respondWith(
            caches.open(CACHE).then((cache) => fetch(request)
                .then((response) => {
                    if (response.ok) cache.put(request, response.clone());
                    return response;
                })
                .catch(() => cache.match(request)))
        );
    }
});
