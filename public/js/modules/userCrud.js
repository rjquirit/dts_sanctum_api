import * as API from './api.js';
import { showLoading, hideLoading, showError, isOnline } from './utils.js';

// Cache key for offline data
const USERS_CACHE_KEY = 'cached_users';

/**
 * Renders users into the table
 */
function renderUsers(users, tableBodySelector) {
    const tbody = document.querySelector(tableBodySelector);
    tbody.innerHTML = '';
    
    users.forEach(user => {
        const tr = document.createElement('tr');
        tr.dataset.id = user.id;
        tr.innerHTML = `
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>
                <button class="edit btn-secondary" aria-label="Edit user">Edit</button>
                <button class="delete btn-danger" aria-label="Delete user">Delete</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Sanitize HTML content
 */
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Load users from API or cache if offline
 */
export async function loadUsers(tableBodySelector) {
    const tableBody = document.querySelector(tableBodySelector);
    
    try {
        showLoading(tableBody);
        
        // Check for authentication
        const token = localStorage.getItem('auth_token'); // Changed from auth_token to token to match frontend
        console.log('Auth token:', token ? 'Present' : 'Missing');
        
        if (!token) {
            console.error('No authentication token found');
            window.location.href = '/login';
            return;
        }
        
        if (!isOnline()) {
            console.log('Offline mode: loading cached users');
            const cachedUsers = JSON.parse(localStorage.getItem(USERS_CACHE_KEY) || '[]');
            renderUsers(cachedUsers, tableBodySelector);
            showError('You are offline. Showing cached users.');
            return;
        }

        console.log('Fetching users from API...');
        const users = await API.get('/api/users', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        console.log('API Response:', users);
        
        // Handle both array response and data wrapper response
        let userData;
        if (!users) {
            userData = []; // Handle empty response as empty array
        } else if (Array.isArray(users)) {
            userData = users;
        } else if (users.data && Array.isArray(users.data)) {
            userData = users.data;
        } else if (typeof users === 'object' && users !== null) {
            userData = [users]; // Handle single user response
        } else {
            console.warn(`Unexpected response format: ${typeof users}`);
            userData = []; // Fallback to empty array
        }
        
        console.log(`Successfully loaded ${userData.length} users`);
        // Cache users for offline access
        localStorage.setItem(USERS_CACHE_KEY, JSON.stringify(userData));
        renderUsers(userData, tableBodySelector);
    } catch (error) {
        console.error('Error loading users:', {
            message: error.message,
            stack: error.stack,
            response: error.response,
            status: error.response?.status,
            statusText: error.response?.statusText,
            error: error
        });
        
        if (error.response) {
            try {
                const errorData = await error.response.json();
                console.error('API Error Response:', errorData);
            } catch (e) {
                console.error('Could not parse error response');
            }
        }
        
        showError('Failed to load users. Please try again.');
        
        // Show cached data as fallback
        const cachedUsers = JSON.parse(localStorage.getItem(USERS_CACHE_KEY) || '[]');
        if (cachedUsers.length > 0) {
            console.log('Loading cached users as fallback');
            renderUsers(cachedUsers, tableBodySelector);
            showError('Showing cached users due to error.');
        }
    } finally {
        hideLoading(tableBody);
    }
}

export function bindUserActions(formSelector, tableBodySelector) {
    const form = document.querySelector(formSelector);
    const tbody = document.querySelector(tableBodySelector);
    
    // Prevent multiple bindings
    if (tbody.dataset.bound === 'true') {
        return;
    }
    tbody.dataset.bound = 'true';
    
    // Create user
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!isOnline()) {
            showError('Cannot create user while offline.');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        
        try {
            showLoading(submitBtn);
            
            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password')
            };

            // Validate inputs
            if (!data.name || !data.email || !data.password) {
                showError('Please fill in all required fields.');
                return;
            }

            await API.post('/api/users', data);
            await loadUsers(tableBodySelector);
            form.reset();
        } catch (error) {
            console.error('Error creating user:', error);
            showError('Failed to create user. Please try again.');
        } finally {
            hideLoading(submitBtn);
        }
    });

    // Edit user
    tbody.addEventListener('click', (e) => {
        if (!e.target.matches('.edit')) return;
        
        const tr = e.target.closest('tr');
        const id = tr.dataset.id;
        
        // Populate form
        form.querySelector('#userId').value = id;
        form.querySelector('#name').value = tr.cells[0].textContent;
        form.querySelector('#email').value = tr.cells[1].textContent;
        
        // Focus first input
        form.querySelector('#name').focus();
    });

    // Update user
    const updateBtn = document.querySelector('#updateBtn');
    updateBtn?.addEventListener('click', async () => {
        if (!isOnline()) {
            showError('Cannot update user while offline.');
            return;
        }

        try {
            showLoading(updateBtn);
            
            const id = document.querySelector('#userId').value;
            const data = {
                name: document.querySelector('#name').value,
                email: document.querySelector('#email').value
            };

            // Validate inputs
            if (!data.name || !data.email) {
                showError('Please fill in all required fields.');
                return;
            }

            const updatedUser = await API.put(`/api/users/${id}`, data);
            // Find and update the specific row instead of reloading all users
            const tr = document.querySelector(`tr[data-id="${id}"]`);
            if (tr) {
                tr.cells[0].textContent = updatedUser.name;
                tr.cells[1].textContent = updatedUser.email;
            }
            form.reset();
            showError('User updated successfully', 'success');
        } catch (error) {
            console.error('Error updating user:', error);
            showError('Failed to update user. Please try again.');
        } finally {
            hideLoading(updateBtn);
        }
    });

    // Delete user
    tbody.addEventListener('click', async (e) => {
        if (!e.target.matches('.delete')) return;
        
        if (!isOnline()) {
            showError('Cannot delete user while offline.');
            return;
        }

        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }

        const tr = e.target.closest('tr');
        const id = tr.dataset.id;

        try {
            showLoading(e.target);
            await API.del(`/api/users/${id}`);
            // Remove the row from the table directly
            const tr = e.target.closest('tr');
            tr.remove();
            showError('User deleted successfully', 'success');
        } catch (error) {
            console.error('Error deleting user:', error);
            if (error.response && error.response.status !== 404) {
                showError('Failed to delete user. Please try again.');
            } else {
                // If it's a 404, remove the row anyway as the user doesn't exist
                const tr = e.target.closest('tr');
                tr.remove();
            }
        } finally {
            hideLoading(e.target);
        }
    });
}
