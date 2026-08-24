// src/utils/api.js

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
const STORAGE_BASE_URL = import.meta.env.VITE_STORAGE_BASE_URL || 'http://127.0.0.1:8000/storage';

/**
 * Resolves full URL for an image/avatar, handling storage relative paths, external URLs, and fallbacks.
 */
export function getImageUrl(path, fallback = 'https://i.pravatar.cc/150?img=12') {
  if (!path) return fallback;
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
    return path;
  }
  const cleanPath = path.replace(/^\/?(storage\/)?/, '');
  return `${STORAGE_BASE_URL}/${cleanPath}`;
}

/**
 * Helper to build URL with query params
 */
function buildUrl(endpoint, params) {
  let url = `${API_BASE_URL}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;
  if (params && Object.keys(params).length > 0) {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        searchParams.append(key, value);
      }
    });
    const queryString = searchParams.toString();
    if (queryString) {
      url += (url.includes('?') ? '&' : '?') + queryString;
    }
  }
  return url;
}

/**
 * Central API request handler
 */
export async function apiRequest(endpoint, options = {}) {
  const token = localStorage.getItem('auth_token');
  const headers = {
    Accept: 'application/json',
    ...(options.headers || {}),
  };

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  let body = options.body;
  // If body is plain object and not FormData, serialize as JSON
  if (body && !(body instanceof FormData) && typeof body === 'object') {
    headers['Content-Type'] = 'application/json';
    body = JSON.stringify(body);
  }

  const url = buildUrl(endpoint, options.params);

  try {
    const response = await fetch(url, {
      ...options,
      headers,
      body,
    });

    let data = null;
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      data = await response.json().catch(() => null);
    } else {
      const text = await response.text();
      data = text ? { message: text } : null;
    }

    if (!response.ok) {
      if (response.status === 401) {
        // Clear token if invalid
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
      }

      let errorMessage = 'Request failed';
      if (data) {
        if (data.message) {
          errorMessage = data.message;
        } else if (data.error) {
          errorMessage = data.error;
        } else if (data.errors && typeof data.errors === 'object') {
          const firstKey = Object.keys(data.errors)[0];
          errorMessage = Array.isArray(data.errors[firstKey])
            ? data.errors[firstKey][0]
            : data.errors[firstKey];
        }
      }

      const error = new Error(errorMessage);
      error.status = response.status;
      error.data = data;
      throw error;
    }

    return data;
  } catch (err) {
    if (!err.status) {
      err.status = 0;
      err.message = err.message || 'Network error. Please check backend connection.';
    }
    throw err;
  }
}

export const api = {
  get: (endpoint, params = null, options = {}) =>
    apiRequest(endpoint, { method: 'GET', params, ...options }),
  post: (endpoint, body = null, options = {}) =>
    apiRequest(endpoint, { method: 'POST', body, ...options }),
  patch: (endpoint, body = null, options = {}) =>
    apiRequest(endpoint, { method: 'PATCH', body, ...options }),
  delete: (endpoint, options = {}) =>
    apiRequest(endpoint, { method: 'DELETE', ...options }),
};

export default api;
