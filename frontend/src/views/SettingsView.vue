<template>
  <div class="settings-container">
    <div class="settings-card">
      <div class="settings-header">
        <h2>Profile Settings</h2>
        <p class="subtitle">Update your personal information and developer profile</p>
      </div>

      <div v-if="successMessage" class="alert alert-success">
        <i class="fa fa-check-circle"></i>
        <span>{{ successMessage }}</span>
      </div>

      <div v-if="errorMessage" class="alert alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleSaveSettings" class="settings-form" enctype="multipart/form-data">
        <!-- Avatar Section -->
        <div class="form-group avatar-section">
          <label>Profile Picture</label>
          <div class="avatar-edit-wrapper">
            <img
              :src="avatarPreview || authStore.userAvatar"
              alt="Avatar Preview"
              class="settings-avatar-preview"
              @error="e => e.target.src = 'https://i.pravatar.cc/150?img=12'"
            />
            <div class="avatar-actions">
              <label for="avatar-file-input" class="btn-upload">
                <i class="fa fa-camera"></i> Change Picture
              </label>
              <input
                id="avatar-file-input"
                type="file"
                accept="image/*"
                @change="onAvatarFileSelect"
                style="display: none;"
              />
              <span v-if="avatarFile" class="avatar-file-name">{{ avatarFile.name }}</span>
            </div>
          </div>
        </div>

        <!-- Name & Email -->
        <div class="form-row">
          <div class="form-group flex-1">
            <label for="name">Full Name</label>
            <input id="name" v-model="name" type="text" required class="input-field" />
          </div>

          <div class="form-group flex-1">
            <label for="email">Email Address</label>
            <input id="email" v-model="email" type="email" required class="input-field" />
          </div>
        </div>

        <!-- Bio -->
        <div class="form-group">
          <label for="bio">Bio</label>
          <textarea
            id="bio"
            v-model="bio"
            rows="3"
            placeholder="Tell the community about yourself, your stack, and what you build..."
            class="input-field textarea"
          ></textarea>
        </div>

        <!-- Links -->
        <div class="form-row">
          <div class="form-group flex-1">
            <label for="github">GitHub Profile</label>
            <div class="input-icon-wrapper">
              <i class="fab fa-github"></i>
              <input
                id="github"
                v-model="github"
                type="url"
                placeholder="https://github.com/yourname"
                class="input-field with-icon"
              />
            </div>
          </div>

          <div class="form-group flex-1">
            <label for="portfolio">Portfolio Website</label>
            <div class="input-icon-wrapper">
              <i class="fa fa-globe"></i>
              <input
                id="portfolio"
                v-model="portfolio"
                type="url"
                placeholder="https://yourportfolio.dev"
                class="input-field with-icon"
              />
            </div>
          </div>
        </div>

        <!-- Password Change -->
        <div class="password-box">
          <h3>Change Password</h3>
          <p class="small-text">Leave blank if you do not want to change your password.</p>
          <div class="form-group">
            <label for="new-password">New Password (min 8 characters)</label>
            <input
              id="new-password"
              v-model="newPassword"
              type="password"
              minlength="8"
              placeholder="••••••••"
              class="input-field"
            />
          </div>
        </div>

        <div class="form-actions">
          <router-link to="/profile" class="btn-cancel">Cancel</router-link>
          <button type="submit" class="btn-save" :disabled="saving">
            <span v-if="saving"><i class="fa fa-spinner fa-spin"></i> Saving...</span>
            <span v-else>Save Changes</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../utils/api';

const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const bio = ref('');
const github = ref('');
const portfolio = ref('');
const newPassword = ref('');
const avatarFile = ref(null);
const avatarPreview = ref('');

const saving = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const initUserData = () => {
  const user = authStore.currentUser;
  if (user) {
    name.value = user.name || '';
    email.value = user.email || '';
    bio.value = user.bio || '';
    github.value = user.github || '';
    portfolio.value = user.portfolio || '';
  }
};

const onAvatarFileSelect = (e) => {
  const file = e.target.files[0];
  if (file) {
    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const handleSaveSettings = async () => {
  saving.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  try {
    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('email', email.value);
    formData.append('bio', bio.value);
    formData.append('github', github.value);
    formData.append('portfolio', portfolio.value);

    if (newPassword.value) {
      formData.append('password', newPassword.value);
    }
    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value);
    }

    const res = await api.post(`/users/update/${authStore.userId}`, formData);

    if (res.user) {
      authStore.user = { ...authStore.user, ...res.user };
      localStorage.setItem('auth_user', JSON.stringify(authStore.user));
    }

    newPassword.value = '';
    avatarFile.value = null;
    successMessage.value = 'Profile updated successfully!';
  } catch (err) {
    errorMessage.value = err.message || 'Failed to update profile settings.';
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  initUserData();
  await authStore.fetchCurrentUser();
  initUserData();
});
</script>

<style scoped>
.settings-container {
  width: 100%;
  max-width: 600px;
  padding: 30px 15px 80px;
  margin: 0 auto;
}

.settings-card {
  background: #0d1117;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 32px;
}

.settings-header {
  margin-bottom: 24px;
}

.settings-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
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

.alert-success {
  background: rgba(66, 184, 131, 0.15);
  border: 1px solid rgba(66, 184, 131, 0.3);
  color: var(--accent);
}

.alert-error {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.avatar-section {
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.avatar-edit-wrapper {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 8px;
}

.settings-avatar-preview {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent);
}

.btn-upload {
  display: inline-block;
  padding: 7px 14px;
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-dark);
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-upload:hover {
  background: rgba(255, 255, 255, 0.18);
}

.avatar-file-name {
  margin-left: 10px;
  font-size: 0.8rem;
  color: #8b949e;
}

.form-row {
  display: flex;
  gap: 16px;
}

.flex-1 {
  flex: 1;
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

.input-field {
  width: 100%;
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: var(--text-dark);
  font-size: 0.92rem;
  font-family: inherit;
  transition: border-color 0.2s;
}

.input-field:focus {
  outline: none;
  border-color: var(--accent);
  background: rgba(255, 255, 255, 0.07);
}

.textarea {
  resize: vertical;
}

.input-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon-wrapper i {
  position: absolute;
  left: 12px;
  color: #6e7681;
}

.input-field.with-icon {
  padding-left: 36px;
}

.password-box {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  padding: 16px;
}

.password-box h3 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.small-text {
  font-size: 0.8rem;
  color: #8b949e;
  margin: 0 0 12px 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

.btn-cancel {
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.08);
  color: var(--text-dark);
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
}

.btn-save {
  padding: 10px 22px;
  background: var(--accent);
  color: #000;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-save:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
