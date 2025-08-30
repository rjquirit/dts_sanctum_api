<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2c3e50">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DTS API">
    <meta name="msapplication-TileColor" content="#2c3e50">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <title>{{ config('app.name', 'ROX') }} - @yield('title')</title>
    
    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <!-- PWA Manifest and Icons -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-16.png">
    
    @yield('styles')
</head>
<body>
    <!-- Main App Container -->
    <div id="appContainer" class="app-container">
        <!-- Top Navigation Bar -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <h4 class="mb-0">DTS 3.0</h4>
                <div>
                    <span id="userNameDisplay" class="me-3"><a href="{{ route('profile') }}" style="display: inline-block; padding: 6px 12px; background-color: #d35400; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                         {{ explode(' ', trim(Auth::user()->name))[0] ?? 'User' }}
                        </a></span>
                    <button id="logoutBtn" class="btn btn-outline-light btn-sm" onclick="confirm('Are you sure you want to logout?');">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Sidebar Navigation (Desktop/Landscape) -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-nav">
                <li><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-inbox"></i>
                    <span>Incoming</span>
                </a></li>
                <li><a href="{{ route('add') }}" class="nav-link {{ request()->routeIs('add') ? 'active' : '' }}">
                    <i class="fas fa-plus"></i>
                    <span>New Document</span>
                </a></li>
                <li><a href="{{ route('find') }}" class="nav-link {{ request()->routeIs('find') ? 'active' : '' }}">
                    <i class="fas fa-search"></i>
                    <span>Search</span>
                </a></li>
                <li><a href="{{ route('forward') }}" class="nav-link {{ request()->routeIs('forward') ? 'active' : '' }}">
                    <i class="fas fa-share"></i>
                    <span>Forward</span>
                </a></li>
                <li><a href="{{ route('archive') }}" class="nav-link {{ request()->routeIs('archive') ? 'active' : '' }}">
                    <i class="fas fa-archive"></i>
                    <span>Keep</span>
                </a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            @yield('content')
        </main>

        <!-- Bottom Navigation (Mobile/Portrait) -->
        <nav class="bottom-nav" id="bottomNav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-inbox"></i>
                <span>Inbox</span>
            </a>
            <a href="{{ route('add') }}" class="nav-item {{ request()->routeIs('add') ? 'active' : '' }}">
                <i class="fas fa-plus"></i>
                <span>New</span>
            </a>
            <a href="{{ route('find') }}" class="nav-item {{ request()->routeIs('find') ? 'active' : '' }}">
                <i class="fas fa-search"></i>
                <span>Find</span>
            </a>
            <a href="{{ route('forward') }}" class="nav-item {{ request()->routeIs('forward') ? 'active' : '' }}">
                <i class="fas fa-share"></i>
                <span>Send</span>
            </a>
            <a href="{{ route('archive') }}" class="nav-item {{ request()->routeIs('archive') ? 'active' : '' }}">
                <i class="fas fa-archive"></i>
                <span>Keep</span>
            </a>
        </nav>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="{{ asset('js/main.js') }}" defer></script>
    <script>
        // Enhanced navigation with AJAX support
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for AJAX navigation (optional enhancement)
            const navLinks = document.querySelectorAll('.nav-link, .nav-item');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Optional: Add loading state
                    this.classList.add('loading');
                    
                    // Remove loading state after navigation
                    setTimeout(() => {
                        this.classList.remove('loading');
                    }, 300);
                });
            });

            // Initialize main.js features if available
            if (window.AppNavigation) {
                window.userNavigation = new AppNavigation();
            } else {
                setTimeout(() => {
                    if (window.AppNavigation) {
                        window.userNavigation = new AppNavigation();
                    }
                }, 100);
            }
        });

        // Logout function
        function logout() {
            if (window.logout && typeof window.logout === 'function') {
                window.logout();
            } else {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = '/logout';
                }
            }
        }

        // Add logout event listener
        document.getElementById('logoutBtn').addEventListener('click', logout);
    </script>
    @stack('scripts')
</body>
</html>