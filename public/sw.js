/**
 * PassVault — Service Worker
 * Zero-Knowledge Safe Offline Caching Strategy
 */

const CACHE_NAME = 'passvault-static-v1.4';

// Static assets to pre-cache on install
const PRECACHE_ASSETS = [
    './manifest.json',
    './assets/img/favicon.svg',
    './assets/img/logo.svg',
    './assets/img/icon-192.png',
    './assets/img/icon-512.png',
    './assets/js/crypto.js',
    './assets/js/generator.js',
    './assets/js/qrcode.min.js',
    './assets/js/app.js',
    './assets/js/vault.js'
];

// Install Event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('[SW] Pre-cache partial fail:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Event: bersihkan cache versi lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Abaikan skema non-HTTP/HTTPS (misalnya chrome-extension://, moz-extension://)
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // SECURITY CRITICAL:
    // Seluruh rute API (/api/*), rute auth login/logout, dan method non-GET TIDAK BOLEH dicache!
    if (request.method !== 'GET' || url.pathname.includes('/api/') || url.pathname.includes('/logout')) {
        event.respondWith(fetch(request));
        return;
    }

    // Static Assets (JS, CSS, Gambar, Fonts): Cache-First dengan Network Update
    if (
        url.pathname.match(/\.(js|css|svg|png|jpg|jpeg|woff2|woff|ttf|ico)$/) ||
        url.hostname.includes('cdn.tailwindcss.com') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache in background
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseToCache));
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // Navigasi HTML: Network-First dengan Cache Fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;
                    return caches.match('./assets/img/favicon.svg');
                });
            })
        );
        return;
    }

    // Default fetch
    event.respondWith(fetch(request));
});
