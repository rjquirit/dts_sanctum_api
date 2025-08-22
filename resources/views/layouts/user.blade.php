<!-- Main App -->
    <div id="appContainer" class="app-container hidden">
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <h4 class="mb-0">Secure PWA App</h4>
                <div>
                    <span id="userNameDisplay" class="me-3"></span>
                    <button class="btn btn-outline-light btn-sm" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </nav>

        <div class="content-wrapper">
            <!-- Sidebar Navigation (Desktop) -->
            <aside class="sidebar" id="sidebar">
                <ul class="sidebar-nav">
                    <li><a href="#" class="nav-link active" data-tab="dashboard">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a></li>
                    <li><a href="#" class="nav-link" data-tab="users">
                        <i class="fas fa-users me-2"></i>User Management
                    </a></li>
                    <li><a href="#" class="nav-link" data-tab="profile">
                        <i class="fas fa-user me-2"></i>Profile
                    </a></li>
                    <li><a href="#" class="nav-link" data-tab="settings">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Dashboard Tab -->
                <div id="dashboard" class="tab-content active">
                    <h2>Dashboard</h2>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Total Users</h5>
                                    <h3 id="totalUsers">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Active Users</h5>
                                    <h3 id="activeUsers">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>New Users</h5>
                                    <h3 id="newUsers">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>Online</h5>
                                    <h3 id="onlineUsers">1</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Management Tab -->
                <div id="users" class="tab-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>User Management</h2>
                        <button class="btn btn-primary" onclick="showAddUserModal()">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                    </div>
                    <div id="usersAlert"></div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="spinner"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Profile Tab -->
                <div id="profile" class="tab-content">
                    <h2>My Profile</h2>
                    <div id="profileAlert"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <form id="profileForm">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" class="form-control" id="profileName">
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" id="profileEmail" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword">
                                        </div>
                                        <div class="form-group">
                                            <label>New Password</label>
                                            <input type="password" class="form-control" id="newPassword">
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirmNewPassword">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div id="settings" class="tab-content">
                    <h2>Settings</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5>App Preferences</h5>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="darkMode">
                                        <label class="form-check-label" for="darkMode">
                                            Dark Mode
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="notifications" checked>
                                        <label class="form-check-label" for="notifications">
                                            Push Notifications
                                        </label>
                                    </div>
                                    <button class="btn btn-primary" onclick="clearCache()">Clear Cache</button>
                                    <button class="btn btn-success ms-2" id="installBtn" onclick="installApp()" style="display: none;">Install App</button>
                                    <button class="btn btn-info ms-2" onclick="requestNotificationPermission()">Enable Notifications</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Bottom Navigation (Mobile) -->
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