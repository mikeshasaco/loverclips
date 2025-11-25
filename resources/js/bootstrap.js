import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

// Add CSRF token to all requests using an interceptor
window.axios.interceptors.request.use(function (config) {
    // Get fresh CSRF token for each request
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token && token.content) {
        config.headers['X-CSRF-TOKEN'] = token.content;
        // Also add to FormData if it's a FormData request
        if (config.data instanceof FormData) {
            // Don't append if already exists (avoid duplicates)
            if (!config.data.has('_token')) {
                config.data.append('_token', token.content);
            }
        }
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

// Handle 419 errors by refreshing the CSRF token
window.axios.interceptors.response.use(
    function (response) {
        return response;
    },
    async function (error) {
        if (error.response && error.response.status === 419) {
            // CSRF token mismatch - try to get a fresh token
            console.warn('CSRF token mismatch. Attempting to refresh token...');
            
            try {
                const tokenResponse = await window.axios.get('/api/csrf-token', {
                    withCredentials: true
                });
                
                if (tokenResponse.data && tokenResponse.data.csrf_token) {
                    // Update the meta tag
                    const metaTag = document.head.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', tokenResponse.data.csrf_token);
                    }
                    
                    // Retry the original request with new token
                    const originalRequest = error.config;
                    originalRequest.headers['X-CSRF-TOKEN'] = tokenResponse.data.csrf_token;
                    
                    if (originalRequest.data instanceof FormData) {
                        // Remove old token if exists
                        if (originalRequest.data.has('_token')) {
                            originalRequest.data.delete('_token');
                        }
                        originalRequest.data.append('_token', tokenResponse.data.csrf_token);
                    }
                    
                    return window.axios(originalRequest);
                }
            } catch (tokenError) {
                console.error('Failed to refresh CSRF token:', tokenError);
            }
            
            // If all else fails, reload the page
            console.warn('Reloading page to refresh CSRF token...');
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
