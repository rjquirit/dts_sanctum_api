@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
.dashboard-stats {
    transition: all 0.3s ease;
}
.dashboard-stats:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
@endsection

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4">Welcome, <span id="welcomeName"></span>!</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card dashboard-stats bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="card-text" id="totalUsers">-</h2>
                </div>
            </div>
        </div>
        <!-- Add more stat cards as needed -->
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Users List</h5>
                    <button class="btn btn-primary btn-sm" onclick="refreshUsersList()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let deleteModal;
let userToDelete;

document.addEventListener('DOMContentLoaded', function() {
    deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
    loadDashboardData();
});

async function loadDashboardData() {
    try {
        const user = await api.request('/user');
        document.getElementById('welcomeName').textContent = user.name;
        await refreshUsersList();
    } catch (error) {
        console.error('Error loading dashboard:', error);
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
    }
}

async function refreshUsersList() {
    try {
        const users = await api.request('/users');
        document.getElementById('totalUsers').textContent = users.length;
        
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = users.map(user => `
            <tr>
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="showDeleteModal(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error fetching users:', error);
    }
}

function showDeleteModal(userId) {
    userToDelete = userId;
    deleteModal.show();
}

async function confirmDelete() {
    try {
        await api.request(`/users/${userToDelete}`, {
            method: 'DELETE'
        });
        deleteModal.hide();
        await refreshUsersList();
    } catch (error) {
        console.error('Error deleting user:', error);
        alert('Failed to delete user: ' + error.message);
    }
}
</script>
@endsection
