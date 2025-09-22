const CACHE_VERSION = 'v1.0';
const STATIC_CACHE = `static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `dynamic-${CACHE_VERSION}`;
const API_CACHE = `api-${CACHE_VERSION}`;

// Assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/offline',
    '/css/style.css',
    '/js/main.js',
    '/manifest.json',
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

// Modified fetch event handler
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // Don't cache browser-sync requests
    if (event.request.url.includes('browser-sync')) {
        return;
    }

    // Handle API requests differently
    if (url.pathname.startsWith('/api/')) {
        // Don't cache POST requests
        if (event.request.method === 'POST') {
            event.respondWith(
                fetch(event.request)
                    .catch(error => {
                        console.error('POST request failed:', error);
                        return new Response(JSON.stringify({
                            success: false,
                            message: 'Failed to submit. Please check your connection.'
                        }), {
                            headers: { 'Content-Type': 'application/json' }
                        });
                    })
            );
            return;
        }

        // Cache GET API requests
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    if (response && response.ok) {
                        const clone = response.clone();
                        caches.open(API_CACHE)
                            .then(cache => {
                                if (event.request.method === 'GET') {
                                    cache.put(event.request, clone);
                                }
                            });
                    }
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Handle regular GET requests
    if (event.request.method === 'GET') {
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    if (response) {
                        return response;
                    }

                    return fetch(event.request)
                        .then(networkResponse => {
                            if (!networkResponse || networkResponse.status !== 200) {
                                return networkResponse;
                            }

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
                            if (event.request.mode === 'navigate') {
                                return caches.match('/offline');
                            }
                            return null;
                        });
                })
        );
    } else {
        // For non-GET requests, just fetch without caching
        event.respondWith(fetch(event.request));
    }
});

// Error handlers
self.addEventListener('error', event => {
    console.error('Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', event => {
    console.error('Unhandled promise rejection:', event.reason);
});
