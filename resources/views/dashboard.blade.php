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
    <main>
        <div class="header-bar">
            <h2>Welcome, <span id="userName">User</span></h2>
            <button id="logoutBtn" class="btn-danger">Logout</button>
        </div>

        <form id="userForm" class="card p-3">
            <input type="hidden" id="userId">
            <input id="name" name="name" placeholder="Name" required class="form-control">
            <input id="email" name="email" placeholder="Email" required class="form-control">
            <div class="d-flex">
                <button type="submit" class="btn btn-primary me-2">Create</button>
                <button type="button" id="updateBtn" class="btn btn-primary">Update</button>
            </div>
        </form>

        <table id="usersTable">
            <thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </main>

    <script type="module" src="{{ asset('js/dashboard.js') }}"></script>
@endsection
