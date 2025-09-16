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
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        const postedDate = new Date(doc.datetime_forwarded).toLocaleString(undefined, options);

        
        tr.innerHTML = `
            <td data-label='Tracking'><b>
                <a href="#" class="tracking-link">${escapeHtml(doc.doc_tracking)}</a>
            </b></td>
            <td data-label='Description'>
            <b>${escapeHtml(doc.doctype_description)}</b> <br> 
            ${escapeHtml(doc.docs_description)}<br>
            From: ${escapeHtml(doc.origin_section)} : ${escapeHtml(doc.origin_fname)}
            </td>
            <td data-label='From'>
            <b>${escapeHtml(doc.route_fromsection)} </b><br>
            ${escapeHtml(doc.route_from)}
            </td>
            <td data-label='Purpose'>
            ${escapeHtml(doc.route_purpose)} <br>
            ${escapeHtml(doc.fwd_remarks)}
            </td>
            <td data-label='Date'>${escapeHtml(postedDate)}</td>
            <td data-label='Action'>
                <button class="btn btn-sm btn-info view" aria-label="Print Trail">
                    <i class="fas fa-print"></i>
                </button>
                <button class="btn btn-sm btn-primary forward" aria-label="Forward document">
                    <i class="fas fa-share"></i>
                </button>
                <button class="btn btn-sm btn-secondary forward" aria-label="Keep document">
                    <i class="fas fa-save"></i>
                </button>
                <button class="btn btn-sm btn-warning forward" aria-label="Deferred document">
                    <i class="fas fa-clock"></i>
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
    addDocumentClickHandlers();
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
export async function loadDocs(tableBodySelector, url = '/api/documents/deferred?${params.toString()}') {
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
        const docs = await API.get(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        console.log('API Response:', docs);
        
        // Handle both array response and data wrapper response
        let docsData = [];
        let pagination = null;

        if (!docs) {
            docsData = [];
        } else if (Array.isArray(docs)) {
            docsData = docs;
        } else if (docs.data && Array.isArray(docs.data)) {
            docsData = docs.data;
        } else if (docs.data && Array.isArray(docs.data.data)) {
            // Laravel paginator
            docsData = docs.data.data;
            pagination = {
                current_page: docs.data.current_page,
                last_page: docs.data.last_page,
                per_page: docs.data.per_page,
                total: docs.data.total,
                from: docs.data.from,
                to: docs.data.to,
                links: docs.data.links
            };
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
        if (pagination) {
            renderPagination(pagination);
        }
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

function renderPagination(pagination) {
    const info = document.getElementById('paginationInfo');
    const linksContainer = document.getElementById('paginationLinks');

    if (!pagination || !info || !linksContainer) return;

    // Update info text
    info.textContent = `Showing ${pagination.from || 0} to ${pagination.to || 0} of ${pagination.total || 0} entries`;

    // Clear links
    linksContainer.innerHTML = '';

    if (!Array.isArray(pagination.links)) return;

    pagination.links.forEach(link => {
        const li = document.createElement('li');
        li.classList.add('page-item');
        if (link.active) li.classList.add('active');
        if (!link.url) li.classList.add('disabled');

        const a = document.createElement('a');
        a.classList.add('page-link');
        a.href = '#';
        a.innerHTML = link.label;

        if (link.url) {
        a.addEventListener('click', (e) => {
            e.preventDefault();

            // Strip base URL if Laravel returns absolute URLs
            let pageUrl = link.url;
            try {
                const base = window.location.origin;
                if (pageUrl.startsWith(base)) {
                    pageUrl = pageUrl.replace(base, '');
                }
            } catch (err) {
                console.warn("URL normalization failed", err);
            }

            loadDocs('#documentsTableBody', pageUrl);
        });
    }


        li.appendChild(a);
        linksContainer.appendChild(li);
    });
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

    // View document click handler
    document.addEventListener('click', function(e) {
        const viewBtn = e.target.closest('.view');
        if (viewBtn) {
            e.preventDefault();
            const row = viewBtn.closest('tr');
            const trackingNumber = row.querySelector('td:first-child').textContent.trim();
            
            // Redirect to find.blade.php with tracking number as URL parameter
            window.location.href = `/find?tracking=${encodeURIComponent(trackingNumber)}`;
        }
    });
}

// Add these functions to your module
function showDocumentModal(trackingNumber) {
    const modal = new bootstrap.Modal(document.getElementById('documentDetailsModal'));
    
    // Show loading state
    document.getElementById('modalDocumentDetailsContent').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    document.getElementById('modalDocumentTimeline').innerHTML = '';
    
    // Fetch document details
    fetch(`/api/docmain/track/${trackingNumber}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.document) {
            displayModalDocument(data);
        } else {
            throw new Error('Document not found or invalid response format');
        }
    })
    .catch(error => {
        document.getElementById('modalDocumentDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                Error loading document details: ${error.message}
            </div>
        `;
    });

    modal.show();
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    return new Date(dateString).toLocaleString(undefined, options);
}

function getStatusBadgeClass(done) {
    return done == 1 ? 'bg-success' : 'bg-warning';
}

function getActionsBadgeClass(actions) {
    if (!actions) return 'bg-secondary';
    switch(actions.toLowerCase()) {
        case 'urgent':
            return 'bg-danger';
        case 'immediate':
            return 'bg-warning';
        default:
            return 'bg-info';
    }
}

// Update the displayModalDocument function
function displayModalDocument(data) {
    const doc = data.document; // Renamed from document to doc
    
    // Display document details
    const detailsContent = document.getElementById('modalDocumentDetailsContent');
    if (!detailsContent) {
        console.error('modalDocumentDetailsContent element not found');
        return;
    }
    
    detailsContent.innerHTML = `
        <div class="detail-row">
            <div class="detail-label">Document ID:</div>
            <div class="detail-value">${doc.doc_id || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Tracking Number:</div>
            <div class="detail-value"><strong>${doc.doc_tracking || 'N/A'}</strong></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">From:</div>
            <div class="detail-value">${doc.origin_fname || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Office/School:</div>
            <div class="detail-value">${doc.origin_school || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Origin Section:</div>
            <div class="detail-value">${doc.origin_section ? doc.origin_section.section_description : 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Date Posted:</div>
            <div class="detail-value">${formatDateTime(doc.datetime_posted)}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Document Type:</div>
            <div class="detail-value">${doc.doctype ? doc.doctype.doctype_description : 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Description:</div>
            <div class="detail-value">${doc.docs_description || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Actions Needed:</div>
            <div class="detail-value">
                <span class="badge ${getActionsBadgeClass(doc.actions_needed)}">${doc.actions_needed || 'N/A'}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value">
                <span class="badge ${getStatusBadgeClass(doc.done)}">${doc.done == 1 ? 'Completed' : 'In Progress'}</span>
            </div>
        </div>
    `;

    // Display timeline
    const timelineContainer = document.getElementById('modalDocumentTimeline');
    if (!timelineContainer) {
        console.error('modalDocumentTimeline element not found');
        return;
    }

    if (data.routes && data.routes.length > 0) {
        let timelineHTML = '';
        data.routes.forEach((route) => {
            const isActive = !route.route_accomplished;
            const isAccepted = route.datetime_route_accepted !== "-000001-11-30T00:00:00.000000Z";
            const hasActions = route.actions_datetime !== "-000001-11-30T00:00:00.000000Z";
            
            let statusText = 'Forwarded';
            let statusClass = 'forwarded';
            
            if (hasActions) {
                statusText = 'Actions Taken';
                statusClass = 'completed';
            } else if (isAccepted) {
                statusText = 'Received';
                statusClass = 'received';
            } else {
                statusText = 'Pending';
                statusClass = 'pending';
            }
            
            timelineHTML += `
                <div class="timeline-item ${isActive ? 'active' : ''} ${statusClass}">
                    <div class="timeline-date">${formatDateTime(route.datetime_forwarded)}</div>
                    <div class="timeline-title">${statusText}</div>
                    <div class="timeline-details">
                        <strong>From:</strong> ${route.route_from || 'N/A'}<br>
                        <strong>From Section:</strong> ${route.route_fromsection || 'N/A'}<br>
                        <strong>To Section:</strong> ${route.route_tosection || 'N/A'}<br>
                        <strong>Purpose:</strong> ${route.route_purpose || 'N/A'}<br>
                        ${route.fwd_remarks ? `<strong>Forwarding Remarks:</strong> ${route.fwd_remarks}<br>` : ''}
                        ${isAccepted ? `<strong>Received:</strong> ${formatDateTime(route.datetime_route_accepted)}<br>` : ''}
                        ${route.received_by ? `<strong>Received By:</strong> ${route.received_by}<br>` : ''}
                        ${route.accepting_remarks ? `<strong>Receiving Remarks:</strong> ${route.accepting_remarks}<br>` : ''}
                        ${hasActions ? `<strong>Actions Date:</strong> ${formatDateTime(route.actions_datetime)}<br>` : ''}
                        ${route.actions_taken ? `<strong>Actions Taken:</strong> ${route.actions_taken}<br>` : ''}
                        ${route.acted_by ? `<strong>Acted By:</strong> ${route.acted_by}<br>` : ''}
                        ${route.end_remarks ? `<strong>End Remarks:</strong> ${route.end_remarks}` : ''}
                    </div>
                </div>
            `;
        });
        timelineContainer.innerHTML = timelineHTML;
    } else {
        timelineContainer.innerHTML = '<p class="text-center text-muted">No route information available</p>';
    }
}

// Add click event listener in your renderDocs function
function addDocumentClickHandlers() {
    document.querySelectorAll('.tracking-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const trackingNumber = e.target.textContent;
            showDocumentModal(trackingNumber);
        });
    });
}
