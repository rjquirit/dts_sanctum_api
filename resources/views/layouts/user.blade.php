<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2c3e50">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>
    
    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
</head>
<body>
    <!-- Main App Container -->
    <div id="appContainer" class="app-container">
        <!-- Top Navigation Bar -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <h4 class="mb-0">Secure PWA App</h4>
                <div>
                    <span id="userNameDisplay" class="me-3">{{ Auth::user()->name ?? 'User' }}</span>
                    <button class="btn btn-outline-light btn-sm" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </nav>

        <!-- Sidebar Navigation (Desktop/Landscape) -->
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-nav">
                <li><a href="#" class="nav-link active" data-tab="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a></li>
                <li><a href="#" class="nav-link" data-tab="users">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a></li>
                <li><a href="#" class="nav-link" data-tab="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a></li>
                <li><a href="#" class="nav-link" data-tab="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="container-fluid">
                    <h2 class="mb-4">Dashboard</h2>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>150</h4>
                                            <p class="mb-0">Total Users</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>95%</h4>
                                            <p class="mb-0">Uptime</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @yield('content')
                </div>
            </div>

            <!-- Users Tab -->
            <div id="users" class="tab-content">
                <div class="container-fluid">
                    <h2 class="mb-4">User Management</h2>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">All Users</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>John Doe</td>
                                            <td>john@example.com</td>
                                            <td>2024-01-01</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Edit</button>
                                                <button class="btn btn-sm btn-danger">Delete</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Tab -->
            <div id="profile" class="tab-content">
                <div class="container-fluid">
                    <h2 class="mb-4">Profile</h2>
                    <div class="card">
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" value="{{ Auth::user()->name ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" value="{{ Auth::user()->email ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div id="settings" class="tab-content">
                <div class="container-fluid">
                    <h2 class="mb-4">Settings</h2>
                    <div class="card">
                        <div class="card-body">
                            <h5>Application Settings</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="darkMode">
                                <label class="form-check-label" for="darkMode">
                                    Dark Mode
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="notifications" checked>
                                <label class="form-check-label" for="notifications">
                                    Push Notifications
                                </label>
                            </div>
                            <button class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bottom Navigation (Mobile/Portrait) -->
        <nav class="bottom-nav" id="bottomNav">
            <div class="nav-item active" data-tab="dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item" data-tab="users">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </div>
            <div class="nav-item" data-tab="profile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </div>
            <div class="nav-item" data-tab="settings">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
        </nav>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching functionality
        function switchTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link, .nav-item').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked nav items
            document.querySelectorAll(`[data-tab="${tabId}"]`).forEach(link => {
                link.classList.add('active');
            });
        }

        // Event listeners for navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar navigation
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('data-tab');
                    switchTab(tabId);
                });
            });

            // Bottom navigation
            document.querySelectorAll('.bottom-nav .nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    switchTab(tabId);
                });
            });
        });

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                // Add your logout logic here
                window.location.href = '/logout';
            }
        }
    </script>
    <script type="module" src="{{ asset('js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>