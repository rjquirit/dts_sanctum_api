import { loadDocs } from './modules/docsPending.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing...');

    // Initialize document loading with current filter states
    loadFilteredDocs();

    // Get DOM elements
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const docTypeFilter = document.getElementById('docTypeFilter');
    const toggleSwitch = document.getElementById('toggleSwitch');

    // Search button click handler
    searchBtn?.addEventListener('click', handleSearch);
    
    // Enter key handler for search input
    searchInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
    
    // Other filter change handlers
    docTypeFilter?.addEventListener('change', loadFilteredDocs);
    toggleSwitch?.addEventListener('change', loadFilteredDocs);

    // Add click handlers for sortable headers
    document.querySelectorAll('th.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const sortBy = header.dataset.sortBy;
            const currentUrl = new URL(window.location.href);
            const currentSortBy = currentUrl.searchParams.get('sort_by');
            const currentSortOrder = currentUrl.searchParams.get('sort_order');
            
            let newSortOrder = 'asc';
            if (currentSortBy === sortBy) {
                newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            }
            
            // Update URL without page reload
            currentUrl.searchParams.set('sort_by', sortBy);
            currentUrl.searchParams.set('sort_order', newSortOrder);
            window.history.pushState({}, '', currentUrl);
            
            // Reload documents with new sorting
            loadFilteredDocs();
            
            // Update sort indicators
            updateSortIndicators(sortBy, newSortOrder);
        });
    });

    // Initial sort indicators
    updateSortIndicatorsFromUrl();
});

function updateSortIndicators(sortBy, sortOrder) {
    // Remove all sort indicators
    document.querySelectorAll('th.sortable i').forEach(icon => {
        icon.className = 'fas fa-sort ms-1';
    });
    
    // Add sort indicator to current column
    const activeHeader = document.querySelector(`th[data-sort-by="${sortBy}"]`);
    if (activeHeader) {
        const icon = activeHeader.querySelector('i');
        if (icon) {
            icon.className = sortOrder === 'asc' 
                ? 'fas fa-sort-up ms-1' 
                : 'fas fa-sort-down ms-1';
        }
    }
}

function updateSortIndicatorsFromUrl() {
    const url = new URL(window.location.href);
    const sortBy = url.searchParams.get('sort_by') || 'datetime_posted';
    const sortOrder = url.searchParams.get('sort_order') || 'desc';
    updateSortIndicators(sortBy, sortOrder);
}

function handleSearch() {
    // Reset to first page when searching
    const url = new URL(window.location.href);
    url.searchParams.delete('page');
    window.history.pushState({}, '', url);
    
    // Trigger the filtered docs load with current search term
    loadFilteredDocs();
}

function loadFilteredDocs() {
    const searchTerm = document.getElementById('searchInput')?.value || '';
    const docTypeId = document.getElementById('docTypeFilter')?.value || '';
    const showPersonal = document.getElementById('toggleSwitch')?.checked || false;
    
    // Get current URL parameters
    const url = new URL(window.location.href);
    const sortBy = url.searchParams.get('sort_by') || 'datetime_posted';
    const sortOrder = url.searchParams.get('sort_order') || 'desc';
    const page = url.searchParams.get('page') || 1;
    
    // Build query parameters - ADD TYPE HERE
    const params = new URLSearchParams();
    params.append('type', 'pending'); // <-- ADD THIS LINE
    if (searchTerm) params.append('search', searchTerm);
    if (docTypeId) params.append('doc_type_id', docTypeId);
    params.append('toggle', showPersonal);
    params.append('sort_by', sortBy);
    params.append('sort_order', sortOrder);
    params.append('page', page);
    
    // Call with type as query parameter
    loadDocs('#documentsTableBody', `/api/documents?${params.toString()}`);
}

// Debounce function to limit rapid calls
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}