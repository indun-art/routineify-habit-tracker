const API_BASE_URL = 'http://localhost:3000/api';

// Utility function to get token
function getToken() {
    return localStorage.getItem('token');
}

// Utility function for fetch with auth header
async function fetchWithAuth(url, options = {}) {
    const token = getToken();
    if (!token) {
        window.location.href = 'index.html';
        return;
    }
    const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        ...options.headers
    };
    const response = await fetch(`${API_BASE_URL}${url}`, { ...options, headers });
    if (response.status === 401 || response.status === 403) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = 'index.html';
        return;
    }
    return response;
}

// Check if user is logged in
function checkAuth() {
    if (!getToken()) {
        window.location.href = 'index.html';
    }
}

function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

// Expose to window for easy access
window.api = {
    fetchWithAuth,
    checkAuth,
    logout,
    API_BASE_URL
};
