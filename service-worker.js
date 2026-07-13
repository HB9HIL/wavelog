/* Wavelog Mobile Service Worker
 * Scope: / (entire origin)
 * Registered only from: application/views/mobile/_layout.php
 *
 * Strategy:
 *   Cache-First  — static assets (/assets/mobile/*, /assets/icons/*, Bootstrap, manifest)
 *   Network-First with cache fallback — /mobile/* HTML routes
 *
 * To update the cache: bump CACHE_VERSION and redeploy.
 */

const CACHE_VERSION = 'wl-mobile-v2';

const STATIC_ASSETS = [
    '/assets/css/default/bootstrap.min.css',
    '/assets/js/bootstrap.bundle.min.js',
    '/assets/fontawesome/css/all.min.css',
    '/assets/mobile/css/app.css',
    '/assets/js/mobile/offline-log.js',
    '/assets/icons/android/android-launchericon-192-192.png',
    '/assets/icons/android/android-launchericon-512-512.png',
    '/manifest.json',
];

// Precache the log route too, so QSOs can be entered while fully offline.
const OFFLINE_FALLBACK = '/mobile/dashboard';
const PRECACHE_ROUTES  = ['/mobile/dashboard', '/mobile/log'];

// ── Install: precache app shell ───────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then(cache =>
            cache.addAll([...STATIC_ASSETS, ...PRECACHE_ROUTES])
        )
    );
    self.skipWaiting();
});

// ── Activate: remove stale caches ────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(k => k !== CACHE_VERSION)
                    .map(k => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ── Fetch: routing strategy ───────────────────────────────────
self.addEventListener('fetch', event => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Cache-First: static assets
    const isCacheFirst =
        url.pathname.startsWith('/assets/mobile/') ||
        url.pathname.startsWith('/assets/icons/') ||
        url.pathname.startsWith('/assets/fontawesome/') ||
        url.pathname === '/manifest.json' ||
        // Bootstrap and other bundled JS/CSS already in STATIC_ASSETS
        (url.pathname.startsWith('/assets/') && (
            url.pathname.endsWith('.css') ||
            url.pathname.endsWith('.js') ||
            url.pathname.endsWith('.woff2') ||
            url.pathname.endsWith('.woff') ||
            url.pathname.endsWith('.png') ||
            url.pathname.endsWith('.svg')
        ));

    if (isCacheFirst) {
        event.respondWith(
            caches.match(event.request).then(cached =>
                cached || fetch(event.request).then(response => {
                    // Cache successful responses for future offline use
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_VERSION).then(c => c.put(event.request, clone));
                    }
                    return response;
                })
            )
        );
        return;
    }

    // Network-First with cache fallback: /mobile/* HTML routes
    if (url.pathname.startsWith('/mobile')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_VERSION).then(c => c.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(event.request).then(cached =>
                        cached || caches.match(OFFLINE_FALLBACK)
                    )
                )
        );
    }
});
