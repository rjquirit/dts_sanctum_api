import * as API from './api.js';
import { showLoading, hideLoading, showError, isOnline } from './utils.js';

// Cache key for offline data
const DOCS_CACHE_KEY = 'cached_docs';

/**
 * Renders Docss into the table
 */
function renderDocs(docs, tableBodySelector) {
    const tbody = document.querySelector(tableBodySelector);
    tbody.innerHTML = '';
    
    if (!Array.isArray(docs)) {
        console.error('Received invalid docs data:', docs);
        return;
    }

    console.log('Rendering docs:', docs); // Debug log
    
    docs.forEach(doc => {
        if (!doc || typeof doc !== 'object') {
            console.warn('Invalid doc object:', doc);
            return;
        }

        const tr = document.createElement('tr');
        tr.dataset.id = doc.doc_id || '';

        // Format date
        const postedDate = new Date(doc.datetime_posted).toLocaleDateString();
        
        tr.innerHTML = `
            <td>${escapeHtml(doc.doc_tracking)}</td>
            <td>${escapeHtml(doc.doctype?.doctype_description || '')}</td>
            <td>${escapeHtml(doc.docs_description)}</td>
            <td>${escapeHtml(doc.origin_office?.school_name || '')}</td>
            <td>${escapeHtml(doc.actions_needed)}</td>
            <td>${escapeHtml(postedDate)}</td>
            <td>
                <button class="btn btn-sm btn-info view" aria-label="View document">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-primary forward" aria-label="Forward document">
                    <i class="fas fa-share"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Show/hide no data message
    const noDataMessage = document.getElementById('noDataMessage');
    if (noDataMessage) {
        noDataMessage.style.display = docs.length === 0 ? 'block' : 'none';
    }

    console.log(`Rendered ${docs.length} documents`); // Debug log
}

/**
 * Sanitize HTML content
 */
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) {
        return '';
    }
    
    // Convert to string in case we receive a number or other type
    const str = String(unsafe);
    
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Load Docs from API or cache if offline
 */
export async function loadDocs(tableBodySelector) {
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
            console.log('Offline mode: loading cached docs');
            const cachedDocs = JSON.parse(localStorage.getItem(DOCS_CACHE_KEY) || '[]');
            renderDocs(cachedDocs, tableBodySelector); // ✅ Fixed function name
            showError('You are offline. Showing cached docs.');
            return;
        }

        console.log('Fetching Docs from API...');
        const docs = await API.get('/api/documents', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        console.log('API Response:', docs);
        
        // Handle both array response and data wrapper response
        let docsData;
        if (!docs) {
            docsData = []; // Handle empty response as empty array
        } else if (Array.isArray(docs)) {
            docsData = docs;
        } else if (docs.data && Array.isArray(docs.data)) {
            // Already an array
            docsData = docs.data;
        } else if (docs.data && Array.isArray(docs.data.data)) {
            // Handle Laravel paginator (docs.data.data)
            docsData = docs.data.data;
        } else if (typeof docs === 'object' && docs !== null) {
            docsData = [docs];
        } else {
            console.warn(`Unexpected response format: ${typeof docs}`);
            docsData = [];
        }
        
        console.log(`Successfully loaded ${docsData.length} docs`);
        // Cache Docss for offline access
        localStorage.setItem(DOCS_CACHE_KEY, JSON.stringify(docsData));
        renderDocs(docsData, tableBodySelector);
    } catch (error) {
        console.error('Error loading docs:', {
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
        
        showError('Failed to load docs from Cache. Please try again.');
        
        // Show cached data as fallback
        const cachedDocs = JSON.parse(localStorage.getItem(DOCS_CACHE_KEY) || '[]');
        if (cachedDocs.length > 0) {
            console.log('Loading cached docs as fallback');
            renderDocs(cachedDocs, tableBodySelector);
            showError('Showing cached docs due to error.');
        }
    } finally {
        hideLoading(tableBody);
    }
}

export function bindDocsActions(formSelector, tableBodySelector) {
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
            showError('Cannot create docs while offline.');
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
