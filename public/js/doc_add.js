import * as API from './modules/api.js';
import { loadDoctypes, loadSections, bindDocsActions } from './modules/docsCrud.js';

document.addEventListener('DOMContentLoaded', function() {
    // Get user info from session storage
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    const userName = userData.name || 'Unknown User';
    const sectionName = userData.section_name || 'Unknown Section';
    
    // Set form field values
    const originSchool = document.getElementById('origin_school');
    if (originSchool) originSchool.value = sectionName;

    // Initialize form elements
    loadDoctypes('#doc_type_id');
    loadSections('#receiving_section');

    // Bind form actions with success callback
    bindDocsActions('#documentForm', null, () => {
        // Show success modal
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });

    // Handle cancel button
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
                window.location.href = '/incoming';
            }
        });
    }
});