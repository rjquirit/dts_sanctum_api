import { get, post, put, del } from './modules/api.js';

/**
 * Document Forward Management
 * Handles document listing, forwarding, and display functionality
 */

class DocumentForward {
    constructor() {
        this.apiUrl = '/api/documents';
        this.currentPage = 1;
        this.perPage = 10;
        this.searchTerm = '';
        this.filters = {
            doc_type_id: '',
            toggle: false // false = office, true = personal
        };
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        this.bindEvents();
        this.loadDocuments();
    }

    bindEvents() {
        // Search button click
        document.getElementById('searchBtn')?.addEventListener('click', () => this.handleSearch());
        
        // Search input enter key
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.handleSearch();
        });
        
        // Toggle switch change
        document.getElementById('toggleSwitch')?.addEventListener('change', (e) => {
            this.filters.toggle = e.target.checked;
            this.currentPage = 1;
            this.loadDocuments();
        });
        
        // Document type filter change
        document.getElementById('docTypeFilter')?.addEventListener('change', (e) => {
            this.filters.doc_type_id = e.target.value;
            this.currentPage = 1;
            this.loadDocuments();
        });
    }

    async loadDocuments() {
        try {
            this.showLoading(true);
            
            // Build query parameters
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: this.searchTerm,
                ...this.filters
            });
            
            // Make API request
            const response = await get(`${this.apiUrl}?${params}`);
            
            if (response.success) {
                this.renderDocuments(response.data.data);
                this.updatePagination(response.data);
                this.toggleNoDataMessage(response.data.data.length === 0);
            } else {
                throw new Error(response.message || 'Failed to load documents');
            }
        } catch (error) {
            console.error('Error loading documents:', error);
            this.showError('Failed to load documents. Please try again.');
        } finally {
            this.showLoading(false);
        }
    }

    renderDocuments(documents) {
        const tbody = document.getElementById('documentsTableBody');
        if (!tbody) return;
        
        if (!documents || documents.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No documents found</td></tr>';
            return;
        }
        
        tbody.innerHTML = documents.map(doc => `
            <tr>
                <td>${doc.doc_tracking || 'N/A'}</td>
                <td>${doc.doctype?.doctype_description || 'N/A'}</td>
                <td class="text-truncate" style="max-width: 200px;" title="${doc.docs_description || ''}">
                    ${doc.docs_description || 'N/A'}
                </td>
                <td>${doc.origin_fname || doc.origin_school || 'N/A'}</td>
                <td>
                    <span class="badge ${doc.done ? 'bg-success' : 'bg-warning'}">
                        ${doc.done ? 'Completed' : 'Pending'}
                    </span>
                </td>
                <td>${this.formatDate(doc.datetime_posted)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary view-document" 
                            data-id="${doc.doc_id}" 
                            data-bs-toggle="modal" 
                            data-bs-target="#viewDocumentModal">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `).join('');
        
        // Attach event listeners to view buttons
        document.querySelectorAll('.view-document').forEach(button => {
            button.addEventListener('click', (e) => this.showDocumentDetails(e.target.dataset.id));
        });
    }

    updatePagination(paginationData) {
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationLinks = document.getElementById('paginationLinks');
        
        if (!paginationInfo || !paginationLinks) return;
        
        // Update pagination info
        paginationInfo.textContent = `Showing ${paginationData.from || 0} to ${paginationData.to || 0} of ${paginationData.total || 0} entries`;
        
        // Generate pagination links
        let links = '';
        const totalPages = paginationData.last_page;
        const currentPage = paginationData.current_page;
        
        // Previous button
        links += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" ${currentPage === 1 ? 'tabindex="-1" aria-disabled="true"' : ''}>
                    &laquo; Previous
                </a>
            </li>
        `;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                links += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                links += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Next button
        links += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'tabindex="-1" aria-disabled="true"' : ''}>
                    Next &raquo;
                </a>
            </li>
        `;
        
        paginationLinks.innerHTML = links;
        
        // Attach event listeners to pagination links
        document.querySelectorAll('.page-link[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.currentPage = parseInt(e.target.dataset.page);
                this.loadDocuments();
            });
        });
    }

    async showDocumentDetails(docId) {
        try {
            const response = await get(`${this.apiUrl}/${docId}`);
            
            if (response.success) {
                const doc = response.data;
                const modalBody = document.getElementById('documentDetails');
                
                if (modalBody) {
                    modalBody.innerHTML = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Tracking #:</strong> ${doc.doc_tracking || 'N/A'}</p>
                                <p><strong>Type:</strong> ${doc.doctype?.doctype_description || 'N/A'}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge ${doc.done ? 'bg-success' : 'bg-warning'}">
                                        ${doc.done ? 'Completed' : 'Pending'}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Date Posted:</strong> ${this.formatDate(doc.datetime_posted)}</p>
                                <p><strong>Origin:</strong> ${doc.origin_fname || doc.origin_school || 'N/A'}</p>
                                ${doc.origin_office ? `<p><strong>Office:</strong> ${doc.origin_office.school_name}</p>` : ''}
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Description</h6>
                            <p>${doc.docs_description || 'No description available'}</p>
                        </div>
                        ${doc.actions_needed ? `
                            <div class="mb-3">
                                <h6>Actions Needed</h6>
                                <p>${doc.actions_needed}</p>
                            </div>
                        ` : ''}
                    `;
                }
            } else {
                throw new Error(response.message || 'Failed to load document details');
            }
        } catch (error) {
            console.error('Error loading document details:', error);
            this.showError('Failed to load document details. Please try again.');
        }
    }

    handleSearch() {
        this.searchTerm = document.getElementById('searchInput')?.value.trim() || '';
        this.currentPage = 1;
        this.loadDocuments();
    }

    showLoading(show) {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const table = document.querySelector('.table-responsive');
        
        if (loadingSpinner && table) {
            loadingSpinner.style.display = show ? 'block' : 'none';
            table.style.opacity = show ? '0.5' : '1';
        }
    }

    toggleNoDataMessage(show) {
        const noDataMessage = document.getElementById('noDataMessage');
        const table = document.querySelector('.table-responsive');
        
        if (noDataMessage && table) {
            noDataMessage.style.display = show ? 'block' : 'none';
            table.style.display = show ? 'none' : 'block';
        }
    }

    showError(message) {
        // You can implement a more sophisticated error display
        console.error(message);
        alert(message);
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('Error formatting date:', error);
            return 'Invalid date';
        }
    }
}

// Initialize the DocumentForward class when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.docForward = new DocumentForward();
});