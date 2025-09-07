import { loadDocs } from './modules/docsCrud.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing...');

    // Initialize document loading
    loadDocs('#documentsTableBody');

    console.log('Document loading initialized');
    // Bind search and filter events
    const searchInput = document.getElementById('searchInput');
    const docTypeFilter = document.getElementById('docTypeFilter');
    const toggleSwitch = document.getElementById('toggleSwitch');

    searchInput?.addEventListener('input', debounce(() => loadDocs('#documentsTableBody'), 300));
    docTypeFilter?.addEventListener('change', () => loadDocs('#documentsTableBody'));
    toggleSwitch?.addEventListener('change', () => loadDocs('#documentsTableBody'));
});

function debounce(func, wait) {
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
