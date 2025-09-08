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

// bindGoogleLogin.js
// Expects helper functions available globally or imported:
// - API_CONFIG { baseUrl, timeout, headers, oauthPopupTimeout? }
// - fetchWithTimeout(url, opts) -> Promise<Response>
// - isOnline(), showAlert(message, type='info', container?), setLoading(button, boolean), getCookie(name)

export function bindGoogleLogin(buttonSelector) {
  const btn = document.querySelector(buttonSelector);
  if (!btn) return;

  btn.addEventListener('click', async (ev) => {
    ev.preventDefault();

    if (!isOnline()) {
      showAlert('You are offline. Please check your internet connection.', 'danger');
      return;
    }

    setLoading(btn, true);

    // Optional: request CSRF cookie first so future credentialed requests are prepared
    try {
      await fetchWithTimeout(`${API_CONFIG.baseUrl}/sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include',
        timeout: API_CONFIG.timeout
      });
      // small delay to allow cookie to be set by browser
      await new Promise(r => setTimeout(r, 120));
    } catch (err) {
      // continue anyway — this is helpful for cookie-based flows but not strictly required for top-level OAuth redirect
      console.warn('Could not fetch CSRF cookie before OAuth:', err);
    }

    // Build OAuth URL (backend endpoint that triggers Socialite redirect)
    const oauthUrl = `${API_CONFIG.baseUrl}/api/login/google`;

    // Open popup window (avoid noopener — we need window.opener)
    const width = 600;
    const height = 700;
    const left = Math.max(0, Math.floor((screen.width / 2) - (width / 2)));
    const top = Math.max(0, Math.floor((screen.height / 2) - (height / 2)));
    const features = `toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=${width},height=${height},top=${top},left=${left}`;

    const popup = window.open(oauthUrl, 'google_oauth_popup', features);
    if (!popup) {
      setLoading(btn, false);
      showAlert('Popup blocked. Please allow popups for this site and try again.', 'danger');
      return;
    }

    let finished = false;
    const maxWait = API_CONFIG.oauthPopupTimeout ?? 60000; // ms
    const pollInterval = 500;
    let elapsed = 0;

    // Helper: cleanup
    const cleanup = (reason) => {
      finished = true;
      window.removeEventListener('message', messageHandler);
      try { clearInterval(poller); } catch (e) {}
      setLoading(btn, false);
    };

    // Message event handler (if callback uses window.opener.postMessage)
    const allowedOrigin = new URL(API_CONFIG.baseUrl).origin;
    const messageHandler = (event) => {
      // optional: validate origin
      // Accept messages from backend origin or '*' if you intentionally want that
      if (event.origin !== allowedOrigin && event.origin !== window.location.origin) {
        // ignore unexpected origins
        return;
      }
      const payload = event.data;
      // Expected shape: { type: 'oauth'|'google_auth', data: {...} }
      if (payload && (payload.type === 'oauth' || payload.type === 'google_auth')) {
        cleanup('message');
        handleOauthResponse(payload.data);
      }
    };

    window.addEventListener('message', messageHandler);

    // Poller — tries to access popup.document when it returns to same-origin (backend)
    const poller = setInterval(async () => {
      if (finished) return;

      try {
        if (!popup || popup.closed) {
          cleanup('closed');
          showAlert('Popup was closed before completing sign-in.', 'danger');
          return;
        }

        // Attempt to read popup document; this will throw while popup is on google.com (cross-origin)
        const doc = popup.document;
        if (!doc) return;

        // Read visible body text (if callback returns raw JSON it will be the body text)
        let text = (doc.body && doc.body.innerText) ? doc.body.innerText.trim() : '';

        // Some backends might return JSON inside a <pre> or <script> tag — try a couple of selectors
        if (!text) {
          const pre = doc.querySelector('pre');
          if (pre) text = pre.innerText.trim();
        }

        if (text) {
          // try parse JSON
          try {
            const data = JSON.parse(text);
            cleanup('parsed_body');
            // close popup (optional)
            try { popup.close(); } catch (e) {}
            handleOauthResponse(data);
            return;
          } catch (err) {
            // Not raw JSON — maybe the server returned an html page that does postMessage instead.
            // Try to read a global `__OAUTH_PAYLOAD__` if backend embedded it into page for the popup pattern.
            const scriptEl = doc.querySelector('#oauth-payload');
            if (scriptEl) {
              try {
                const data = JSON.parse(scriptEl.textContent);
                cleanup('embedded_payload');
                try { popup.close(); } catch (e) {}
                handleOauthResponse(data);
                return;
              } catch (e) {
                // ignore and continue polling
              }
            }
          }
        }
      } catch (e) {
        // cross-origin while on provider domain — ignore until it navigates back to same origin
      }

      elapsed += pollInterval;
      if (elapsed >= maxWait) {
        cleanup('timeout');
        try { if (popup && !popup.closed) popup.close(); } catch (e) {}
        showAlert('Timed out waiting for Google sign-in. Please try again.', 'danger');
      }
    }, pollInterval);

    // Final handler for parsed response
    async function handleOauthResponse(responseData) {
      // Example expected shape:
      // { isAuthenticated: true, access_token: 'xxx', token_type: 'Bearer', user: {id, name, email, ...} }
      if (!responseData) {
        showAlert('No response received from authentication server.', 'danger');
        return;
      }

      if (responseData.isAuthenticated) {
        try {
          // Persist like your other login handler
          localStorage.setItem('user', JSON.stringify(responseData.user));
          localStorage.setItem('auth_token', responseData.access_token);

          console.log('Google login successful:', {
            user: responseData.user,
            token: responseData.access_token
          });

          // NOTE: the backend should set the httpOnly cookie `auth_token` on the OAuth callback response.
          // If cookie wasn't set due to SameSite or Secure config you may need to adjust backend cookie settings.
          // (See notes below.)
          window.location.href = '/dashboard';
        } catch (err) {
          console.error('Error handling oauth response:', err);
          showAlert('An error occurred after Google sign-in. Please try logging in again.', 'danger');
        }
      } else {
        console.error('Google login failed, server response:', responseData);
        showAlert(responseData.message || 'Google sign-in failed. Please try again.', 'danger');
      }
    }
  });
}
