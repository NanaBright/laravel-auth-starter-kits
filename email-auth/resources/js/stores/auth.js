import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    currentUser: (state) => state.user
  },

  actions: {
    async fetchUser() {
      if (!this.token) return;

      this.loading = true;

      try {
        const response = await window.axios.get('/user');
        this.user = response.data.user;
      } catch (error) {
        console.error('Failed to fetch user:', error);
        if (error.response?.status === 401) {
          this.logout();
        }
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        if (this.token) {
          await window.axios.post('/auth/logout');
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        localStorage.removeItem('token');
      }
    },

    setToken(token) {
      this.token = token;
      localStorage.setItem('token', token);
    },

    clearAuth() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('token');
    }
  }
});
