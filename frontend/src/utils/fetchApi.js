import { apiRequest } from './api';

export async function fetchApi(path, options = {}) {
  return apiRequest(path, options);
}

export default fetchApi;
