<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1 class="logo">Dev<span>Blogs</span></h1>
        <h2>Create Account</h2>
        <p class="subtitle">Join the developer blogging community</p>
      </div>

      <div v-if="errorMessage" class="alert alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <span>{{ errorMessage }}</span>
      </div>

      <div v-if="successMessage" class="alert alert-success">
        <i class="fa fa-check-circle"></i>
        <span>{{ successMessage }}</span>
      </div>

      <form @submit.prevent="handleRegister" class="auth-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="name">Full Name *</label>
          <div class="input-wrapper">
            <i class="fa fa-user input-icon"></i>
            <input
              id="name"
              v-model="name"
              type="text"
              placeholder="Ada Lovelace"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <div class="input-wrapper">
            <i class="fa fa-envelope input-icon"></i>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="ada@example.com"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password (min 8 characters) *</label>
          <div class="input-wrapper">
            <i class="fa fa-lock input-icon"></i>
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              minlength="8"
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

        <div class="form-group">
          <label for="bio">Bio (Optional)</label>
          <textarea
            id="bio"
            v-model="bio"
            rows="2"
            placeholder="Full-stack engineer passionate about Laravel & Vue..."
            class="textarea-input"
          ></textarea>
        </div>

        <div class="form-row">
          <div class="form-group flex-1">
            <label for="github">GitHub URL</label>
            <div class="input-wrapper">
              <i class="fab fa-github input-icon"></i>
              <input
                id="github"
                v-model="github"
                type="url"
                placeholder="https://github.com/username"
              />
            </div>
          </div>

          <div class="form-group flex-1">
            <label for="portfolio">Portfolio URL</label>
            <div class="input-wrapper">
              <i class="fa fa-globe input-icon"></i>
              <input
                id="portfolio"
                v-model="portfolio"
                type="url"
                placeholder="https://myportfolio.dev"
              />
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="avatar-label">Avatar (Optional)</label>
          <div class="avatar-upload-box">
            <img :src="avatarPreview || 'https://i.pravatar.cc/150?img=12'" alt="Avatar Preview" class="preview-img" />
            <div class="upload-controls">
              <label for="avatar-input" class="file-btn">
                <i class="fa fa-camera"></i> Choose Picture
              </label>
              <input
                id="avatar-input"
                type="file"
                accept="image/*"
                @change="handleAvatarChange"
                style="display: none;"
              />
              <span v-if="avatarFile" class="file-name">{{ avatarFile.name }}</span>
            </div>
          </div>
        </div>

        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading"><i class="fa fa-spinner fa-spin"></i> Creating account...</span>
          <span v-else>Create Account</span>
        </button>
      </form>

      <div class="auth-footer">
        <p>Already have an account? <router-link to="/login">Log in</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const bio = ref('');
const github = ref('');
const portfolio = ref('');
const avatarFile = ref(null);
const avatarPreview = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const handleAvatarChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('email', email.value);
    formData.append('password', password.value);
    if (bio.value) formData.append('bio', bio.value);
    if (github.value) formData.append('github', github.value);
    if (portfolio.value) formData.append('portfolio', portfolio.value);
    if (avatarFile.value) formData.append('avatar', avatarFile.value);

    await authStore.register(formData);

    // Automatically log in after registration
    await authStore.login({
      email: email.value,
      password: password.value,
    });

    successMessage.value = 'Account created successfully! Redirecting...';
    setTimeout(() => {
      router.push('/');
    }, 1000);
  } catch (err) {
    errorMessage.value = err.message || 'Registration failed. Please check the inputs.';
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
  max-width: 520px;
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 36px 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.auth-header {
  text-align: center;
  margin-bottom: 24px;
}

.logo {
  font-size: 2rem;
  font-weight: 800;
  margin: 0 0 10px 0;
  color: var(--text-dark);
}

.logo span {
  color: var(--accent);
}

.auth-header h2 {
  font-size: 1.4rem;
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

.alert-success {
  background: rgba(66, 184, 131, 0.15);
  border: 1px solid rgba(66, 184, 131, 0.3);
  color: var(--accent);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: flex;
  gap: 12px;
}

.flex-1 {
  flex: 1;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 0.85rem;
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
  font-size: 0.9rem;
}

.input-wrapper input,
.textarea-input {
  width: 100%;
  padding: 9px 36px 9px 34px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: var(--text-dark);
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.textarea-input {
  padding: 8px 12px;
  resize: vertical;
}

.input-wrapper input:focus,
.textarea-input:focus {
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

.avatar-upload-box {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px dashed rgba(255, 255, 255, 0.15);
  border-radius: 8px;
}

.preview-img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent);
}

.file-btn {
  display: inline-block;
  padding: 6px 14px;
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-dark);
  border-radius: 6px;
  font-size: 0.82rem;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.2s;
}

.file-btn:hover {
  background: rgba(255, 255, 255, 0.18);
}

.file-name {
  margin-left: 10px;
  font-size: 0.8rem;
  color: #8b949e;
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
  margin-top: 8px;
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
  margin-top: 20px;
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
