import { API_CONFIG } from './config.js';
import { isOnline, showError } from './utils.js';

// Request queue for offline mode
const requestQueue = [];

// Rate limiting state
const requestHistory = [];

// Cache storage
const cache = new Map();

// Default request options
const defaultOptions = {
    credentials: 'include',
    headers: {
        ...API_CONFIG.headers,
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
    }
};

/**
 * Debug logger
 */
function debugLog(...args) {
    if (API_CONFIG.debug) {
        console.debug('[API]', ...args);
    }
}

/**
 * Check and handle rate limits
 */
function checkRateLimit() {
    if (!API_CONFIG.rateLimit.enabled) return true;

    const now = Date.now();
    const windowStart = now - API_CONFIG.rateLimit.perWindow;
    
    // Clean up old requests
    while (requestHistory.length && requestHistory[0] < windowStart) {
        requestHistory.shift();
    }
    
    if (requestHistory.length >= API_CONFIG.rateLimit.maxRequests) {
        const waitTime = requestHistory[0] + API_CONFIG.rateLimit.perWindow - now;
        throw new Error(`Rate limit exceeded. Please wait ${Math.ceil(waitTime / 1000)} seconds.`);
    }
    
    requestHistory.push(now);
    return true;
}

/**
 * Cache management
 */
function getCacheKey(endpoint, options) {
    return `${options.method || 'GET'}:${endpoint}`;
}

function getCachedResponse(endpoint, options) {
    if (!API_CONFIG.cache.enabled) return null;
    if (API_CONFIG.cache.exclude.includes(endpoint)) return null;
    if (options.method !== 'GET') return null;

    const key = getCacheKey(endpoint, options);
    const cached = cache.get(key);
    
    if (!cached) return null;
    if (Date.now() - cached.timestamp > API_CONFIG.cache.duration) {
        cache.delete(key);
        return null;
    }
    
    debugLog('Cache hit:', endpoint);
    return cached.data;
}

function setCacheResponse(endpoint, options, data) {
    if (!API_CONFIG.cache.enabled || options.method !== 'GET') return;
    if (API_CONFIG.cache.exclude.includes(endpoint)) return;

    const key = getCacheKey(endpoint, options);
    cache.set(key, {
        data,
        timestamp: Date.now()
    });
    debugLog('Cache set:', endpoint);
}

/**
 * Handle API response
 */
async function handleResponse(response) {
    // Always try to parse JSON response, fall back to null if no content
    let data = null;
    try {
        data = await response.json();
    } catch (e) {
        console.debug('No JSON content in response');
    }
    
    if (!response.ok) {
        const error = new Error((data && data.message) || 'An error occurred');
        error.response = response;
        error.data = data;
        
        // Handle specific error cases
        switch (response.status) {
            case 401:
                // Handle unauthorized access
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
                throw new Error('Please log in to continue');
            case 403:
                error.message = 'You do not have permission to perform this action.';
                break;
            case 422:
                // Validation errors
                const errors = Object.values(data.errors || {}).flat();
                error.message = errors.join('\n');
                break;
            case 429:
                error.message = 'Too many requests. Please try again later.';
                break;
        }
        
        throw error;
    }
    
    return data;
}

/**
 * Queue a request for offline mode
 */
function queueRequest(endpoint, options) {
    const queueItem = { endpoint, options, timestamp: Date.now() };
    requestQueue.push(queueItem);
    debugLog('Request queued:', endpoint);
    
    // Save queue to localStorage for persistence
    localStorage.setItem('apiQueue', JSON.stringify(requestQueue));
    
    throw new Error('Request queued for when you\'re back online');
}

/**
 * Retry a failed request with exponential backoff
 */
async function retryRequest(endpoint, options, attempt = 1) {
    const { maxAttempts, delay, maxDelay, shouldRetry } = API_CONFIG.retry;
    
    try {
        const url = `${API_CONFIG.baseUrl}${endpoint}`;
        const response = await fetch(url, options);
        return await handleResponse(response);
    } catch (error) {
        if (attempt >= maxAttempts || !shouldRetry(error)) {
            throw error;
        }

        const waitTime = Math.min(delay * Math.pow(2, attempt - 1), maxDelay);
        debugLog(`Retry attempt ${attempt} for ${endpoint} in ${waitTime}ms`);
        
        await new Promise(resolve => setTimeout(resolve, waitTime));
        return retryRequest(endpoint, options, attempt + 1);
    }
}

/**
 * Make an API request
 */
async function apiRequest(endpoint, options = {}) {
    debugLog('Request:', options.method || 'GET', endpoint);

    // Check rate limits
    try {
        checkRateLimit();
    } catch (error) {
        if (API_CONFIG.rateLimit.queueExceeding) {
            return queueRequest(endpoint, options);
        }
        throw error;
    }

    // Handle offline state
    if (!isOnline()) {
        if (options.method === 'GET') {
            const cached = getCachedResponse(endpoint, options);
            if (cached) return cached;
        }
        return queueRequest(endpoint, options);
    }

    const finalOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };

    try {
        // Check cache first for GET requests
        if (options.method === 'GET') {
            const cached = getCachedResponse(endpoint, options);
            if (cached) {
                // Revalidate in background if enabled
                if (API_CONFIG.cache.revalidate) {
                    retryRequest(endpoint, finalOptions)
                        .then(fresh => setCacheResponse(endpoint, options, fresh))
                        .catch(error => debugLog('Revalidation failed:', error));
                }
                return cached;
            }
        }

        const data = await retryRequest(endpoint, finalOptions);
        
        // Cache successful GET requests
        if (options.method === 'GET') {
            setCacheResponse(endpoint, options, data);
        }

        return data;
    } catch (error) {
        debugLog('Request failed:', error);
        throw error;
    }
}

/**
 * GET request
 */
export function get(endpoint, options = {}) {
    return apiRequest(endpoint, {
        ...options,
        method: 'GET'
    });
}

/**
 * POST request
 */
export function post(endpoint, data, options = {}) {
    return apiRequest(endpoint, {
        ...options,
        method: 'POST',
        body: JSON.stringify(data)
    });
}

/**
 * PUT request
 */
export function put(endpoint, data, options = {}) {
    return apiRequest(endpoint, {
        ...options,
        method: 'PUT',
        body: JSON.stringify(data)
    });
}

/**
 * DELETE request
 */
export function del(endpoint, options = {}) {
    return apiRequest(endpoint, {
        ...options,
        method: 'DELETE'
    });
}
