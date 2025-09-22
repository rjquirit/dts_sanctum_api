import { loadDocs, forwardDocument, loadSections, loadSectionUsers } from './modules/docsForward.js';

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

    document.querySelector('#documentsTableBody').addEventListener('click', (e) => {
            const acceptBtn = e.target.closest('.forward');
            if (acceptBtn) {
                const row = acceptBtn.closest('tr');
                const actionId = row.querySelector('input[name="action_id"]').value; // Get the hidden action_id
                showForwardModal({
                    actionId: actionId, // Use the action_id from hidden input
                    tracking: row.querySelector('td:nth-child(1)').textContent.trim(),
                    description: row.querySelector('td:nth-child(2)').textContent.trim(),
                    from: row.querySelector('td:nth-child(3)').textContent.trim()
                });
            }
        });
    
            // In the confirmforwardBtn click handler:
        
        document.getElementById('confirmforwardBtn')?.addEventListener('click', async function() {
            try {
                const actionId = document.getElementById('forwardActionId').value;
                const forwardReason = document.getElementById('forwardReason').value.trim();
                const forwardCopy = document.getElementById('forwardCopy').value;
                const receiving_section = document.getElementById('receiving_section').value;
                const receiving_personnel = document.getElementById('receiving_personnel').value;
                const forward_purpose = document.getElementById('forward_purpose').value.trim();
        
                // Improved validation
                if (!actionId) {
                    throw new Error('Invalid action ID');
                }
                if (!receiving_section) {
                    throw new Error('Please select a receiving section');
                }
                if (!receiving_personnel) {
                    throw new Error('Please select a receiving personnel');
                }
                if (!forward_purpose) {
                    throw new Error('Please provide a forwarding purpose');
                }
        
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        
                // Call the forwardDocument function
                const result = await forwardDocument(
                    actionId, 
                    forwardReason, 
                    forwardCopy || '1', // Default to 1 if empty
                    receiving_section,
                    receiving_personnel,
                    forward_purpose
                );
        
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('forwardDocumentModal'));
                modal.hide();
        
                // Show success message
                alert('Document has been forwarded successfully');
        
                // Clear any cached data
                localStorage.removeItem('cached_docs_pending');
        
                // Force a complete page reload from server
                window.location.reload(true);
        
            } catch (error) {
                console.error('Error forwarding document:', error);
                alert(error.message || 'Failed to forward document. Please try again.');
            } finally {
                // Reset button state
                this.disabled = false;
                this.innerHTML = 'Confirm Forward';
            }
        });
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
    params.append('type', 'forward'); // <-- ADD THIS LINE
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
// Add this after document.addEventListener('DOMContentLoaded', ...)
document.querySelector('#documentsTableBody').addEventListener('click', (e) => {
    const printBtn = e.target.closest('.print-doc');
    if (printBtn) {
        e.preventDefault();
        const trackingNumber = printBtn.dataset.tracking;
        if (trackingNumber) {
            printDocument(trackingNumber);
        }
    }
});

function printDocument(trackingNumber) {
    // Open print.blade.php in a new window
    const printWindow = window.open(`/print/${trackingNumber}/doc`, '_blank', 'width=800,height=600');
    
    // Add event listener to monitor when the window finishes loading
    printWindow.onload = function() {
        // Give some time for the content to fully render
        setTimeout(() => {
            printWindow.print();
            // Close the window after printing (optional)
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        }, 1000);
    };
}

async function showForwardModal(docInfo) {
    // Populate modal with document info
    document.querySelector('#forwardActionId').value = docInfo.actionId;
    document.querySelector('#forwardTrackingNo').textContent = docInfo.tracking;
    document.querySelector('#forwardDescription').textContent = docInfo.description;
    document.querySelector('#forwardFrom').textContent = docInfo.from;
    
    // Load sections when modal opens
    await loadSections();
    
    // Clear personnel dropdown when modal opens
    clearPersonnelDropdown();
    
    // Show modal
    const modal = new bootstrap.Modal(document.querySelector('#forwardDocumentModal'));
    modal.show();
}

function clearPersonnelDropdown() {
    const personnelSelect = document.querySelector('#receiving_personnel');
    personnelSelect.innerHTML = '<option value="">-- Select Personnel --</option>';
    personnelSelect.disabled = false;
}

// Event listener for section dropdown change
document.addEventListener('DOMContentLoaded', function() {
    const sectionSelect = document.querySelector('#receiving_section');
    
    if (sectionSelect) {
        sectionSelect.addEventListener('change', function() {
            const selectedSectionId = this.value;
            loadSectionUsers(selectedSectionId);
        });
    }
});

// Your existing click event listener remains the same
document.querySelector('#documentsTableBody').addEventListener('click', (e) => {
    const acceptBtn = e.target.closest('.forward');
    if (acceptBtn) {
        const row = acceptBtn.closest('tr');
        const actionId = row.querySelector('input[name="action_id"]').value;
        showForwardModal({
            actionId: actionId,
            tracking: row.querySelector('td:nth-child(1)').textContent.trim(),
            description: row.querySelector('td:nth-child(2)').textContent.trim(),
            from: row.querySelector('td:nth-child(3)').textContent.trim()
        });
    }
});