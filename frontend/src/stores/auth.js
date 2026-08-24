import { defineStore } from 'pinia';
import api, { getImageUrl } from '../utils/api';

export const useAuthStore = defineStore('auth', {
  state: () => {
    let savedUser = null;
    try {
      savedUser = JSON.parse(localStorage.getItem('auth_user') || 'null');
    } catch {
      savedUser = null;
    }

    return {
      user: savedUser,
      token: localStorage.getItem('auth_token') || null,
      loading: false,
      error: null,
    };
  },

  getters: {
    isAuthenticated: (state) => !!state.token,
    currentUser: (state) => state.user,
    userAvatar: (state) => getImageUrl(state.user?.avatar, 'https://i.pravatar.cc/150?img=12'),
    userFullName: (state) => state.user?.name || 'Guest',
    userId: (state) => state.user?.id || null,
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/login', credentials);
        this.token = response.token;
        this.user = response.user;

        localStorage.setItem('auth_token', response.token);
        localStorage.setItem('auth_user', JSON.stringify(response.user));
        return response;
      } catch (err) {
        this.error = err.message || 'Login failed';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async register(formData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/users/create', formData);
        return response;
      } catch (err) {
        this.error = err.message || 'Registration failed';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      try {
        if (this.token) {
          await api.post('/logout').catch(() => {});
        }
      } finally {
        this.user = null;
        this.token = null;
        this.error = null;
        this.loading = false;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
      }
    },

    async fetchCurrentUser() {
      if (!this.token) return null;
      try {
        const user = await api.get('/user');
        this.user = user;
        localStorage.setItem('auth_user', JSON.stringify(user));
        return user;
      } catch {
        this.user = null;
        this.token = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        return null;
      }
    },

    async updateProfile(formData, userId) {
      this.loading = true;
      this.error = null;
      try {
        const targetId = userId || this.user?.id;
        const response = await api.post(`/users/update/${targetId}`, formData, {
        });
        if (response.user) {
          this.user = { ...this.user, ...response.user };
          localStorage.setItem('auth_user', JSON.stringify(this.user));
        }
        return response;
      } catch (err) {
        this.error = err.message || 'Update failed';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
