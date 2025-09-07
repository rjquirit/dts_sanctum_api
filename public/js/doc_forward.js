import { loadDocs } from './modules/docsCrud.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing...');

    // Initialize document loading with current filter states
    loadFilteredDocs();

    // Get DOM elements
    //const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    //const docTypeFilter = document.getElementById('docTypeFilter');
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
});

function handleSearch() {
    // Trigger the filtered docs load with current search term
    loadFilteredDocs();
}

function loadFilteredDocs() {
    const searchTerm = document.getElementById('searchInput')?.value || '';
    const docTypeId = document.getElementById('docTypeFilter')?.value || '';
    const showPersonal = document.getElementById('toggleSwitch')?.checked || false;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (docTypeId) params.append('doc_type_id', docTypeId);
    params.append('toggle', showPersonal);
    
    // Call loadDocs with the constructed query string
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