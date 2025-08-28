/**
 * Document Add Form Handler
 * Handles form validation, submission, and user interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('documentForm');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    // Form fields
    const fields = {
        from: document.getElementById('from'),
        office: document.getElementById('office'),
        document_type: document.getElementById('document_type'),
        details: document.getElementById('details'),
        purpose: document.getElementById('purpose'),
        receiving_units: document.getElementById('receiving_units'),
        document_file: document.getElementById('document_file')
    };

    // Validation rules
    const validationRules = {
        from: {
            required: true,
            minLength: 2,
            maxLength: 100,
            message: 'Full name must be between 2 and 100 characters'
        },
        office: {
            required: true,
            minLength: 2,
            maxLength: 150,
            message: 'Office name must be between 2 and 150 characters'
        },
        document_type: {
            required: true,
            message: 'Please select a document type'
        },
        details: {
            required: true,
            minLength: 10,
            maxLength: 1000,
            message: 'Details must be between 10 and 1000 characters'
        },
        purpose: {
            required: true,
            minLength: 10,
            maxLength: 1000,
            message: 'Purpose must be between 10 and 1000 characters'
        },
        receiving_units: {
            required: true,
            message: 'Please select receiving units'
        },
        document_file: {
            required: true,
            message: 'Please select a document file'
        }
    };

    // Initialize form
    initializeForm();

    function initializeForm() {
        // Add event listeners
        form.addEventListener('submit', handleFormSubmit);
        cancelBtn.addEventListener('click', handleCancel);
        
        // Add real-time validation
        Object.keys(fields).forEach(fieldName => {
            const field = fields[fieldName];
            if (field) {
                field.addEventListener('blur', () => validateField(fieldName));
                field.addEventListener('input', () => clearFieldError(fieldName));
            }
        });

        // File input specific validation
        fields.document_file.addEventListener('change', validateFile);

        // Auto-resize textareas
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', autoResize);
        });
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitForm();
        }
    }

    function validateForm() {
        let isValid = true;
        
        Object.keys(validationRules).forEach(fieldName => {
            if (!validateField(fieldName)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function validateField(fieldName) {
        const field = fields[fieldName];
        const rules = validationRules[fieldName];
        const value = field.value.trim();

        // Clear previous errors
        clearFieldError(fieldName);

        // Required validation
        if (rules.required && !value) {
            showFieldError(fieldName, `${getFieldLabel(fieldName)} is required`);
            return false;
        }

        // Skip other validations if field is empty and not required
        if (!value) return true;

        // Length validations
        if (rules.minLength && value.length < rules.minLength) {
            showFieldError(fieldName, rules.message);
            return false;
        }

        if (rules.maxLength && value.length > rules.maxLength) {
            showFieldError(fieldName, rules.message);
            return false;
        }

        // File validation
        if (fieldName === 'document_file' && field.files.length > 0) {
            return validateFileContent(field.files[0], fieldName);
        }

        return true;
    }

    function validateFile() {
        const file = fields.document_file.files[0];
        if (file) {
            validateFileContent(file, 'document_file');
        }
    }

    function validateFileContent(file, fieldName) {
        // File size validation (10MB max)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            showFieldError(fieldName, 'File size must not exceed 10MB');
            return false;
        }

        // File type validation
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'image/png'
        ];

        if (!allowedTypes.includes(file.type)) {
            showFieldError(fieldName, 'Please select a valid file format (PDF, DOC, DOCX, JPG, JPEG, PNG)');
            return false;
        }

        return true;
    }

    function showFieldError(fieldName, message) {
        const field = fields[fieldName];
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        
        field.classList.add('is-invalid');
        if (feedback) {
            feedback.textContent = message;
        }
    }

    function clearFieldError(fieldName) {
        const field = fields[fieldName];
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        
        field.classList.remove('is-invalid');
        if (feedback) {
            feedback.textContent = '';
        }
    }

    function getFieldLabel(fieldName) {
        const labels = {
            from: 'Full Name',
            office: 'Office',
            document_type: 'Document Type',
            details: 'Details',
            purpose: 'Purpose of Submission',
            receiving_units: 'Receiving Units',
            document_file: 'Document File'
        };
        return labels[fieldName] || fieldName;
    }

    function submitForm() {
        // Show loading state
        showLoadingState(true);
        loadingModal.show();

        const formData = new FormData(form);

        // Submit via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                               document.querySelector('input[name="_token"]')?.value
            }
        })
        .then(async response => {
            const data = await response.json();
            
            if (response.ok) {
                handleSubmitSuccess(data);
            } else {
                handleSubmitError(data);
            }
        })
        .catch(error => {
            console.error('Submit error:', error);
            handleSubmitError({
                message: 'An unexpected error occurred. Please try again.',
                errors: {}
            });
        })
        .finally(() => {
            showLoadingState(false);
            loadingModal.hide();
        });
    }

    function handleSubmitSuccess(data) {
        // Show success message
        showAlert('success', data.message || 'Document submitted successfully!');
        
        // Reset form
        form.reset();
        clearAllErrors();

        // Redirect after delay
        setTimeout(() => {
            window.location.href = data.redirect || '/documents';
        }, 2000);
    }

    function handleSubmitError(data) {
        // Show general error message
        showAlert('danger', data.message || 'Please check the form for errors and try again.');
        
        // Show field-specific errors
        if (data.errors) {
            Object.keys(data.errors).forEach(fieldName => {
                if (fields[fieldName]) {
                    showFieldError(fieldName, data.errors[fieldName][0]);
                }
            });
        }
    }

    function showLoadingState(loading) {
        const spinner = submitBtn.querySelector('.spinner-border');
        
        if (loading) {
            submitBtn.disabled = true;
            cancelBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
        } else {
            submitBtn.disabled = false;
            cancelBtn.disabled = false;
            spinner.classList.add('d-none');
            submitBtn.innerHTML = 'Submit';
        }
    }

    function handleCancel() {
        if (isFormDirty()) {
            if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                resetForm();
                window.location.href = '/documents';
            }
        } else {
            window.location.href = '/documents';
        }
    }

    function isFormDirty() {
        return Object.values(fields).some(field => {
            if (field.type === 'file') {
                return field.files.length > 0;
            }
            return field.value.trim() !== '';
        });
    }

    function resetForm() {
        form.reset();
        clearAllErrors();
    }

    function clearAllErrors() {
        Object.keys(fields).forEach(fieldName => {
            clearFieldError(fieldName);
        });
    }

    function autoResize() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    }

    function showAlert(type, message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert at the top of the form
        form.insertBefore(alertDiv, form.firstChild);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Character counter for textareas
    function addCharacterCounter() {
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            const maxLength = validationRules[textarea.id]?.maxLength;
            if (maxLength) {
                const counter = document.createElement('small');
                counter.className = 'text-muted character-counter';
                counter.style.float = 'right';
                textarea.parentNode.appendChild(counter);

                function updateCounter() {
                    const remaining = maxLength - textarea.value.length;
                    counter.textContent = `${remaining} characters remaining`;
                    counter.style.color = remaining < 50 ? '#dc3545' : '#6c757d';
                }

                textarea.addEventListener('input', updateCounter);
                updateCounter();
            }
        });
    }

    // Initialize character counters
    addCharacterCounter();
});