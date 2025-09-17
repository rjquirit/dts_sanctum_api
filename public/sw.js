const CACHE_VERSION = 'v1.0';
const STATIC_CACHE = `static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `dynamic-${CACHE_VERSION}`;
const API_CACHE = `api-${CACHE_VERSION}`;

// Assets to cache immediately - update paths to match your actual files
const STATIC_ASSETS = [
    '/',
    '/offline',
    '/css/style.css',
    '/js/main.js',
    '/manifest.json',
    // Only include icons that actually exist
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// Install event handler with error checking
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                // Cache files individually to handle failures
                return Promise.allSettled(
                    STATIC_ASSETS.map(url => {
                        return cache.add(url).catch(error => {
                            console.error(`Failed to cache: ${url}`, error);
                            return Promise.resolve(); // Continue despite error
                        });
                    })
                );
            })
            .then(() => self.skipWaiting())
            .catch(error => {
                console.error('Service Worker installation failed:', error);
            })
    );
});

// Activate event handler
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => {
                return Promise.all(
                    keys.filter(key => {
                        return !key.includes(CACHE_VERSION);
                    }).map(key => {
                        return caches.delete(key);
                    })
                );
            })
            .then(() => {
                self.clients.claim();
                console.log('Service Worker activated');
            })
            .catch(error => {
                console.error('Service Worker activation failed:', error);
            })
    );
});

// Fetch event handler with fallback
self.addEventListener('fetch', event => {
    // Don't cache browser-sync requests during development
    if (event.request.url.includes('browser-sync')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(response => {
                if (response) {
                    return response; // Return cached response
                }

                return fetch(event.request)
                    .then(networkResponse => {
                        // Don't cache responses that aren't successful
                        if (!networkResponse || networkResponse.status !== 200) {
                            return networkResponse;
                        }

                        // Clone the response before caching
                        const responseToCache = networkResponse.clone();

                        caches.open(DYNAMIC_CACHE)
                            .then(cache => {
                                cache.put(event.request, responseToCache);
                            })
                            .catch(error => {
                                console.error('Cache put failed:', error);
                            });

                        return networkResponse;
                    })
                    .catch(error => {
                        console.error('Fetch failed:', error);
                        // Return offline page for navigation requests
                        if (event.request.mode === 'navigate') {
                            return caches.match('/offline');
                        }
                        return null;
                    });
            })
    );
});

// Handle errors
self.addEventListener('error', event => {
    console.error('Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', event => {
    console.error('Unhandled promise rejection:', event.reason);
});
