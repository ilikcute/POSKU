import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Set up CSRF token for axios - initial load from meta tag
let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error(
        "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token"
    );
}

// Update CSRF token dynamically on every axios request
window.axios.interceptors.request.use(function (config) {
    // Try to get fresh token from meta tag (updated by Inertia)
    const freshToken = document.head.querySelector('meta[name="csrf-token"]');
    if (freshToken) {
        config.headers['X-CSRF-TOKEN'] = freshToken.content;
    }
    return config;
});
