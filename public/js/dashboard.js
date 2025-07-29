import { loadUsers, bindUserActions } from './modules/userCrud.js';
import { logout } from './modules/logout.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize user management
    loadUsers('#usersTable tbody');
    bindUserActions('#userForm', '#usersTable tbody');

    // Set user name
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const userNameElement = document.getElementById('userName');
    if (userNameElement && user.name) {
        userNameElement.textContent = user.name;
    }

    // Bind logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
});
