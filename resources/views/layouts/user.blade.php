<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FF8C42">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DTS">
    <meta name="msapplication-TileColor" content="#FF8C42">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <title>{{ config('app.name', 'DTS') }} - @yield('title')</title>
    
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
      <style>
    .fab {
      position: fixed;
      bottom: 70px;
      right: 10px;
      border-radius: 50%;
      z-index: 1030; /* higher than navbar */
    }


  </style>
</head>
<body>
    <!-- Main App Container -->
    <div id="appContainer" class="app-container">
        <!-- Top Navigation Bar -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <h4 class="mb-0">DTS 3.0</h4>
                {{-- Search --}}
                <a href="{{ route('find') }}" 
                class="nav-link {{ request()->routeIs('find') ? 'active' : '' }} me-3 d-flex align-items-center">
                    <i class="fas fa-search me-1"></i>
                    <span>Search</span>
                </a>
                <div>
                    {{-- Avatar --}}
                    <span id="userAvatarDisplay" class="me-3">
                        <a href="{{ route('profile') }}" style="display: inline-block; padding: 6px 12px; background-color: #d35400; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                            <img src="{{ Auth::user()->avatar ?? asset('images/default-avatar.png') }}" 
                                alt="User Avatar" 
                                style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                        </a>
                    </span>
                    {{-- Logout --}}
                    <button id="logoutBtn" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Sidebar Navigation (Desktop/Landscape) -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-nav">
                <li><a href="{{ route('mydocs') }}" class="nav-link {{ request()->routeIs('mydocs') ? 'active' : '' }}">
                    <i class="fas fa-folder"></i>
                    <span>My Document</span>
                </a></li>
                <li><a href="{{ route('incoming') }}" class="nav-link {{ request()->routeIs('incoming') ? 'active' : '' }}">
                    <i class="fas fa-inbox"></i>
                    <span>Incoming</span>
                </a></li>
                <li><a href="{{ route('pending') }}" class="nav-link {{ request()->routeIs('pending') ? 'active' : '' }}">
                    <i class="fas fa-file"></i>
                    <span>Pending</span>
                </a></li>
                <li><a href="{{ route('forward') }}" class="nav-link {{ request()->routeIs('forward') ? 'active' : '' }}">
                    <i class="fas fa-share"></i>
                    <span>Forward</span>
                </a></li>
                <li><a href="{{ route('deferred') }}" class="nav-link {{ request()->routeIs('deferred') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Deferred</span>
                </a></li>
                <li><a href="{{ route('keep') }}" class="nav-link {{ request()->routeIs('keep') ? 'active' : '' }}">
                    <i class="fas fa-archive"></i>
                    <span>Keep</span>
                </a></li>
                <li><a href="{{ route('release') }}" class="nav-link {{ request()->routeIs('release') ? 'active' : '' }}">
                    <i class="fas fa-plane"></i>
                    <span>Released</span>
                </a></li>
                <li><a href="https://dts.deped10.com" class="nav-link" target="_blank" rel="noopener">
                    <i class="fas fa-arrow-left"></i>
                    <span>DTS OLD</span>
                </a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            @yield('content')
            <div class="mobile-bottom-spacer"></div>
            <a href="{{ route('add') }}" class="btn btn-primary d-flex align-items-center justify-content-center fab shadow-lg">+</a>
        </main>

        <!-- Bottom Navigation (Mobile/Portrait) -->
        <nav class="bottom-nav" id="bottomNav">
            <a href="{{ route('mydocs') }}" class="nav-item {{ request()->routeIs('mydocs') ? 'active' : '' }}">
                <i class="fas fa-folder"></i>
                <span>MyDocs</span>
            </a>
            <a href="{{ route('incoming') }}" class="nav-item {{ request()->routeIs('incoming') ? 'active' : '' }}">
                <i class="fas fa-inbox"></i>
                <span>Inbox</span>
            </a>
            <a href="{{ route('pending') }}" class="nav-item {{ request()->routeIs('pending') ? 'active' : '' }}">
                <i class="fas fa-file"></i>
                <span>Held</span>
            </a>
            <a href="{{ route('forward') }}" class="nav-item {{ request()->routeIs('forward') ? 'active' : '' }}">
                <i class="fas fa-share"></i>
                <span>Send</span>
            </a>
            <a href="{{ route('deferred') }}" class="nav-item {{ request()->routeIs('deferred') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span>Defer</span>
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
        // function logout() {
        //     if (window.logout && typeof window.logout === 'function') {
        //         window.logout();
        //     } else {
        //         if (confirm('Are you sure you want to logout?')) {
        //             window.location.href = '/logout';
        //         }
        //     }
        // }

        // Add logout event listener
        document.getElementById('logoutBtn').addEventListener('click', logout);
        function adjustMobileSpacing() {
            const isMobile = window.innerWidth <= 768 && window.innerHeight > window.innerWidth;
            const mainContent = document.querySelector('.main-content');
            const bottomNav = document.querySelector('.bottom-nav');
            
            if (isMobile && bottomNav && mainContent) {
                const bottomNavHeight = bottomNav.offsetHeight;
                const buffer = 30; // Extra buffer space
                
                // Set bottom padding
                mainContent.style.paddingBottom = `${bottomNavHeight + buffer}px`;
                
                // Ensure tables have proper spacing
                const tables = document.querySelectorAll('.table-responsive');
                tables.forEach(table => {
                    table.style.marginBottom = '30px';
                });
                
                // Ensure pagination has proper spacing
                const pagination = document.querySelector('.pagination');
                if (pagination) {
                    pagination.style.marginBottom = '30px';
                }
            }
        }

        // Run on load and resize
        document.addEventListener('DOMContentLoaded', adjustMobileSpacing);
        window.addEventListener('resize', adjustMobileSpacing);
        window.addEventListener('orientationchange', () => {
            setTimeout(adjustMobileSpacing, 100); // Small delay for orientation change
        });
    </script>
    @stack('scripts')
</body>
</html>