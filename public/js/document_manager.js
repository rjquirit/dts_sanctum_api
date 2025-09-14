
import { loadDocs, bindDocsActions } from './modules/docsCrud.js';
import { AppNavigation } from './main.js';

class DocumentManager {
    constructor() {
        this.currentType = 'incoming';
        this.currentPage = 1;
        this.searchTerm = '';
        this.sortBy = 'datetime_route_accepted';
        this.sortOrder = 'desc';
        this.toggle = false; // false = office, true = personal
    }

    /**
     * Initialize the document management system
     */
    init() {
        this.bindEventListeners();
        this.initializeFilters();
        this.loadCurrentView();
    }

    /**
     * Bind all event listeners
     */
    bindEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.searchTerm = e.target.value;
                this.currentPage = 1;
                this.loadCurrentView();
            }, 500));
        }

        // Sort functionality
        const sortSelect = document.getElementById('sortBy');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortBy = e.target.value;
                this.currentPage = 1;
                this.loadCurrentView();
            });
        }

        const sortOrderSelect = document.getElementById('sortOrder');
        if (sortOrderSelect) {
            sortOrderSelect.addEventListener('change', (e) => {
                this.sortOrder = e.target.value;
                this.currentPage = 1;
                this.loadCurrentView();
            });
        }

        // Toggle between personal and office documents
        const toggleSwitch = document.getElementById('togglePersonal');
        if (toggleSwitch) {
            toggleSwitch.addEventListener('change', (e) => {
                this.toggle = e.target.checked;
                this.currentPage = 1;
                this.loadCurrentView();
            });
        }

        // Navigation tab changes
        document.addEventListener('tabChanged', (e) => {
            this.currentType = e.detail.tabId;
            this.currentPage = 1;
            this.loadCurrentView();
        });

        // Bulk actions
        this.bindBulkActions();

        // Refresh button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadCurrentView(true);
            });
        }
    }

    /**
     * Initialize filters and controls
     */
    initializeFilters() {
        // Set up filter dropdowns, date pickers, etc.
        const perPageSelect = document.getElementById('perPage');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', (e) => {
                this.perPage = e.target.value;
                this.currentPage = 1;
                this.loadCurrentView();
            });
        }
    }

    /**
     * Load documents for the current view
     */
    async loadCurrentView(forceRefresh = false) {
        const params = new URLSearchParams({
            type: this.currentType,
            page: this.currentPage,
            sort_by: this.sortBy,
            sort_order: this.sortOrder,
            toggle: this.toggle,
            per_page: this.perPage || 15
        });

        if (this.searchTerm) {
            params.append('search', this.searchTerm);
        }

        const url = `/api/documents?${params.toString()}`;
        
        try {
            await loadDocs('#documentsTableBody', url);
            this.updateUI();
        } catch (error) {
            console.error('Error loading documents:', error);
        }
    }

    /**
     * Update UI elements based on current state
     */
    updateUI() {
        // Update active tab
        document.querySelectorAll('.nav-link, .nav-item').forEach(item => {
            item.classList.toggle('active', item.dataset.tab === this.currentType);
        });

        // Update page title
        const titles = {
            'incoming': 'Incoming Documents',
            'pending': 'Pending Documents',
            'forward': 'Forwarded Documents',
            'deferred': 'Deferred Documents'
        };
        
        document.title = `DepEd ROX - ${titles[this.currentType] || 'Documents'}`;
        
        // Update header
        const pageHeader = document.getElementById('pageHeader');
        if (pageHeader) {
            pageHeader.textContent = titles[this.currentType] || 'Documents';
        }
    }

    /**
     * Bind bulk action functionality
     */
    bindBulkActions() {
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('input[name="doc_id"]:not(#selectAll)');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
                this.updateBulkActionButtons();
            });
        }

        // Individual checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.name === 'doc_id' && e.target.id !== 'selectAll') {
                this.updateBulkActionButtons();
            }
        });

        // Bulk action buttons
        const bulkAcceptBtn = document.getElementById('bulkAccept');
        const bulkForwardBtn = document.getElementById('bulkForward');
        
        if (bulkAcceptBtn) {
            bulkAcceptBtn.addEventListener('click', () => this.handleBulkAccept());
        }
        
        if (bulkForwardBtn) {
            bulkForwardBtn.addEventListener('click', () => this.handleBulkForward());
        }
    }

    /**
     * Update bulk action button states
     */
    updateBulkActionButtons() {
        const selectedCount = document.querySelectorAll('input[name="doc_id"]:checked:not(#selectAll)').length;
        const bulkActions = document.getElementById('bulkActions');
        
        if (bulkActions) {
            bulkActions.style.display = selectedCount > 0 ? 'block' : 'none';
        }

        // Update select all checkbox state
        const selectAllCheckbox = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('input[name="doc_id"]:not(#selectAll)');
        
        if (selectAllCheckbox && allCheckboxes.length > 0) {
            const checkedCount = document.querySelectorAll('input[name="doc_id"]:checked:not(#selectAll)').length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
            selectAllCheckbox.checked = checkedCount === allCheckboxes.length;
        }
    }

    /**
     * Handle bulk accept action
     */
    async handleBulkAccept() {
        const selectedDocs = Array.from(document.querySelectorAll('input[name="doc_id"]:checked:not(#selectAll)'))
                                 .map(checkbox => checkbox.value);

        if (selectedDocs.length === 0) return;

        if (!confirm(`Are you sure you want to accept ${selectedDocs.length} document(s)?`)) return;

        // Implementation for bulk accept
        console.log('Bulk accept:', selectedDocs);
        // You would implement the actual bulk accept API call here
    }

    /**
     * Handle bulk forward action
     */
    async handleBulkForward() {
        const selectedDocs = Array.from(document.querySelectorAll('input[name="doc_id"]:checked:not(#selectAll)'))
                                 .map(checkbox => checkbox.value);

        if (selectedDocs.length === 0) return;

        // Show forward modal or redirect to bulk forward page
        console.log('Bulk forward:', selectedDocs);
    }

    /**
     * Utility function for debouncing
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize document manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;
    
    // Only initialize on document-related pages
    if (path.includes('/incoming') || path.includes('/pending') || 
        path.includes('/forward') || path.includes('/deferred') || path === '/') {
        
        window.documentManager = new DocumentManager();
        window.documentManager.init();
        
        // Bind document actions
        bindDocsActions('#documentForm', '#documentsTableBody');
    }
    
    // Handle document creation page
    if (path.includes('/add')) {
        import('./modules/doc_add.js').then(module => {
            // Document creation functionality is handled in the imported module
        });
    }
});