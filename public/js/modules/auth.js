import { fetchWithTimeout, isOnline } from './utils.js';
import { API_CONFIG } from './config.js';

// Helper function to get cookie value
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Decode the XSRF token from the cookie
function decodeXSRFToken(cookie) {
    if (!cookie) return null;
    return decodeURIComponent(cookie);
}

export function bindRegister(formSelector) {
    const form = document.querySelector(formSelector);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // No external validation, use HTML5 validation and custom feedback
    const passwordInput = form.querySelector('#password');
    const confirmInput = form.querySelector('#password_confirmation');
    if (passwordInput && confirmInput) {
        confirmInput.addEventListener('input', () => {
            if (confirmInput.value !== passwordInput.value) {
                confirmInput.setCustomValidity('Passwords do not match.');
            } else {
                confirmInput.setCustomValidity('');
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!isOnline()) {
            showAlert('You are offline. Please check your internet connection.', 'danger', form);
            return;
        }

        // Check form validity
        if (!form.checkValidity()) {
            showAlert('Please fill all required fields correctly.', 'danger', form);
            return;
        }

        try {
            setLoading(submitBtn, true);
            
            await fetchWithTimeout(`${API_CONFIG.baseUrl}/sanctum/csrf-cookie`, {
                credentials: 'include',
                timeout: API_CONFIG.timeout
            });

            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password'),
                password_confirmation: formData.get('password_confirmation')
            };

            // Validate password match
            if (data.password !== data.password_confirmation) {
                showAlert('Passwords do not match.', 'danger', form);
                return;
            }

            const response = await fetchWithTimeout(`${API_CONFIG.baseUrl}/api/register`, {
                method: 'POST',
                credentials: 'include',
                headers: API_CONFIG.headers,
                body: JSON.stringify(data),
                timeout: API_CONFIG.timeout
            });

            const responseData = await response.json();

            if (response.ok) {
                // Cache user data for offline access
                localStorage.setItem('user', JSON.stringify(responseData.user));
                localStorage.setItem('auth_token', responseData.access_token);
                window.location.href = '/';
            } else {
                showAlert(responseData.message || 'Registration failed. Please try again.', 'danger', form);
            }
        } catch (error) {
            console.error('Registration error:', error);
            showAlert('An error occurred while registering. Please try again.', 'danger', form);
        } finally {
            setLoading(submitBtn, false);
        }
    });
}

export function bindLogin(formSelector) {
  const form = document.querySelector(formSelector);
  const submitBtn = form.querySelector('button[type="submit"]');
  
  // No external validation, use HTML5 validation and custom feedback

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!isOnline()) {
      showAlert('You are offline. Please check your internet connection.', 'danger', form);
      return;
    }

    // Check form validity
    if (!form.checkValidity()) {
      showAlert('Please fill all required fields correctly.', 'danger', form);
      return;
    }

    try {
      setLoading(submitBtn, true);
      
      // Get CSRF cookie
      await fetchWithTimeout(`${API_CONFIG.baseUrl}/sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include',
        timeout: API_CONFIG.timeout
      });
      
      // Wait a moment for the cookie to be set
      await new Promise(resolve => setTimeout(resolve, 100));

      const formData = new FormData(form);
      const data = {
        email: formData.get('email'),
        password: formData.get('password')
      };

      const response = await fetchWithTimeout(`${API_CONFIG.baseUrl}/api/login`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            ...API_CONFIG.headers,
            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
        },
        body: JSON.stringify(data)
      });

      const responseData = await response.json();

      if (response.ok) {
        // Cache user data for offline access
        localStorage.setItem('user', JSON.stringify(responseData.user));
        localStorage.setItem('auth_token', responseData.access_token); // Add this line to store the token
        
        console.log('Login successful:', {
          user: responseData.user,
          token: responseData.access_token
        });
        
        window.location.href = '/dashboard';
      } else {
        console.error('Login failed:', {
          status: response.status,
          response: responseData
        });
        showAlert(responseData.message || 'Login failed. Please check your credentials.', 'danger', form);
      }
    } catch (error) {
      console.error('Login error:', {
        message: error.message,
        stack: error.stack,
        response: error.response
      });
      showAlert('An error occurred while logging in. Please try again.', 'danger', form);
    } finally {
      setLoading(submitBtn, false);
    }
    
  });
}

// --- Pure JS UI helpers ---
function showAlert(message, type = 'danger', form) {
  let alert = form.querySelector('.js-alert');
  if (!alert) {
    alert = document.createElement('div');
    alert.className = `alert alert-${type} js-alert`;
    alert.role = 'alert';
    form.prepend(alert);
  }
  alert.textContent = message;
  alert.classList.remove('d-none');
}

function setLoading(btn, loading = true) {
  if (!btn) return;
  if (loading) {
    btn.disabled = true;
    btn.dataset.originalText = btn.textContent;
    btn.textContent = 'Please wait...';
  } else {
    btn.disabled = false;
    if (btn.dataset.originalText) btn.textContent = btn.dataset.originalText;
  }
}

// --- 2FA API integration ---
// All functions assume user is authenticated (Sanctum token present)

export async function enable2FA() {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor`, {
    method: 'POST',
    credentials: 'include',
    headers: API_CONFIG.headers,
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Failed to enable 2FA');
  return await res.json(); // { message, qr_code, recovery_codes }
}

export async function confirm2FA(code) {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor/confirm`, {
    method: 'POST',
    credentials: 'include',
    headers: { ...API_CONFIG.headers, 'Content-Type': 'application/json' },
    body: JSON.stringify({ code }),
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Failed to confirm 2FA');
  return await res.json(); // { message }
}

export async function disable2FA() {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor`, {
    method: 'DELETE',
    credentials: 'include',
    headers: API_CONFIG.headers,
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Failed to disable 2FA');
  return await res.json(); // { message }
}

export async function get2FAStatus() {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor`, {
    method: 'GET',
    credentials: 'include',
    headers: API_CONFIG.headers,
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Failed to get 2FA status');
  return await res.json(); // { two_factor_enabled, two_factor_confirmed }
}

export async function get2FAQrAndCodes() {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor`, {
    method: 'GET',
    credentials: 'include',
    headers: API_CONFIG.headers,
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Failed to get 2FA QR and codes');
  return await res.json(); // { qr_code, recovery_codes }
}

export async function get2FARecoveryCodes() {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor/recovery-codes`, {
    method: 'GET',
    credentials: 'include',
    headers: API_CONFIG.headers,
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Failed to get recovery codes');
  return await res.json(); // { recovery_codes }
}

export async function challenge2FA(code) {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor/challenge`, {
    method: 'POST',
    credentials: 'include',
    headers: { ...API_CONFIG.headers, 'Content-Type': 'application/json' },
    body: JSON.stringify({ code }),
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Invalid 2FA code');
  return await res.json(); // { message }
}

export async function challenge2FARecovery(recovery_code) {
  const res = await fetchWithTimeout(`${API_CONFIG.baseUrl}/two-factor/challenge-recovery`, {
    method: 'POST',
    credentials: 'include',
    headers: { ...API_CONFIG.headers, 'Content-Type': 'application/json' },
    body: JSON.stringify({ recovery_code }),
    timeout: API_CONFIG.timeout
  });
  if (!res.ok) throw new Error('Invalid recovery code');
  return await res.json(); // { message, remaining_codes }
}

