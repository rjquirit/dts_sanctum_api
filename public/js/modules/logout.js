import { API_CONFIG } from './config.js';
import { showError, showLoading, hideLoading } from './utils.js';

export async function logout() {
    const logoutBtn = document.getElementById('logoutBtn');
    
    try {
        showLoading(logoutBtn);
        
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        const response = await fetch(`${API_CONFIG.baseUrl}/api/logout`, {
            method: 'POST',
            headers: {
                ...API_CONFIG.headers,
                'Authorization': `Bearer ${token}`
            },
            credentials: 'include'
        });

        if (response.ok) {
            // Clear local storage
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            localStorage.removeItem('cached_users');
            
            // Redirect to login page
            window.location.href = '/login';
        } else {
            const data = await response.json();
            showError(data.message || 'Logout failed. Please try again.');
        }
    } catch (error) {
        console.error('Logout error:', error);
        showError('An error occurred while logging out. Please try again.');
    } finally {
        hideLoading(logoutBtn);
    }
}
