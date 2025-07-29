export function showLoading(element) {
    element.classList.add('loading');
    element.disabled = true;
}

export function hideLoading(element) {
    element.classList.remove('loading');
    element.disabled = false;
}

export function showError(message, type = 'error') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
    alertDiv.textContent = message;
    
    // Remove existing alerts
    document.querySelectorAll('.alert').forEach(el => el.remove());
    
    // Insert new alert message
    document.querySelector('main').insertAdjacentElement('afterbegin', alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => alertDiv.remove(), 5000);
}

export async function fetchWithTimeout(resource, options = {}) {
    const { timeout = 8000 } = options;
    
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);
    
    try {
        const response = await fetch(resource, {
            ...options,
            signal: controller.signal
        });
        clearTimeout(id);
        return response;
    } catch (error) {
        clearTimeout(id);
        if (error.name === 'AbortError') {
            throw new Error('Request timeout');
        }
        throw error;
    }
}

export function isOnline() {
    return navigator.onLine;
}
