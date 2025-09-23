import * as API from './modules/api.js';
import { loadDoctypes, loadSections, bindDocsActions, addDocument } from './modules/docsCrud.js';

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
    // bindDocsActions('#documentForm', null, () => {
    //     // Show success modal
    //     const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    //     successModal.show();
    // });

    document.getElementById('submitBtn')?.addEventListener('click', async function() {
                try {
                    const docTypeId = document.getElementById('doc_type_id').value;
                    const docdescription = document.getElementById('docs_description').value;
                    const actionneeded = document.getElementById('actions_needed').value;
                    const receivingID = document.getElementById('receiving_section').value;
        
                    if (!docTypeId.trim()) {
                        alert('Please select a document type.');
                        return;
                    }

                    if (!docdescription.trim()) {
                        alert('Please provide a brief description of the document.');
                        return;
                    }

                    if (!actionneeded.trim()) {
                        alert('Please specify the action needed for the document.');
                        return;
                    }

                    if (!receivingID.trim()) {
                        alert('Please select a receiving section.');
                        return;
                    }
        
                    // Show loading state
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        
                    // Call the keepDocument function
                    const result = await addDocument(docTypeId, docdescription, actionneeded, receivingID);

                    // const modal = bootstrap.Modal.getInstance(document.getElementById('keepDocumentModal'));
                    // modal.hide();
        
                    // Show success message
                    // alert('Document has been keeped successfully');
        
                    // // Clear any cached data
                    // localStorage.removeItem('cached_docs_pending');
        
                    // // Force a complete page reload from server
                    // window.location.reload(true);
        
                } catch (error) {
                    console.error('Error Keeping document:', error);
                    alert(error.message);
                } finally {
                    // Reset button state
                    this.disabled = false;
                    this.innerHTML = 'Confirm Submit';
                    
                    // Clear form
                    document.getElementById('actionneeded').value = '';
                    document.getElementById('docdescription').value = '';
                }
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