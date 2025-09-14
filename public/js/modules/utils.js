
/**
 * Check if the browser is online
 */
export function isOnline() {
    return navigator.onLine;
}

/**
 * Show error message
 */
export function showError(message, container = null) {
    showAlert(message, 'danger', container);
}

/**
 * Show success message
 */
export function showSuccess(message, container = null) {
    showAlert(message, 'success', container);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info', container = null) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Determine where to show the alert
    let targetContainer = container;
    if (!targetContainer) {
        targetContainer = document.querySelector('.container-fluid') || document.body;
    }

    // Insert at the beginning of the container
    targetContainer.insertBefore(alert, targetContainer.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

/**
 * Show loading state on button
 */
export function setLoading(button, loading = true, text = 'Loading...') {
    if (!button) return;
    
    const spinner = button.querySelector('.spinner-border');
    
    if (loading) {
        button.disabled = true;
        if (!button.dataset.originalText) {
            button.dataset.originalText = button.textContent;
        }
        button.textContent = text;
        if (spinner) spinner.classList.remove('d-none');
    } else {
        button.disabled = false;
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
        if (spinner) spinner.classList.add('d-none');
    }
}

/**
 * Show loading spinner in container
 */
export function showLoading(container) {
    if (!container) return;
    
    const spinner = document.createElement('div');
    spinner.className = 'text-center p-4';
    spinner.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Loading documents...</div>
    `;
    
    container.innerHTML = '';
    container.appendChild(spinner);
}

/**
 * Hide loading spinner
 */
export function hideLoading(container) {
    if (!container) return;
    
    const spinner = container.querySelector('.spinner-border');
    if (spinner) {
        spinner.closest('.text-center')?.remove();
    }
}

/**
 * Fetch with timeout support
 */
export async function fetchWithTimeout(url, options = {}) {
    const { timeout = 30000, ...fetchOptions } = options;
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);
    
    try {
        const response = await fetch(url, {
            ...fetchOptions,
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        return response;
    } catch (error) {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
            throw new Error('Request timed out');
        }
        throw error;
    }
}

/**
 * Format date for display
 */
export function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    return new Date(date).toLocaleDateString(undefined, finalOptions);
}

/**
 * Sanitize HTML content
 */
export function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) {
        return '';
    }
    
    const str = String(unsafe);
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Debounce function for performance
 */
export function debounce(func, wait) {
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

/**
 * Get URL parameters
 */
export function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const result = {};
    for (const [key, value] of params) {
        result[key] = value;
    }
    return result;
}

/**
 * Validate form field
 */
export function validateField(field) {
    if (field.hasAttribute('required') && !field.value.trim()) {
        field.classList.add('is-invalid');
        return false;
    } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        return true;
    }
}