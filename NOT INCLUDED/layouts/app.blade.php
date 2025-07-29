<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FF8C42">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @yield('styles')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item" id="loginLink">
                            <a class="nav-link" href="/login">Login</a>
                        </li>
                        <li class="nav-item" id="registerLink">
                            <a class="nav-link" href="/register">Register</a>
                        </li>
                        <li class="nav-item dropdown d-none" id="userDropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <span id="userFullName"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="/dashboard">Dashboard</a>
                                <a class="dropdown-item" href="#" onclick="logout()">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // API Helper Functions
        const api = {
            baseUrl: '/api',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            
            async request(endpoint, options = {}) {
                const token = localStorage.getItem('token');
                if (token) {
                    this.headers['Authorization'] = `Bearer ${token}`;
                }
                
                try {
                    const response = await fetch(`${this.baseUrl}${endpoint}`, {
                        ...options,
                        headers: this.headers
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'Something went wrong');
                    }
                    
                    return data;
                } catch (error) {
                    throw error;
                }
            },
            
            setToken(token) {
                localStorage.setItem('token', token);
            },
            
            removeToken() {
                localStorage.removeItem('token');
            }
        };

        // Auth Functions
        async function logout() {
            try {
                await api.request('/logout', { method: 'POST' });
                api.removeToken();
                updateAuthUI(false);
                window.location.href = '/login';
            } catch (error) {
                console.error('Logout failed:', error);
            }
        }

        // UI Update Functions
        function updateAuthUI(isAuthenticated, user = null) {
            const loginLink = document.getElementById('loginLink');
            const registerLink = document.getElementById('registerLink');
            const userDropdown = document.getElementById('userDropdown');
            const userFullName = document.getElementById('userFullName');

            if (isAuthenticated && user) {
                loginLink.classList.add('d-none');
                registerLink.classList.add('d-none');
                userDropdown.classList.remove('d-none');
                userFullName.textContent = user.name;
            } else {
                loginLink.classList.remove('d-none');
                registerLink.classList.remove('d-none');
                userDropdown.classList.add('d-none');
            }
        }

        // Check Authentication Status
        async function checkAuth() {
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const user = await api.request('/user');
                    updateAuthUI(true, user);
                } catch (error) {
                    api.removeToken();
                    updateAuthUI(false);
                }
            } else {
                updateAuthUI(false);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', checkAuth);

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/js/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful');
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available
                                    if (confirm('A new version of the app is available. Load it?')) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.error('ServiceWorker registration failed:', error);
                    });
            });

            // Handle offline status changes
            window.addEventListener('online', function() {
                document.body.classList.remove('offline');
            });
            
            window.addEventListener('offline', function() {
                document.body.classList.add('offline');
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
