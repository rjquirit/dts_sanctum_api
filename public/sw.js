const STATIC_CACHE = 'static-v1';
const DYNAMIC_CACHE = 'dynamic-v1';
const API_CACHE = 'api-v1';

const STATIC_FILES = [
  '/',
  '/login',
  '/register',
  '/offline',
  '/css/app.css',
  '/css/materialize.min.css',
  '/css/theme.css',
  'https://fonts.googleapis.com/icon?family=Material+Icons',
  '/js/materialize.min.js',
  '/js/main.js',
  '/js/modules/auth.js',
  '/js/modules/utils.js',
  '/js/modules/userCrud.js',
  '/js/modules/materialize-utils.js'
];

// Cache static assets on install
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => cache.addAll(STATIC_FILES))
      .then(() => self.skipWaiting())
  );
});

// Clean up old caches on activate
self.addEventListener('activate', e => {
  const cacheWhitelist = [STATIC_CACHE, DYNAMIC_CACHE, API_CACHE];
  e.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (!cacheWhitelist.includes(cacheName)) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Handle fetch events with different strategies based on request type
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);
  
  // API requests - Network first, then cache
  if (url.pathname.startsWith('/api/')) {
    e.respondWith(
      fetch(e.request)
        .then(response => {
          const clone = response.clone();
          caches.open(API_CACHE)
            .then(cache => cache.put(e.request, clone));
          return response;
        })
        .catch(() => caches.match(e.request))
    );
    return;
  }

  // External resources (CDN) - Cache first, network as fallback
  if (!url.origin.includes(self.location.origin)) {
    e.respondWith(
      caches.match(e.request)
        .then(cacheResponse => {
          if (cacheResponse) {
            // Return cached version
            return cacheResponse;
          }
          // Try network and cache the response
          return fetch(e.request)
            .then(networkResponse => {
              const clone = networkResponse.clone();
              caches.open(DYNAMIC_CACHE)
                .then(cache => cache.put(e.request, clone));
              return networkResponse;
            })
            .catch(error => {
              console.error('Failed to fetch external resource:', error);
              // Return a fallback if available
              if (e.request.url.includes('Material+Icons')) {
                return new Response('', { 
                  status: 200, 
                  headers: new Headers({ 'Content-Type': 'text/css' })
                });
              }
              throw error;
            });
        })
    );
    return;
  }

  // Static assets - Cache first, then network
  e.respondWith(
    caches.match(e.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(e.request)
          .then(networkResponse => {
            const clone = networkResponse.clone();
            caches.open(DYNAMIC_CACHE)
              .then(cache => cache.put(e.request, clone));
            return networkResponse;
          })
          .catch(() => {
            // If both cache and network fail, show offline page
            if (e.request.headers.get('accept').includes('text/html')) {
              return caches.match('/offline');
            }
          });
      })
  );
});
