<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1 class="logo">Dev<span>Blogs</span></h1>
        <h2>Welcome Back</h2>
        <p class="subtitle">Log in to your developer account</p>
      </div>

      <div v-if="errorMessage" class="alert alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrapper">
            <i class="fa fa-envelope input-icon"></i>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="developer@example.com"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <i class="fa fa-lock input-icon"></i>
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
            <button
              type="button"
              class="toggle-password"
              @click="showPassword = !showPassword"
            >
              <i :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading"><i class="fa fa-spinner fa-spin"></i> Logging in...</span>
          <span v-else>Log In</span>
        </button>
      </form>

      <div class="auth-footer">
        <p>Don't have an account? <router-link to="/register">Create one</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    await authStore.login({
      email: email.value,
      password: password.value,
    });

    const redirectPath = route.query.redirect || '/';
    router.push(redirectPath);
  } catch (err) {
    errorMessage.value = err.message || 'Invalid email or password.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-container {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  width: 100%;
}

.auth-card {
  width: 100%;
  max-width: 440px;
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 36px 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.auth-header {
  text-align: center;
  margin-bottom: 28px;
}

.logo {
  font-size: 2rem;
  font-weight: 800;
  margin: 0 0 12px 0;
  color: var(--text-dark);
}

.logo span {
  color: var(--accent);
}

.auth-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 6px 0;
}

.subtitle {
  color: #8b949e;
  font-size: 0.9rem;
  margin: 0;
}

.alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 0.88rem;
}

.alert-error {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 0.88rem;
  font-weight: 500;
  color: #c9d1d9;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  color: #6e7681;
  font-size: 0.95rem;
}

.input-wrapper input {
  width: 100%;
  padding: 10px 40px 10px 36px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: var(--text-dark);
  font-size: 0.92rem;
  transition: all 0.2s ease;
}

.input-wrapper input:focus {
  outline: none;
  border-color: var(--accent);
  background: rgba(255, 255, 255, 0.07);
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.2);
}

.toggle-password {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #6e7681;
  cursor: pointer;
  padding: 0;
}

.toggle-password:hover {
  color: #c9d1d9;
}

.submit-btn {
  background: var(--accent);
  color: #000;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 6px;
}

.submit-btn:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-footer {
  margin-top: 24px;
  text-align: center;
  font-size: 0.88rem;
  color: #8b949e;
}

.auth-footer a {
  color: var(--accent);
  text-decoration: none;
  font-weight: 500;
}

.auth-footer a:hover {
  text-decoration: underline;
}
</style>
