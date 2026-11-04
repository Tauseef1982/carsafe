const CACHE_NAME = 'carsafe-driver-v1.0.1';
const OFFLINE_URL = '/offline.html';

// Core static assets to pre-cache on service worker installation
const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/assets/images/logo/carsafe-icon.png',
    '/assets/images/logo/carsafe-icon-192.png',
    '/assets/images/logo/carsafe-icon-512.png',
    '/assets/images/logo/carsafe-maskable-192.png',
    '/assets/images/logo/carsafe-maskable-512.png',
    '/assets/css/vendors/bootstrap.css',
    '/assets/css/style.css',
    '/assets/css/responsive.css',
    '/assets/css/color-1.css',
    '/assets/js/bootstrap/bootstrap.bundle.min.js'
];

// 1. Install event: pre-cache offline page & core styles
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

// 2. Activate event: clean up older caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch event
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Never intercept non-GET requests (POST / PUT / DELETE payment, OTP, trip updates)
    if (request.method !== 'GET') {
        return;
    }

    // Skip third-party or chrome-extension schemes
    if (!request.url.startsWith('http')) {
        return;
    }

    // Skip API, payment webhooks, or translate tools
    if (
        request.url.includes('/api/') ||
        request.url.includes('/genToken') ||
        request.url.includes('translate.google.com')
    ) {
        return;
    }

    // Strategy A: Page navigations (HTML responses) -> Network-First, fallback to offline page
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    return networkResponse;
                })
                .catch(async () => {
                    const cache = await caches.open(CACHE_NAME);
                    const cachedResponse = await cache.match(request);
                    return cachedResponse || cache.match(OFFLINE_URL);
                })
        );
        return;
    }

    // Strategy B: Static assets (CSS, JS, Fonts, Images) -> Stale-While-Revalidate / Cache-First
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font'
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache in the background
                    fetch(request)
                        .then((networkResponse) => {
                            if (networkResponse && networkResponse.status === 200) {
                                caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                            }
                        })
                        .catch(() => {});
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                        return networkResponse;
                    }
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseToCache);
                    });
                    return networkResponse;
                });
            })
        );
    }
});

