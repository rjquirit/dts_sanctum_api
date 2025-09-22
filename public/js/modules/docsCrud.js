import * as API from './api.js';
import { showLoading, hideLoading, showError, showSuccess, isOnline } from './utils.js';

/**
 * Load document types into a select element
 * @param {string} selectSelector - CSS selector for the select element
 */
export async function loadDoctypes(selectSelector) {
    const select = document.querySelector(selectSelector);
    if (!select) return;

    try {
        // Show loading state
        select.innerHTML = '<option value="">Loading document types...</option>';
        select.disabled = true;

        // Fetch document types from API
        const response = await fetch('/api/test/doctypes');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseData = await response.json();
        
        // The data is directly in responseData.data array
        const doctypes = responseData.data || [];
        
        if (!Array.isArray(doctypes)) {
            throw new Error('Invalid document types data format');
        }

        // Populate select with document types
        select.innerHTML = `
            <option value="">-- Select Document Type --</option>
            ${doctypes.map(type => {
                const id = type.doctype_id || '';
                const name = type.doctype_description || 'Unnamed Type';
                return id ? `<option value="${id}">${name}</option>` : '';
            }).join('')}
        `;
        
    } catch (error) {
        console.error('Error loading document types:', error);
        select.innerHTML = '<option value="">Error loading document types</option>';
        showError('Failed to load document types. Please refresh the page to try again.');
    } finally {
        select.disabled = false;
    }
}

/**
 * Load sections into a select element
 * @param {string} selectSelector - CSS selector for the select element
 */
export async function loadSections(selectSelector) {
    const select = document.querySelector(selectSelector);
    if (!select) return;

    try {
        // Show loading state
        select.innerHTML = '<option value="">Loading sections...</option>';
        select.disabled = true;

        // Fetch sections from API
        const response = await fetch('/api/test/sections');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseData = await response.json();
        
        // The data is directly in responseData.data array
        const sections = responseData.data || [];
        
        if (!Array.isArray(sections)) {
            throw new Error('Invalid sections data format');
        }

        // Filter out the current user's section
        // const userSectionId = document.getElementById('origin_section')?.value;
        // const filteredSections = sections.filter(section => {
        //     const sectionId = section.section_id || '';
        //     return !userSectionId || sectionId != userSectionId;
        // });
        
        // Populate select with sections
        select.innerHTML = `
            <option value="">-- Select Receiving Unit --</option>
            ${sections.map(section => {
                const sectionId = section.section_id || '';
                const sectionName = section.section_description || 'Unnamed Section';
                return sectionId ? `<option value="${sectionId}">${sectionName}</option>` : '';
            }).join('')}
        `;
        
    } catch (error) {
        console.error('Error loading sections:', error);
        select.innerHTML = '<option value="">Error loading sections</option>';
        showError('Failed to load sections. Please refresh the page to try again.');
    } finally {
        select.disabled = false;
    }
}

/**
 * Bind document form actions
 * @param {string} formSelector - CSS selector for the form element
 * @param {string} tableBodySelector - CSS selector for the table body (optional)
 */
export function bindDocsActions(formSelector, tableBodySelector = null) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    // Prevent multiple bindings
    if (form.dataset.bound === 'true') return;
    form.dataset.bound = 'true';

    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!isOnline()) {
            showError('Cannot submit document while offline. Please check your internet connection.');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);

        try {
            // Show loading state
            showLoading(submitBtn);
            
            // Validate required fields
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                showError('Please fill in all required fields.');
                return;
            }

            // Get auth token from localStorage
            const token = localStorage.getItem('auth_token');
            if (!token) {
                console.error('No authentication token found');
                window.location.href = '/login';
                return;
            }

            // Prepare data for submission
            const data = {
                doc_type_id: formData.get('doc_type_id'),
                docs_description: formData.get('docs_description'),
                origin_fname: formData.get('origin_fname'),
                origin_school: formData.get('origin_school'),
                origin_school_id: formData.get('origin_school_id'),
                origin_section: formData.get('origin_section'),
                origin_userid: formData.get('origin_userid'),
                receiving_section: formData.get('receiving_section'),
                actions_needed: formData.get('actions_needed'),
                route_purpose: formData.get('actions_needed')
            };

            // Submit the document
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(data)
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'Failed to submit document');
            }

            // Show success message
            showSuccess('Document submitted successfully.');
            
            // Get the newly created document ID from the response
            const trackingNumber = responseData.data?.tracking_number;
            
            if (trackingNumber) {
                // Redirect to the tracking page
                window.location.href = `/find?tracking=${trackingNumber}`;
            } else {
                // Fallback if no ID is returned
                alert('Document has been submitted successfully');
                window.location.href = '/incoming';
            }

            // Reset form
            form.reset();
            
            // If we have a table to update, refresh it
            if (tableBodySelector) {
                // You can implement table refresh logic here if needed
            }

        } catch (error) {
            console.error('Error submitting document:', error);
            showError(error.message || 'An error occurred while submitting the document.');
        } finally {
            hideLoading(submitBtn);
        }
    });

    // Real-time validation
    const validateField = (field) => {
        if (field.required && !field.value.trim()) {
            field.classList.add('is-invalid');
            return false;
        }
        field.classList.remove('is-invalid');
        return true;
    };

    // Add input event listeners for real-time validation
    form.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
        field.addEventListener('input', () => validateField(field));
        field.addEventListener('change', () => validateField(field));
        field.addEventListener('blur', () => validateField(field));
    });
}