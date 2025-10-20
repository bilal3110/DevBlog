// src/utils/fetchApi.js

const baseUrl = import.meta.env.VITE_API_BASE_URL;

export async function fetchApi(path, options = {}) {
  const response = await fetch(`${baseUrl}${path}`, {
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'API Error');
  }

  return await response.json();
}
