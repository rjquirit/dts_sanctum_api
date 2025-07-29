// Base configuration for API calls
export const API_CONFIG = {
    baseUrl: 'http://127.0.0.1:8000',
    timeout: 8000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    // Retry configuration
    retry: {
        maxAttempts: 3,
        delay: 1000, // Base delay between retries (ms)
        maxDelay: 5000, // Maximum delay between retries
        shouldRetry: (error) => {
            // Retry on network errors and 5xx server errors
            return error.name === 'NetworkError' || 
                   (error.response && error.response.status >= 500);
        }
    },
    // Cache configuration
    cache: {
        enabled: true,
        duration: 5 * 60 * 1000, // 5 minutes
        exclude: ['/api/login', '/api/logout'], // Routes to never cache
        revalidate: true // Background revalidation of cached data
    },
    // Rate limiting
    rateLimit: {
        enabled: true,
        maxRequests: 50,
        perWindow: 60 * 1000, // 1 minute window
        queueExceeding: true // Queue requests that would exceed the rate limit
    },
    // Debug mode
    //debug: process.env.NODE_ENV !== 'production'
    debug: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
};
