const CACHE_NAME = 'picme-pwa-v2';
const ASSETS_TO_CACHE = [
    '/offline',
    '/logo.png',
    '/asset/css/bootstrap.min.css',
    '/asset/css/style.css',
    '/asset/js/jquery.min.js',
    '/asset/js/bootstrap.min.js'
];

// Install Event: Pre-cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Opened cache');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// Activate Event: Cleanup old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch Event: Network-first for HTML, Cache-first for static assets
self.addEventListener('fetch', (event) => {
    // Only intercept GET requests
    if (event.request.method !== 'GET') return;

    // For HTML requests, go network-first
    if (event.request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match('/offline');
            })
        );
    } else {
        // For other requests (CSS, JS, Images), go cache-first
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request);
            })
        );
    }
});
